<?php

namespace App\Fe\Actions;

use Areaseb\Core\Models\{City, Setting, Company, Client, Invoice, Item, Exemption, Product};
use App\Fe\Primitive;
use App\Fe\Xml\Xml;
use App\Fe\Auth\{Authenticate, Config, Request, Response};
use \Log;
use \Exception;
use \Carbon\Carbon;

class Sync extends Primitive
{
    public function __construct()
    {
        $this->token = Authenticate::getToken();
        $this->azienda = (new Config)->getDatiAnagraficiCommittente();
        $this->config = new Config;
        $this->status = config('fe.status');
        $this->types = config('fe.types');
        $this->payment_methods = config('fe.payment_methods');
        $this->payment_modes = config('fe.payment_modes');
        $this->product_default = Product::default();
    }

    public function init()
    {
        $contents = $this->call();
        if($contents)
        {
            foreach($contents as $content)
            {
                $invoice = $content->invoices[0];
                $status = array_search($invoice->status, $this->status);
                $invoice_crm = $this->doesInvoiceExists($invoice, $content->filename, true);
               
                if( $invoice_crm )
                {
                    if($invoice_crm->status != $status)
                    {
                        $invoice_crm->update(['status' => $status]);
                        $message = "SYNC: fattura ".$invoice->number . " status aggiornato ".$invoice->status;
                    }
                    else
                    {
                        $message = "SYNC: fattura ". $invoice->number . " è già presente ed aggiornata ".$invoice->status;
                    }
                    $this->notify($invoice_crm, $message, 'info');
                }
                else
                {
                    if($invoice->status != 'Errore elaborazione')
                    {
                        $invoice = $this->config->getInvoiceById('out', $content->id, $this->token);

                        if($invoice)
                        {
                            $invoice_crm = $this->insertInvoice($invoice, $status);

                            if((new Xml)->rename($content->filename, $invoice_crm, 'out') === false)
                            {
                                $message = "SYNC errore rinominando {$content->filename}";
                                $this->notify($invoice_crm, $message);
                            }
                            $message = "SYNC fattura ".$invoice->invoices[0]->number . " inserita";
                            $this->notify($invoice_crm, $message, 'info');
                        }
                        else
                        {
                            $this->notify($this, "fattura ".$content->invoices[0]->number." non caricata");
                        }
                    }

                }
            }
        }

        $this->updateDate();

        return 'done';
    }

    public function call()
    {

        $url = $this->config->getInvoiceListOut();
        $request = new Request($this->config->getInvoiceListOut());
        $request->addHeader('Accept', 'application/json');
        $request->addHeader('Authorization', 'Bearer ' .  $this->token);
        $response = $request->get();
     
        if($response->status == 200)
        {
            $data = json_decode($response->body);
       
            if($data->errorCode == "0000")
            {
                if($data->totalElements > 0)
                {
                    return $data->content;
                }
                $this->notify($this, "SYNC nessuna fattura inviata trovata nella data fornita", 'info');
                return false;
            }

            $this->notify($this, "errore nella risposta http: codice={$data->errorCode}; descrizione={$data->errorDescription}" . "url chiamato: $url");
            return false;
        }
        $data = json_decode($response->body);
        $this->notify($this, "errore nella risposta http: codice={$data->errorCode}; descrizione={$data->errorDescription}" . "url chiamato: $url");
        return false;
    }

    public function insertInvoice($content, $status)
    {
        $receiver = $content->receiver;
        $code = $this->getFiscalCode($receiver);

        $xml = (new Xml)->getXml(base64_decode($content->file), $content->filename, 'out');

        if ($xml === false)
        {
            Log::channel('fe')->error("SYNC xml object not created.");
            return false;
        }

        $company = Company::where('piva', $code)->orWhere('cf', $code)->first();

        if(is_null($company))
        {
            $header = $xml->FatturaElettronicaHeader;
            $arr = ['supplier' => 0, 'partner' => 0];
            $company = $this->addCompany($header, $code, $receiver, $arr);
            $company->update(['client_id' => Client::Client()->id]);
        }
        else
        {
            Log::channel('fe')->info("SYNC azienda ({$receiver->description}) presente");
        }


        $formatoTrasmissione = $xml->FatturaElettronicaHeader->DatiTrasmissione->FormatoTrasmissione;
        $tipoDoc = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->TipoDocumento;
        $numero_xml = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero;
        $date = Carbon::parse($content->invoices[0]->invoiceDate);
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

		Log::channel('fe')->info("test sync: {$numero}");
/*        $debug = [
        'tipo_doc' => substr($formatoTrasmissione, 1, 2),
        'tipo' => array_search($tipoDoc, $this->types),
        'numero' => $numero,
        'numero_registrazione' => $numero,
        'data' => $date->format('d/m/Y'),
        'data_registrazione' => $date->format('d/m/Y'),
        'company_id' => $company->id,
        'pagamento' => $this->getMetodoPagamento($xml),
        'tipo_saldo' => $this->getTipoSaldo($xml),
        'data_saldo' => $this->getScadenza($xml)->format('d/m/Y'),
        'data_scadenza' => $this->getScadenza($xml)->format('Y-m-d'),
        'rate' => $this->getRate($xml),
        'saldato' => 0,
        'bollo' => $this->getBollo($xml),
        'bollo_a' => ($this->getBollo($xml)) ? 'cliente' : null,
        'pa_n_doc' => $pa->numero,
        'pa_data_doc' => $pa->data,
        'pa_cup' => $pa->cup,
        'pa_cig' => $pa->cig,
        'ddt_n_doc' => $ddt->numero,
        'ddt_data_doc' => $ddt->data,
        'status' => $status,
        'sendable' => 1,
        'imponibile' => $totale->imponibile,
        'iva' => $totale->iva,
    ];

        dd($debug);*/


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

            $invoice->fe_id = $this->getCodeFromFilename($content->filename);

        $invoice->save();

        $this->addItems($invoice, $xml);

        return $invoice;
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
