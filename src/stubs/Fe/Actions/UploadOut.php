<?php

namespace App\Fe\Actions;

use Areaseb\Core\Models\{Invoice, Item, Exemption, Product,City, Media, Setting,Client,Company};
use App\Fe\Xml\Xml;
use App\Fe\Primitive;
use \Log;
use \Exception;
use \Storage;
use \Carbon\Carbon;

class UploadOut extends Primitive
{

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function init()
    {
        $xml = $this->getXml($this->file);
        $company = $this->companyExists($xml);
        if(is_null($company))
        {
            $company = $this->createCompany($xml);
        }

        $invoice = $this->exists($xml, $company);
        if(is_null($invoice))
        {
            $invoice = $this->add($xml, $company);
        }
        if(is_null($invoice->fe_id))
        {
            $invoice->update(['fe_id' => $this->getCodeFromFilename($this->file->getFilename())]);
        }
        return $this->rename($this->file, $invoice);
    }


    public function getXml($file)
    {
        try
        {
            $content = file_get_contents($file->getRealPath());
        }
        catch(\Exception $e)
        {
            dd($e, $file);
        }
        try
        {
            libxml_use_internal_errors(true);
            return new \SimpleXMLElement($content);
        }
        catch(\Exception $e)
        {
            dd($e);
        }
    }


    private function getData($xml)
    {
        return Carbon::parse($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Data);
    }

    private function getNumero($xml)
    {
        return trim(str_replace('FPR', '', $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero));
    }

    private function getNumeroV2($xml)
    {
        return $this->getCrmId($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero);
    }

    private function getImponibile($xml)
    {
        $dati = $this->getTotale($xml);
        return $dati->imponibile;
    }

    private function getPiva($xml)
    {
        if(isset($xml->FatturaElettronicaHeader->CessionarioCommittente->DatiAnagrafici))
        {
            return $xml->FatturaElettronicaHeader->CessionarioCommittente->DatiAnagrafici->IdFiscaleIVA->IdCodice;
        }
        return $xml->FatturaElettronicaHeader->CessionarioCommittente->IdFiscaleIVA->IdCodice;
    }

    private function getCompanyId($xml)
    {
        return Company::where('piva', $this->getPiva($xml))->first()->id;
    }

    private function getNation($sede)
    {
        $country = $sede->Nazione;
        if($country == '' || is_null($country))
        {
            return 'IT';
        }
        return $country;
    }

    private function companyExists($xml)
    {
        try
        {
            if(isset($xml->FatturaElettronicaHeader->CessionarioCommittente->DatiAnagrafici))
            {
                $piva = $xml->FatturaElettronicaHeader->CessionarioCommittente->DatiAnagrafici->IdFiscaleIVA->IdCodice;
            }
            else
            {
                $piva = $xml->FatturaElettronicaHeader->CessionarioCommittente->IdFiscaleIVA->IdCodice;
            }
            $piva = trim($piva);
        }
        catch (\Exception $e)
        {
            dd($xml->FatturaElettronicaHeader->CessionarioCommittente);
        }

        $company = Company::where('piva', $piva)->first();
        if(is_null($company))
        {
            $piva = preg_replace("/[^0-9]/", "", $piva );
        }

        return Company::where('piva', $piva)->first();
    }

    private function createCompany($xml)
    {
        $sede = $xml->FatturaElettronicaHeader->CessionarioCommittente->Sede;
        $datiAnagrafici =  $xml->FatturaElettronicaHeader->CessionarioCommittente->DatiAnagrafici;
        $indirizzo = $sede->Indirizzo;
        if(isset($sede->NumeroCivico))
        {
            $indirizzo .= ', '.$sede->NumeroCivico;
        }

        $data = [
            'rag_soc' => $datiAnagrafici->Anagrafica->Denominazione,
            'piva' => $this->getPiva($xml),
            'cf' => $this->getPiva($xml),
            'address' => ucwords(strtolower($indirizzo)),
            'zip' => $sede->CAP,
            'city' => ucwords(strtolower($sede->Comune)),
            'province' => City::provinciaFromSigla($sede->Provincia, $sede->CAP),
            'nation' => $this->getNation($sede),
            'lang' => ($this->getNation($sede) == 'IT') ? 'it' : 'en',
        ];

        $company = Company::create($data);
        if($company->province)
        {
            $company->update(['city_id' => City::getCityIdFromData($company->province, $company->nation, $company->city )]);
        }
        $company->update(['client_id' => Client::Client()->id]);
        return $company;
    }

