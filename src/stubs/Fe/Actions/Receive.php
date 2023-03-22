<?php

namespace App\Fe\Actions;

use Areaseb\Core\Models\{Client,Company,City, Setting, Cost, Invoice, Item, Exemption, Expense};
use App\Fe\Xml\Xml;
use App\Fe\Auth\{Authenticate, Config, Request, Response};
use App\Fe\Primitive;
use \Log;
use \Exception;
use \Carbon\Carbon;

class Receive extends Primitive
{
    public function __construct()
    {
        $this->token = Authenticate::getToken();
        $this->azienda = (new Config)->getDatiAnagraficiCommittente();
        $this->config = new Config;
    }

    public function init()
    {
        $contents = $this->readResponse($this->call());

        if($contents)
        {
            foreach($contents as $content)
            {

                $cost = $this->costExists($content);
// if(is_null($cost))
// {
//     dd('content', $content);
// } else {
// 	dd('cost', $cost);
// }
                if(is_null($cost))
                {
                    $cost = $this->insertInvoice($content);

                    if($cost)
                    {
                        $this->notify($cost, "costo inserito correttamente", 'info');
                        $cost->update(['data_ricezione' => Carbon::parse($content->creationDate)->format('d/m/Y')]);
                    }
                }
                else
                {
                    $this->notify($cost, "costo ".trim($content->invoices[0]->number)." già presente", 'info');
                    $cost->update(['data_ricezione' => Carbon::parse($content->creationDate)->format('d/m/Y')]);
                }
            }
            $this->notify($this, "terminato", 'info');
            $this->updateDate();
            return 'done';
        }
        else
        {
            $this->notify($this, "impossibile leggere risposta da SDI");
        }

        return 'done';
    }

    public function costExists($content)
    {
        $company = Company::where('piva', $content->sender->vatCode)->first();
        if(is_null($company) && !is_null($content->sender->fiscalCode))
        {
            $company = Company::where('cf', $content->sender->fiscalCode)->first();
        }

        if(is_null($company))
        {
            return null;
        }

        $cost = Cost::where('company_id', $company->id)
                ->where('data', Carbon::parse($content->invoices[0]->invoiceDate)->format('Y-m-d'))
                ->where('fe_id', $this->getCodeFromFilename($content->filename))
                ->first();
        return $cost;
    }

    public function call()
    {
        $request = new Request($this->config->getInvoiceListIn());
        $request->addHeader('Accept', 'application/json');
        $request->addHeader('Authorization', 'Bearer ' .  $this->token);
        return $request->get();
    }

    public function readResponse($response)
    {
        if ($response->status == 200)
        {
            $data = json_decode($response->body);
            if($data->totalElements <= 0)
            {
                $this->notify($this, "RECEIVE nessuna nuova fattura ricevuta.", 'info');
                return false;
            }
            return $data->content;
        }

        $this->notify($this, "RECEIVE errore nella risposta http: status={$response->status}; body={$response->body}");
        return false;
    }


    public function insertInvoice($content)
    {
        $sender = $content->sender;
        $code = $this->getFiscalCode($sender);

        $invoice = $content->invoices[0];
        $number = trim($invoice->number);
        $date = Carbon::parse($invoice->invoiceDate);

        $invoice = $this->config->getInvoiceById('in', $content->id, $this->token);

        if($invoice)
        {
            $xml = (new Xml)->getXml(base64_decode($invoice->file), $invoice->filename, 'in');

            if ($xml === false)
            {
                $this->notify($this, "RECEIVE impossibile decriptare il file ".$content->filename.' da '.$content->sender->description);
                return false;
            }
// return $xml;
            $company = Company::where('piva', $code)->orWhere('cf', $code)->first();
            if(is_null($company))
            {
                $header = $xml->FatturaElettronicaHeader;
                $arr = ['supplier' => 1, 'partner' => 0];
                $company = $this->addCompany($header, $code, $sender, $arr);
                $company->update(['client_id' => Client::Lead()->id]);
            }
            else
            {
                $this->notify($this, "RECEIVE azienda ({$sender->description}) già presente", 'info');
            }

            return $this->addCost($xml, $company, $number, $date, $content->filename);
        }

        $this->notify($this, "RECEIVE problem getting invoice with id=".$content->id, " numero: ".$number);
        return false;
    }


