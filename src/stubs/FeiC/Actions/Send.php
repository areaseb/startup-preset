<?php

namespace App\FeiC\Actions;

use Areaseb\Core\Models\{Invoice, Item, Exemption, Company, Country, Media, Setting};
use App\Fe\Xml\Xml;
use Areaseb\Core\Models\Fe\FatturaCheck;
use App\Fe\Primitive;
use App\Fe\Auth\{Authenticate, Config, Request, Response};
use App\FeiC\FeiC;
use \Log;
use \Exception;
use FattureInCloud\Api\IssuedDocumentsApi;
use FattureInCloud\Api\IssuedEInvoicesApi;
use FattureInCloud\Model\CreateIssuedDocumentRequest;
use GuzzleHttp\Client;

class Send extends FeiC
{
    public $invoice;

    public function __construct($invoice) {
        parent::__construct();

        $this->invoice = $invoice;
    }

    public function send() {
		// Send invoices or register only
		$send_einvoice = true;
		
		$company_id = $this->company_id;

		if($this->invoice->fe_id) {
			// Invoice already registered. Check and send
			$document_id = $this->invoice->fe_id;

			// Verify invoice
			$apiInstance = new IssuedEInvoicesApi(
				new Client(),
				$this->config
			);

			try {
				$result = $apiInstance->verifyEInvoiceXml($company_id, $document_id);

				if (!$result['data']['success']) {
					// Invoice invalid
					$this->notify($this->invoice, 'Invalid e_invoice XML', 'info');
				}

			} catch (Exception $e) {
				return 'Exception when calling IssuedEInvoicesApi->verifyEInvoiceXml: '. $e->getMessage();
			}		

			if($send_einvoice) {
				try {
					$result = $apiInstance->sendEInvoice($this->company_id, $document_id);
				} catch (Exception $e) {
					return 'Exception when calling IssuedEInvoicesApi->sendEInvoice: '. $e->getMessage(). PHP_EOL;
				}
			}

			return 'done';
		}

        $apiInstance = new IssuedDocumentsApi(
            new Client(),
            $this->config
        );

        $document = new CreateIssuedDocumentRequest;

		$is_ritenuta = ($this->invoice->ritenuta > 0);

        // Invoice client
        $company = $this->invoice->company;
        if($company->prov){
        	if($company->piva && substr($company->piva, 0, 1) != '9'){
        		$entity = [
		            "name" => $company->rag_soc,
		            "vat_number" => $company->piva,
		            "tax_code" => $company->cf,
		            "address_street" => $company->address,
		            "address_postal_code" => $company->zip,
		            "address_city" => $company->city,
		            "address_province" => $company->prov,
		            "country" => Country::where('iso2', $company->nation)->first()->nome,	
		        ];
        	} else {
        		$entity = [
		            "name" => $company->rag_soc,
		            "tax_code" => $company->cf,
		            "address_street" => $company->address,
		            "address_postal_code" => $company->zip,
		            "address_city" => $company->city,
		            "address_province" => $company->prov,
		            "country" => Country::where('iso2', $company->nation)->first()->nome,	
		        ];
        	}
        	
        } else {
        	$entity = [
	            "name" => $company->rag_soc,
	            "vat_number" => $company->piva,
	            "tax_code" => $company->cf,
	            "address_street" => $company->address,
	            "address_postal_code" => $company->zip,
	            "address_city" => $company->city,
	            "country" => Country::where('iso2', $company->nation)->first()->nome,	
	        ];
        }
        
		if($company->pec)
			$entity['certified_email'] = $company->pec;

		$pubblica_amministrazione = ($this->invoice->tipo_doc == 'Pu');
		
		$sottoconto = 'P';
		
		if($this->invoice->tipo == 'R'){
			$type = 'receipt';
		} elseif($this->invoice->tipo == 'F'){
			$type = 'invoice';
		} elseif($this->invoice->tipo == 'A'){
			$type = 'credit_note';
		}
		
		if(!is_null($sottoconto)){
			$data = [
	            "entity" => $entity,
	            "type" => $type,
	            "numeration" => $sottoconto,
	            "number" => $this->invoice->numero,
	            "amount_net" => $this->invoice->total,
	            "amount_vat" => $this->invoice->iva,
				'gross_price' => $this->invoice->imponibile + $this->invoice->iva,
	            "date" => $this->invoice->data->format('Y-m-d'),
	            "next_due_date" => $this->invoice->data_scadenza->format('Y-m-d'),
	            "e_invoice" => true,
				"ei_data" => [
					"vat_kind" => 'I',
				],
	        ];
		} else {
			$data = [
	            "entity" => $entity,
	            "type" => $type,
	            "number" => $this->invoice->numero,
	            "amount_net" => $this->invoice->total,
	            "amount_vat" => $this->invoice->iva,
				'gross_price' => $this->invoice->imponibile + $this->invoice->iva,
	            "date" => $this->invoice->data->format('Y-m-d'),
	            "next_due_date" => $this->invoice->data_scadenza->format('Y-m-d'),
	            "e_invoice" => true,
				"ei_data" => [
					"vat_kind" => 'I',
				],
	        ];
		}
        

		// Cup / SDI
		if($pubblica_amministrazione) {
			$data['entity']['type'] = 'pa';
			$data['entity']['ei_code'] = $this->invoice->pa_cup;	
			$data['ei_data']['cup'] = $this->invoice->pa_cup;		
		} else {
			$data['entity']['ei_code'] = $company->sdi;
		}

        // Add invoice items		
        $data['items_list'] = [];

		$bollo_cliente = false;

        foreach($this->invoice->items as $item) {
			$name = $item->product->nome;
			$description = $item->descrizione;
			$iva_id = 0;
			$net_price = $item->importo * ((100 - $item->sconto) / 100);
			$gross_price = ($item->importo + $item->iva) * ((100 - $item->sconto) / 100);
			$not_taxable = false;

			if($item->product->codice == 'BOL') {
				$iva_id = 21;
				$net_price = 2;
				$gross_price = 2;
				$not_taxable = true;

				$bollo_cliente = true;
			} elseif($item->exemption_id) {
				$not_taxable = false; // To check
				$iva_id = config('fe.vat_feic')[$item->exemption_id];		
			}

            $new_item = [
                "product_id" => $item->product_id,
                "code" => $item->product->codice,
                "name" => $name,
                "net_price" => $net_price,
				"gross_price" => $gross_price,
                "discount" => $item->sconto,
                "discount_highlight" => false,
                "qty" => $item->qta,
                "description" => $description,
                "vat" => [
                    "id" => $iva_id,					
                ],                
				"not_taxable" => $not_taxable,
            ];

			if($is_ritenuta)
				$new_item['apply_withholding_taxes'] = true;

            $data['items_list'][] = $new_item;
        }        

        // Add IBAN
/*        $settings = Setting::fe();
        if ($settings->IBAN != '')
        {
            $data['ei_data']['bank_iban'] = str_replace(' ', '', $settings->IBAN);
        }*/

        $data['ei_data']['payment_method'] = config('fe.payment_methods')[$this->invoice->pagamento];				

		$options = [];

		// FORCE FIX PAYMENTS
		$force_fix_payments = false;
		if($force_fix_payments) {
        	$options["fix_payments"] = true;
		}
		
        /**
         * Check if ritenuta acconto
         */
        if ($is_ritenuta) {			
			/*
            $net = $data['amount_net'] - $this->invoice->ritenuta;
            $data['amount_net'] = $net;
            $iva = $this->invoice->iva;
            $data['amount_vat'] = $iva;
            $data['gross_price'] = $net + $iva;
			*/
			$data["ei_withholding_tax_causal"] = "A";
			$data["amount_withholding_tax_taxable"] = $this->invoice->imponibile;

			if(!$bollo_cliente && !is_null($this->invoice->bollo)) {
				$data['stamp_duty'] = $this->invoice->bollo;
			}
        } else {
			$data["withholding_tax"] = 0;
		}

		// Set payment information
        $data['payments_list'] = [];

        if($this->invoice->rate)
        {
            $data['use_split_payment'] = true;

            $rate = explode(',', $this->invoice->rate);
            $n_rate = count($rate);
            $total = $this->decimal($this->invoice->total);
            $amount_rata = $this->decimal($total / $n_rate);
            $amount_payed = 0;

            for($nr=0;$nr<$n_rate;$nr++)
            {
                $new_payment = [];

                $scadenza_rata = \Carbon\Carbon::createFromFormat('d/m/Y', trim($rate[$nr]))->format('Y-m-d');

                $new_payment['due_date'] = $scadenza_rata;

                if($nr == ($n_rate -1))
                {
                    $new_payment['amount'] = $this->decimal($total - $amount_payed);
                }
                else
                {
                    $new_payment['amount'] = $amount_rata;
                }

                $amount_payed +=  $amount_rata;

                $data['payments_list'][] = $new_payment;
            }
        }
        else
        {
            $data['use_split_payment'] = false;

            $new_payment = [];
            $new_payment['amount'] = $this->invoice->total;
            $new_payment['due_date'] = $this->invoice->data_scadenza->format('Y-m-d');

            $data['payments_list'][] = $new_payment;
        }
        
        // Set invoice data
        $document->setData($data);
        $document->setOptions($options);

        try {
            // Create invoice
            $result = $apiInstance->createIssuedDocument($company_id, $document);

            $document_id = $result['data']['id'];

        } catch (Exception $e) {
			var_dump($e->getMessage());
            \Log::error('Exception when calling IssuedDocumentsApi->createIssuedDocument => '. $e->getMessage());
            return 'Exception when calling IssuedDocumentsApi->createIssuedDocument => '. $e->getMessage(). PHP_EOL;
        }

        // Verify invoice
        $apiInstance = new IssuedEInvoicesApi(
            new Client(),
            $this->config
        );

        try {
            $result = $apiInstance->verifyEInvoiceXml($company_id, $document_id);

            if (!$result['data']['success']) {
                // Invoice invalid
                $this->notify($this->invoice, 'Invalid e_invoice XML', 'info');
            }

        } catch (Exception $e) {
			\Log::error('Exception when calling IssuedEInvoicesApi->verifyEInvoiceXml: '. $e->getMessage());
            return 'Exception when calling IssuedEInvoicesApi->verifyEInvoiceXml: '. $e->getMessage();
        }

		if($send_einvoice) {
	        try {
	            $result = $apiInstance->sendEInvoice($this->company_id, $document_id);	            	
	        } catch (Exception $e) {
	            \Log::error('Exception when calling IssuedEInvoicesApi->sendEInvoice: '. $e->getMessage());
	            return 'Exception when calling IssuedEInvoicesApi->sendEInvoice: '. $e->getMessage(). PHP_EOL;
	        }
		}
		     
		// Save FE ID in database
		$this->invoice->update([
			'status' => 1, //status = presa in carico (1)
			'fe_id' => $document_id,
		]); 
				
        return 'done';
    }
}