    private function exists($xml, $company)
    {
        $invoice = Invoice::whereYear('data',$this->getData($xml)->format('Y'))
                            ->where('numero', $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero)
                            ->where('company_id', $company->id)
                            ->first();
        if(!is_null($invoice))
        {
            return $invoice;
        }

        $invoice = Invoice::whereYear('data',$this->getData($xml)->format('Y'))
                            ->where('numero', $this->getNumero($xml))
                            ->where('company_id', $company->id)
                            ->first();
        if(!is_null($invoice))
        {
            return $invoice;
        }

        $invoice = Invoice::whereYear('data',$this->getData($xml)->format('Y'))
                        ->where('numero', $this->getNumeroV2($xml))
                        ->where('company_id', $company->id)
                        ->first();
        if(!is_null($invoice))
        {
            return $invoice;
        }

        $invoice = Invoice::where('data', $this->getData($xml))
                    ->where('company_id',$company->id)
                    ->where('imponibile', $this->getImponibile($xml))
                    ->first();
        if(!is_null($invoice))
        {
            return $invoice;
        }

        return null;

    }

    private function add($xml, $company)
    {

        $pa = $this->getDatiPA($xml);
        $ddt = $this->getDatiDDT($xml);
        $totale = $this->getTotale($xml);

        $debug = [
            'tipo_doc' => $this->getFormatoTrasmissione($xml),
            'tipo' => $this->getTipoDocumento($xml),
            'numero' => $this->getCrmId($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero),
            'numero_registrazione' => $this->getCrmId($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero),
            'data' => $this->getData($xml)->format('d/m/Y'),
            'data_registrazione' => $this->getData($xml)->format('d/m/Y'),
            'company_id' => $company->id,
            'pagamento' => $this->getMetodoPagamento($xml),
            'tipo_saldo' => $this->getTipoSaldo($xml),
            'data_saldo' => $this->getScadenza($xml)->format('d/m/Y'),
            'data_scadenza' => $this->getScadenza($xml)->format('Y-m-d'),
            'rate' => $this->getRate($xml),
            'bollo' => $this->getBollo($xml),
            'bollo_a' => ($this->getBollo($xml)) ? 'cliente' : null,
            'pa_n_doc' => $pa->numero,
            'pa_data_doc' => $pa->data,
            'pa_cup' => $pa->cup,
            'pa_cig' => $pa->cig,
            'ddt_n_doc' => $ddt->numero,
            'ddt_data_doc' => $ddt->data,
            'status' => 7,
            'sendable' => 1,
            'imponibile' => $totale->imponibile,
            'iva' => $totale->iva,
        ];

        $invoice = new Invoice;
            $invoice->tipo_doc = $this->getFormatoTrasmissione($xml);
            $invoice->tipo = $this->getTipoDocumento($xml);
            $invoice->numero = $this->getCrmId($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero);
            $invoice->numero_registrazione = $this->getCrmId($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero);
            $invoice->data = $this->getData($xml)->format('d/m/Y');
            $invoice->data_registrazione = $this->getData($xml)->format('d/m/Y');
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

            $invoice->status = 7;
            $invoice->sendable = 1;

            $invoice->imponibile = $totale->imponibile;
            $invoice->iva = $totale->iva;

            $invoice->fe_id = $this->getCodeFromFilename($this->file->getClientOriginalName());

        $invoice->save();

        $this->addItems($invoice, $xml);

        return $invoice;
    }



    private function rename($file, $invoice)
    {
        $file = $file->getRealPath();
        $arr = explode('/', $file);
        $originaFileName = end($arr);
        $arr2 = explode('.', $originaFileName);
        $newFileName = $arr2[0]."_".$invoice->id.".".$arr2[1];
        $newFile = str_replace($originaFileName, $newFileName, $file);
        $dbFileName = $invoice->data->format('Y').'/'.$newFileName;

        if (file_exists($newFile))
        {
            unlink($newFile);
        }

        $path = 'public/fe/inviate/'.$invoice->data->format('Y').'/';

        if(!file_exists(storage_path('app/'.$path.$originaFileName)))
        {
            $path = 'public/fe/inviate/'.(intval($invoice->data->format('Y'))+1).'/';
            $dbFileName = (intval($invoice->data->format('Y'))+1).'/'.$newFileName;
        }

        try
        {
            Storage::move($path.$originaFileName, $path.$newFileName);
        }
        catch(\Exception $e)
        {
            dd($e, $path.$originaFileName, $path.$newFileName);
        }

        if(!$invoice->media()->where('filename', $dbFileName)->exists())
        {
            try
            {
                Media::create([
                    'description' => 'Fattura XML '.$invoice->numero,
                    'mime' => 'doc',
                    'filename' => $dbFileName,
                    'mediable_id' => $invoice->id,
                    'mediable_type' => $invoice->full_class,
                    'media_order' => 1,
                    'size' => round(Storage::size($path.$newFileName)/1000)
                ]);
            }
            catch (\Exception $e)
            {
                dd($e, $invoice);
            }
        }
        return $dbFileName;
    }

}
