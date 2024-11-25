<?php

namespace App\FeiC\Actions;

use Areaseb\Core\Models\{City, Setting, Company, Client, Invoice, Item, Exemption, Product, Media, Contact, Country, ContactBranch, CompanyBranches};
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
		// Debug
		// Prints FE id, Crm id and Crm invoice number
		$print_ids_only = false;
		$print_id_and_stop = false;
		$allow_only_id = false;

        // Get invoices (vendita)
        // https://github.com/fattureincloud/fattureincloud-php-sdk/blob/master/docs/Api/IssuedDocumentsApi.md#listissueddocuments
        $apiInstance = new IssuedDocumentsApi(
            new GuzzleHttpClient(),
            $this->config,
        );

        $settings = Setting::fe();
        $types = array('invoice', 'credit_note');
        $sort = '-date';
        $per_page = $settings->max_receive;

        if ($per_page < 5)
            $per_page = 5; // Min value

		$per_page = 25;

        $fields = 'ei_status';
        $fieldset = 'detailed';

		$skip = 1; // min: 1
		
		foreach($types as $type){
			
	        try {
	            $result = $apiInstance->listIssuedDocuments($this->company_id, $type, $fields, $fieldset, $sort, $skip, $per_page);
	            $invoices = $result['data'];
	        } catch (Exception $e) {
	            echo 'Exception when calling IssuedDocumentsApi->listIssuedDocuments: ', $e->getMessage(), PHP_EOL;
	        }

	        foreach($invoices as $invoice)
	        {
	            // If invoice exists, update status. Else, add invoice
				$status = array_search($invoice['ei_status'], $this->status);

				if($invoice['ei_status'] == 'processing')
					$status = 1;
				$invoice_crm = $this->invoiceExists($invoice);

				if($print_id_and_stop != false) {
					if ($invoice['id'] == $print_id_and_stop)
						dd($invoice);
					else
						continue;
				}

				if($allow_only_id != false && $invoice['id'] != $allow_only_id)
					continue;

				if ($invoice_crm) {
					// Debug
					if($print_ids_only) {
						echo 'FE ID: ' . $invoice['id'] . ' | CRM ID: ' . $invoice_crm->id . ' | CRM NUM: ' . $invoice_crm->numero . '<br />';
						continue;
					}

	                // Update invoice
					$invoice_crm->status = $status;
					$invoice_crm->save();

					if($invoice['ei_status'] == 'rejected')
	                {
	                    // GET REJECTION REASON
	                }
	            } else {
					// Debug
					if($print_ids_only) {
						continue;
					}

	                // Invoice not found. Add to CRM

					// Check if invoice has company // association or private client
					$company = null;
					$client = null;

					if($invoice['entity']['vat_number']) { 
						// Company
						$company = $this->getOrAddCompanyOnly($invoice);
					} else {
						if(preg_match("/[a-z]/i", $invoice['entity']['tax_code'])){
							// Private
							$client = $this->getOrAddClientOnly($invoice);
						} else {
							// Association
							$company = $this->getOrAddAssociationOnly($invoice);
						}
					}

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
					
	                $invoice_crm = $this->insertInvoiceFromXml($xml_raw, $company, $client, $status, $invoice['id']);
	            }
			}
		}

        // Update last sync date
        $this->updateDate();

        return 'done';
    }

	public function insertInvoiceFromXml($xml_raw, $company, $client, $status, $fe_id)
    {
		$company_id = null;
		$contact_id = null;

		if($company)
			$company_id = $company->id;

		if($client)
			$contact_id = $client->id;
				
		if(!is_null($contact_id)){
			$cb = ContactBranch::where('contact_id', $contact_id)->whereNotNull('branch_id')->first();
			if($cb){
				$branch_id = $cb->branch_id;
			} else {
				// Attach branches				
				$contact_branch = new ContactBranch;
				$contact_branch->contact_id = $contact_id;
				$contact_branch->branch_id = 2;
				$contact_branch->save();
				
				$branch_id = 2;
			}
		}
		
		if(!is_null($company_id)){
			$cb = CompanyBranches::where('company_id', $company_id)->whereNotNull('branch_id')->first();
			if($cb){
				$branch_id = $cb->branch_id;
			} else {
				// Attach branches
				$company_branch = new CompanyBranches;
				$company_branch->company_id = $company_id;
				$company_branch->branch_id = 2;
				$company_branch->save();
				
				$branch_id = 2;
			}
		}

		$xml = (new Xml)->createSimpleXml($xml_raw);

        if ($xml === false)
        {
            Log::channel('fe')->error("SYNC xml object not created.");
            return false;
        }

        $formatoTrasmissione = $xml->FatturaElettronicaHeader->DatiTrasmissione->FormatoTrasmissione;
        $tipoDoc = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->TipoDocumento;
        $numero_xml = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero;
        $date = Carbon::parse($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Data);
        $pa = $this->getDatiPA($xml);
        $ddt = $this->getDatiDDT($xml);
        $totale = $this->getTotale($xml);
                
		// Get max number from DB
		$type = $this->getTipoDocumento($xml);
		$year = $date->format('Y');

		/*
		$numero = Invoice::whereYear('data', $year)->where('tipo', $type)->max('numero');
		$numero++;
		*/
		$numero = $numero_xml;

		if (strstr($numero_xml, 'Promiscuo')){
			$branch_id = 1;
		} elseif (strstr($numero_xml, 'Mendola')){
			$branch_id = 3;
		} elseif (strstr($numero_xml, 'Everywhere')){
			$branch_id = 4;
		}
			

		/*
        if(strpos($numero_xml, '/#') > 0){
    		$numero = strstr($numero_xml, '/#', true);
    	} elseif(strpos($numero_xml, '/') > 0){
    		list($tipo, $nu) = explode(' ', $numero_xml);
    		list($numero, $an) = explode('/', $nu);
    	} else {
    		$numero = $numero_xml;
    	}
		*/

        $invoice = new Invoice;
            $invoice->tipo_doc = $this->getFormatoTrasmissione($xml);
            $invoice->tipo = $type;
            $invoice->numero = $numero;
            $invoice->numero_registrazione = $numero;
            $invoice->data = $date->format('d/m/Y');
            $invoice->data_registrazione = $date->format('d/m/Y');

            $invoice->company_id = $company_id;
			$invoice->contact_id = $contact_id;
			$invoice->branch_id = $branch_id;

            $invoice->pagamento = $this->getMetodoPagamento($xml);
            $invoice->tipo_saldo = $this->getTipoSaldo($xml);
            $invoice->data_saldo = $this->getScadenza($xml)->format('d/m/Y');
            $invoice->data_scadenza = $this->getScadenza($xml)->format('Y-m-d');
            $invoice->saldato = 1;

            $invoice->rate = $this->getRate($xml);
            $invoice->bollo = $this->getBollo($xml);
            $invoice->bollo_a = ($this->getBollo($xml)) ? 'cliente' : null;

			$invoice->perc_ritenuta = $this->getPercRitenuta($xml);
			$invoice->ritenuta = $this->getRitenuta($xml);

            $invoice->pa_n_doc = $pa->numero;
            $invoice->pa_data_doc = $pa->data;
            $invoice->pa_cup = $pa->cup;
            $invoice->pa_cig =$pa->cig;
            $invoice->ddt_n_doc = $ddt->numero;
            $invoice->ddt_data_doc = $ddt->data;
			
			$invoice->aperta = 0;
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

	public function getOrAddCompanyOnly($invoice) {
        // Check if provider exists
        $fe_company = $invoice['entity'];

        $fe_id = $fe_company['id'];
        $piva = $fe_company['vat_number'];
        $cf = $fe_company['tax_code'];

		// Search by front-end ID or Partita IVA
		if(!$fe_id)
			$company = null;
		else
        	$company = Company::where('fe_id', $fe_id)->first();

        if (!$company && $piva)
            $company = Company::where('piva', $piva)->first();

        if(!$company) // Add provider
        {
            // Adjust format
            $fe_province_code = $fe_company['address_province'];
            $province_from_code = City::where('sigla_provincia', $fe_province_code)->first();
            if ($province_from_code)
                $province = $province_from_code->provincia;
            else
                $province = null;

            $fe_nation_name = $fe_company['country'];
            $nation = \DB::table('countries')->select('iso2')->where('name', 'like', $fe_nation_name)->orWhere('nome', 'like', $fe_nation_name)->first();
	      	if($nation){
	      		$nation = $nation->iso2;
	      	} else {
	      		$nation = 'IT';
	      	}
	      	
	      	
	      	$city_id = \DB::table('cities')->select('id')->where('sigla_provincia', strtoupper($fe_company['address_province']))->where('comune', 'like', $fe_company['address_city'])->first();
	      	if($city_id){
	      		$city_id = $city_id->id;
	      	} else {
	      		$city_id = null;
	      	}
	      	
	      	$lang = 'it';
	        if($nation != 'IT')
	        {
	            $lang = 'en';
	        }    

            $company = new Company;
            $company->fe_id = $fe_company['id'];
            $company->rag_soc = $fe_company['name'];
            $company->nation = $nation;
            $company->lang = $lang;
            $company->address = $fe_company['address_street'];
            $company->city = $fe_company['address_city'];
            $company->zip = $fe_company['address_postal_code'];
            $company->province = $province;            
		    $company->email = $fe_company['email'];
		    $company->phone = $fe_company['phone'];
		    $company->sdi = $fe_company['ei_code'];
			$company->private = 0;
            $company->piva = $fe_company['vat_number'];
            $company->cf = $fe_company['tax_code'];
		    $company->supplier = 0;
		    $company->partner = 0;
		    $company->active = 1;
		    $company->parent_id = null;
		    $company->city_id = $city_id;
		    $company->s1 = 0;
		    $company->s2 = 0;
		    $company->s3 = 0;
            $company->client_id = 3;
			$company->origin = 'FattureInCloud';
            $company->save();

			// Create contact
			$contact = new Contact;
            $contact->fe_id = $fe_company['id'];
			$contact->nome = '';
            $contact->cognome = $fe_company['name'];
			$contact->cellulare = $fe_company['phone'];
            $contact->cod_fiscale = $fe_company['tax_code'];
			$contact->email = $fe_company['email'];
            $contact->indirizzo = $fe_company['address_street'];
            $contact->cap = $fe_company['address_postal_code'];
            $contact->citta = $fe_company['address_city'];
            $contact->provincia = $province;
			$contact->city_id = $city_id;
            $contact->nazione = $nation;
		    $contact->lingua = strtolower($lang);
		    $contact->subscribed = 1;
		    $contact->requested_unsubscribed = 0;
		    $contact->user_id = null;
		    $contact->parent_id = null;
		    $contact->piva = null;
			$contact->contact_type_id = 1;
			$contact->company_id = $company->id;
			$contact->origin = 'FattureInCloud';
		    $contact->attivo = 1;
	    	$contact->nickname = $fe_company['name'];
		    $contact->privacy = 1;
            $contact->save();

			// Attach branches
			$company_branch = new CompanyBranches;
			$company_branch->company_id = $company->id;
			$company_branch->branch_id = 2;
			$company_branch->save();
			
			$contact_branch = new ContactBranch;
			$contact_branch->contact_id = $contact->id;
			$contact_branch->branch_id = 2;
			$contact_branch->save();
        }
        else
        {
            $this->notify($this, "RECEIVE azienda ({$fe_company['name']}) già presente", 'info');
        }

        return $company;
    }

	public function getOrAddAssociationOnly($invoice) {
		// Check if provider exists
        $fe_company = $invoice['entity'];

        $fe_id = $fe_company['id'];
        $cf = $fe_company['tax_code'];

		// Search by front-end ID or Codice Fiscale
		$company = null;

		if($fe_id)
        	$company = Company::where('fe_id', $fe_id)->first();

        if (!$company && $cf)
            $company = Company::where('cf', $cf)->first();

        if(!$company) // Add provider
        {
            // Adjust format
            $fe_province_code = $fe_company['address_province'];
            $province_from_code = City::where('sigla_provincia', $fe_province_code)->first();
            if ($province_from_code)
                $province = $province_from_code->provincia;
            else
                $province = null;

            $fe_nation_name = $fe_company['country'];
            $nation = \DB::table('countries')->select('iso2')->where('name', 'like', $fe_nation_name)->orWhere('nome', 'like', $fe_nation_name)->first();
	      	if($nation){
	      		$nation = $nation->iso2;
	      	} else {
	      		$nation = 'IT';
	      	}
	      	
	      	
	      	$city_id = \DB::table('cities')->select('id')->where('sigla_provincia', strtoupper($fe_company['address_province']))->where('comune', 'like', $fe_company['address_city'])->first();
	      	if($city_id){
	      		$city_id = $city_id->id;
	      	} else {
	      		$city_id = null;
	      	}
	      	
	      	$lang = 'it';
	        if($nation != 'IT')
	        {
	            $lang = 'en';
	        }    

            $company = new Company;
            $company->fe_id = $fe_company['id'];
            $company->rag_soc = $fe_company['name'];
            $company->nation = $nation;
            $company->lang = $lang;
            $company->address = $fe_company['address_street'];
            $company->city = $fe_company['address_city'];
            $company->zip = $fe_company['address_postal_code'];
            $company->province = $province;            
		    $company->email = $fe_company['email'];
		    $company->phone = $fe_company['phone'];
		    $company->sdi = '0000000';
			$company->private = 1;
            $company->piva = null;
            $company->cf = $fe_company['tax_code'];
		    $company->supplier = 0;
		    $company->partner = 0;
		    $company->active = 1;
		    $company->parent_id = null;
		    $company->city_id = $city_id;
		    $company->s1 = 0;
		    $company->s2 = 0;
		    $company->s3 = 0;
            $company->client_id = 3;
			$company->origin = 'FattureInCloud';
            $company->save();

			// Create contact
			$contact = new Contact;
            $contact->fe_id = $fe_company['id'];
			$contact->nome = '';
            $contact->cognome = $fe_company['name'];
			$contact->cellulare = $fe_company['phone'];
            $contact->cod_fiscale = $fe_company['tax_code'];
			$contact->email = $fe_company['email'];
            $contact->indirizzo = $fe_company['address_street'];
            $contact->cap = $fe_company['address_postal_code'];
            $contact->citta = $fe_company['address_city'];
            $contact->provincia = $province;
			$contact->city_id = $city_id;
            $contact->nazione = $nation;
		    $contact->lingua = strtolower($lang);
		    $contact->subscribed = 1;
		    $contact->requested_unsubscribed = 0;
		    $contact->user_id = null;
		    $contact->parent_id = null;
		    $contact->piva = null;
			$contact->contact_type_id = 1;
			$contact->company_id = $company->id;
			$contact->origin = 'FattureInCloud';
		    $contact->attivo = 1;
	    	$contact->nickname = $fe_company['name'];
		    $contact->privacy = 1;
            $contact->save();

			// Attach branches
			$company_branch = new CompanyBranches;
			$company_branch->company_id = $company->id;
			$company_branch->branch_id = 2;
			$company_branch->save();
			
			$contact_branch = new ContactBranch;
			$contact_branch->contact_id = $contact->id;
			$contact_branch->branch_id = 2;
			$contact_branch->save();
        }
        else
        {
            $this->notify($this, "RECEIVE azienda ({$fe_company['name']}) già presente", 'info');
        }

        return $company;
	}

	public function getOrAddClientOnly($invoice) {
		// Check if provider exists
        $fe_company = $invoice['entity'];

        $fe_id = $fe_company['id'];
        $cf = $fe_company['tax_code'];

		// Search by front-end ID or Codice Fiscale
		$company = null;

		if($fe_id)
        	$company = Contact::where('fe_id', $fe_id)->first();

        if (!$company && $cf)
            $company = Contact::where('cod_fiscale', $cf)->first();

        if(!$company) // Add client
        {
            // Adjust format
            $fe_province_code = $fe_company['address_province'];
            $province_from_code = City::where('sigla_provincia', $fe_province_code)->first();
            if ($province_from_code)
                $province = $province_from_code->provincia;
            else
                $province = null;

            $fe_nation_name = $fe_company['country'];
            $nation = \DB::table('countries')->select('iso2')->where('name', 'like', $fe_nation_name)->orWhere('nome', 'like', $fe_nation_name)->first();
	      	if($nation){
	      		$nation = $nation->iso2;
	      	} else {
	      		$nation = 'IT';
	      	}
	      	
	      	
	      	$city_id = \DB::table('cities')->select('id')->where('sigla_provincia', strtoupper($fe_company['address_province']))->where('comune', 'like', $fe_company['address_city'])->first();
	      	if($city_id){
	      		$city_id = $city_id->id;
	      	} else {
	      		$city_id = null;
	      	}
	      	
	      	$lang = 'it';
	        if($nation != 'IT')
	        {
	            $lang = 'en';
	        } 

			// Get name
			$name = '';
			$surname = '';

			if(strpos($fe_company['name'], ' ') > 0) {
				$names = explode(' ', $fe_company['name'], 2);
				$name = $names[0];
				$surname = end($names);
			} else {
				$surname = $fe_company['name'];
			}
			
			// Create company
			$company = new Company;
            $company->fe_id = $fe_company['id'];
            $company->rag_soc = $fe_company['name'];
            $company->nation = $nation;
            $company->lang = $lang;
            $company->address = $fe_company['address_street'];
            $company->city = $fe_company['address_city'];
            $company->zip = $fe_company['address_postal_code'];
            $company->province = $province;            
		    $company->email = $fe_company['email'];
		    $company->phone = $fe_company['phone'];
			$company->private = 1;
            $company->piva = null;
            $company->cf = $fe_company['tax_code'];
		    $company->supplier = 0;
		    $company->partner = 0;
		    $company->active = 1;
		    $company->parent_id = null;
		    $company->city_id = $city_id;
		    $company->s1 = 0;
		    $company->s2 = 0;
		    $company->s3 = 0;
            $company->client_id = 3;
			$company->origin = 'FattureInCloud';
            $company->save();

			// Create contact
			$contact = new Contact;
            $contact->fe_id = $fe_company['id'];
			$contact->nome = $name;
            $contact->cognome = $surname;
			$contact->cellulare = $fe_company['phone'];
            $contact->cod_fiscale = $fe_company['tax_code'];
			$contact->email = $fe_company['email'];
            $contact->indirizzo = $fe_company['address_street'];
            $contact->cap = $fe_company['address_postal_code'];
            $contact->citta = $fe_company['address_city'];
            $contact->provincia = $province;
			$contact->city_id = $city_id;
            $contact->nazione = $nation;
		    $contact->lingua = strtolower($lang);
		    $contact->subscribed = 1;
		    $contact->requested_unsubscribed = 0;
		    $contact->user_id = null;
		    $contact->parent_id = null;
		    $contact->piva = null;
			$contact->contact_type_id = 1;
			$contact->company_id = $company->id;
			$contact->origin = 'FattureInCloud';
		    $contact->attivo = 1;
	    	$contact->nickname = strtoupper($surname . ' ' . substr($name, 0, 1) . '.');
		    $contact->privacy = 1;
            $contact->save();

			// Attach branches
			$company_branch = new CompanyBranches;
			$company_branch->company_id = $company->id;
			$company_branch->branch_id = 2;
			$company_branch->save();
			
			$contact_branch = new ContactBranch;
			$contact_branch->contact_id = $contact->id;
			$contact_branch->branch_id = 2;
			$contact_branch->save();
        }
        else
        {
            $this->notify($this, "RECEIVE cliente ({$fe_company['name']}) già presente", 'info');
        }

        return $company;
	}
}
