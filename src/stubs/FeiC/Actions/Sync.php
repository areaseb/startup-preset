<?php

namespace App\FeiC\Actions;

use Areaseb\Core\Models\{City, Setting, Company, Client, Invoice, Item, Exemption, Product, Media};
use App\FeiC\FeiC;
use App\FeiC\Xml\Xml;
use \Log;
use \Exception;
use \Carbon\Carbon;
use FattureInCloud\Api\IssuedDocumentsApi;
use GuzzleHttp\Client as GuzzleHttpClient;
use \Storage;

class Sync extends FeiC
{
    public function sync()
    {
        // Get invoices (vendita)
        // https://github.com/fattureincloud/fattureincloud-php-sdk/blob/master/docs/Api/IssuedDocumentsApi.md#listissueddocuments
        $apiInstance = new IssuedDocumentsApi(
            new GuzzleHttpClient(),
            $this->config,
        );

        $settings = Setting::fe();
        $type = 'invoice';
        $sort = '-date';
        $per_page = $settings->max_receive;

        if ($per_page < 5)
            $per_page = 5; // Min value

        $fields = 'ei_status';
        $fieldset = 'detailed';

        try {
            $result = $apiInstance->listIssuedDocuments($this->company_id, $type, $fields, $fieldset, $sort, 1, $per_page);
            $invoices = $result['data'];
        } catch (Exception $e) {
            echo 'Exception when calling IssuedDocumentsApi->listIssuedDocuments: ', $e->getMessage(), PHP_EOL;
        }

        foreach($invoices as $invoice)
        {
            // If invoice exists, update status. Else, add invoice
			$status = array_search($invoice['ei_status'], $this->status);
			$invoice_crm = $this->invoiceExists($invoice);
            
            if ($invoice_crm) {
                // Update invoice
				$invoice_crm->status = $status;
				$invoice_crm->save();

				if($invoice['ei_status'] == 'rejected')
                {
                    // GET REJECTION REASON
                }
            } else {
                // Invoice not found. Add to CRM
				$company = $this->getOrAddCompany($invoice);

				// Get XML and add to CRM
				$apiInstance = new \FattureInCloud\Api\IssuedEInvoicesApi(
					new GuzzleHttpClient(),
					$this->config,
				);
				
				try {
					$xml_raw = $apiInstance->getEInvoiceXml($this->company_id, $invoice['id'], true);						
				} catch (Exception $e) {
					echo 'Exception when calling IssuedEInvoicesApi->getEInvoiceXml: ', $e->getMessage(), PHP_EOL;
				}
				
                $invoice_crm = $this->insertInvoiceFromXml($xml_raw, $company, $status, $invoice['id']);
            }
		}

        // Update last sync date
        $this->updateDate();

        return 'done';
    }

	public function insertInvoiceFromXml($xml_raw, $company, $status, $fe_id)
    {
		$xml = (new Xml)->createSimpleXml($xml_raw);

        if ($xml === false)
        {
            Log::channel('fe')->error("SYNC xml object not created.");
            return false;
        }

        $formatoTrasmissione = $xml->FatturaElettronicaHeader->DatiTrasmissione->FormatoTrasmissione;
        $tipoDoc = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->TipoDocumento;
        $numero_xml = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero;
        $date = Carbon::parse($xml->FatturaElettronicaBody->Data);
        $pa = $this->getDatiPA($xml);
        $ddt = $this->getDatiDDT($xml);
        $totale = $this->getTotale($xml);
                
        if(strpos($numero_xml, '/#') > 0){
    		$numero = strstr($numero_xml, '/#', true);
    	} elseif(strpos($numero_xml, '/') > 0){
    		list($tipo, $nu) = explode(' ', $numero_xml);
    		list($numero, $an) = explode('/', $nu);
    	} else {
    		$numero = $numero_xml;
    	}

        $invoice = new Invoice;
            $invoice->tipo_doc = $this->getFormatoTrasmissione($xml);
            $invoice->tipo = $this->getTipoDocumento($xml);
            $invoice->numero = $numero;
            $invoice->numero_registrazione = $numero;
            $invoice->data = $date->format('d/m/Y');
            $invoice->data_registrazione = $date->format('d/m/Y');
            $invoice->company_id = $company->id;

            $invoice->pagamento = $this->getMetodoPagamento($xml);
            $invoice->tipo_saldo = $this->getTipoSaldo($xml);
            $invoice->data_saldo = $this->getScadenza($xml)->format('d/m/Y');
            $invoice->data_scadenza = $this->getScadenza($xml)->format('Y-m-d');

            $invoice->rate = $this->getRate($xml);
            $invoice->bollo = $this->getBollo($xml);
            $invoice->bollo_a = ($this->getBollo($xml)) ? 'cliente' : null;

            $invoice->pa_n_doc = $pa->numero;
            $invoice->pa_data_doc = $pa->data;
            $invoice->pa_cup = $pa->cup;
            $invoice->pa_cig =$pa->cig;
            $invoice->ddt_n_doc = $ddt->numero;
            $invoice->ddt_data_doc = $ddt->data;

            $invoice->status = $status;
            $invoice->sendable = 1;

            $invoice->imponibile = $totale->imponibile;
            $invoice->iva = $totale->iva;

            $invoice->fe_id = $fe_id;
        $invoice->save();

        $this->addItems($invoice, $xml);

        return $invoice;
    }

    /**
     * Check if we have an invoice in the CRM with FE ID or number and date
     */
    public function invoiceExists($invoice)
    {
        $crm_invoice = Invoice::where('fe_id', $invoice['id'])->first();

        if (!$crm_invoice) {
            $crm_invoice = Invoice::whereDate('data', Carbon::parse($invoice['date'])->format('Y-m-d'))
            ->where('numero', $invoice['number'])
            ->where('tipo', 'U')
            ->first();
        }

        return $crm_invoice;
    }

    /**
     * today -50days
     * @return [str] [date YYYY-MM-DD]
     */
    private function updateDate()
    {
        $setting = Setting::where('model', 'Fe')->first();
            $fields = $setting->fields;
            $fields['last_sync'] = Carbon::today()->subDays(50)->format('Y-m-d');
            $setting->fields = $fields;
        $setting->save();
    }

}