    public function addCost($xml, $company, $number, $date, $filename)
    {
        $imponibile = 0;
        $iva = 0;
        $perc_iva = 0;
        foreach ($xml->FatturaElettronicaBody->DatiBeniServizi->DatiRiepilogo as $dr)
        {
            $imponibile += floatval($dr->ImponibileImporto);
            $iva += floatval($dr->Imposta);
        }

        // $imponibile = abs($imponibile);
        // $iva = abs($iva);

        if ($imponibile != 0)
        {
            $perc_iva = (100 * abs($iva)) / abs($imponibile);
        }
        $totale = $imponibile + $iva;

        //nota di accredito
        if ($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->TipoDocumento == "TD04")
        {
            if($imponibile > 0)
            {
                $imponibile *= -1;
            }
            if($totale > 0)
            {
                $totale *= -1;
            }
        }

        $scadenza = '';
        $rate = '';
        if (isset($xml->FatturaElettronicaBody->DatiPagamento))
        {
            $date_pays = array();
            foreach ($xml->FatturaElettronicaBody->DatiPagamento as $dati)
            {
                foreach ($dati->DettaglioPagamento as $dettaglio)
                {
                    if (isset($dettaglio->DataScadenzaPagamento))
                    {
                        $date_pays[] = $dettaglio->DataScadenzaPagamento;
                    }
                }
            }
            if (count($date_pays) > 0)
            {
                sort($date_pays);
                $scadenza = end($date_pays);
                $scadenza = Carbon::parse($scadenza);

                if( $scadenza->diffInYears(Carbon::today(), true) > 1 )
                {
                    $scadenza = $date;
                }

                reset($date_pays);
                if(count($date_pays) > 1)
                {
                    for ($i=0; $i<count($date_pays); $i++)
                    {
                        $date_pays[$i] = Carbon::parse($date_pays[$i])->format('d/m/Y');
                    }
                    $rate = implode(';', $date_pays);
                }

            }
        }
        if ($scadenza == '')
        {
            $scadenza = $date;
        }

        $cost = Cost::create([
            "company_id" => $company->id,
            "expense_id" => $this->idSpesaDefault(),
            "numero" => $number,
            "anno" => $date->format('Y'),
            "data" => $date->format('d/m/Y'),
            "imponibile" => $this->decimal($imponibile),
            "iva" => $this->decimal($iva),
            "totale" => $this->decimal($totale),
            "data_scadenza" => $scadenza->format('d/m/Y'),
            "rate" => ($rate == '') ? null : $rate,
            "fe_id" => $this->getCodeFromFilename($filename)
        ]);

        $this->notify($cost, "RECEIVE inserito nuovo costo da ".$company->rag_soc, 'info');

        if((new Xml)->rename($filename, $cost, 'in') === false)
        {
            $this->notify($cost, "RECEIVE errore rinominando {$filename}");
        }

        if(isset($xml->FatturaElettronicaBody->Allegati))
        {
            try
            {
                (new Xml)->savePdf($xml, $cost);
            }
            catch(\Exception $e)
            {
                $this->notify($cost, "RECEIVE errore creando il pdf $number");
            }
        }
        return $cost;
    }

    private function updateDate()
    {
        $setting = Setting::where('model', 'Fe')->first();
            $fields = $setting->fields;
            $fields['last_receive'] = Carbon::today()->subDays(15)->format('Y-m-d');
            $setting->fields = $fields;
        $setting->save();
    }




}
