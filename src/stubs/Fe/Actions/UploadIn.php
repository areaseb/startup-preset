<?php

namespace App\Fe\Actions;

use Areaseb\Core\Models\{Cost, Invoice, Item, Exemption, Expense, City, Media, Setting, Client,Company};
use App\Fe\Xml\Xml;
use App\Fe\Primitive;
use \Log;
use \Exception;
use \Storage;
use \Carbon\Carbon;

class UploadIn extends Primitive
{

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function init($id = null)
    {
        $xml = $this->getXml($this->file);
        $company = $this->companyExists($xml);

        if(is_null($company))
        {
            $company = $this->createCompany($xml);
            Log::channel('fe')->info("inserita nuova azienda con({$company->rag_soc})");
        }

        $cost = $this->exists($xml);


        if(is_null($cost))
        {
            //return 'not found';
            $cost = $this->add($xml, $company, $id);
            $this->notify($cost, "costo inserito correttamente", 'info');
        }

        $cost->update(['fe_id' => $this->getCodeFromFilename($this->file->getFilename())]);


        if($this->isXmlElement($this->file))
        {
            return $this->moveInFolder($this->file, $cost);
        }

        $arr = explode('/', $this->file->getRealPath());
        $originaFileName = end($arr);
        $dbFileName = $cost->anno.'/'.$originaFileName;

        if(!$cost->media()->where('filename', $dbFileName)->exists())
        {
            return $this->rename($this->file, $cost);
        }
        return 'aready done';


    }



    public function getXml($file)
    {
        if($this->isXmlElement($file))
        {
            return $file;
        }
        try
        {
            if( strpos($file, "https") !== false)
            {
                $content = file_get_contents($file);
            }
            else
            {
                $content = file_get_contents($file->getRealPath());
            }
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


    public function getData($xml)
    {
        return Carbon::parse($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Data);
    }

    public function getNumero($xml)
    {
        return trim(str_replace('FPR', '', $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero));
    }

    public function getNumeroV2($xml)
    {
        return $this->getCrmId($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero);
    }

    public function getTotale($xml)
    {
        $dati = $this->getPercIvaImponibileTotale($xml);
        return $dati->totale;
    }

    public function getPiva($xml)
    {
        if(isset($xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici))
        {
            return $xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdCodice;
        }
        return $xml->FatturaElettronicaHeader->CedentePrestatore->IdFiscaleIVA->IdCodice;
    }

    public function getCompanyId($xml)
    {
        return Company::where('piva', $this->getPiva($xml))->first()->id;
    }

    public function getNation($sede)
    {
        $country = $sede->Nazione;
        if($country == '' || is_null($country))
        {
            return 'IT';
        }
        return $country;
    }

    public function companyExists($xml)
    {
        try
        {
            if(isset($xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici))
            {
                $piva = $xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdCodice;
            }
            else
            {
                $piva = $xml->FatturaElettronicaHeader->CedentePrestatore->IdFiscaleIVA->IdCodice;
            }

        }
        catch (\Exception $e)
        {
            dd($xml->FatturaElettronicaHeader->CedentePrestatore);
        }

        return Company::where('piva', $piva)->first();
    }

    public function createCompany($xml)
    {
        $sede = $xml->FatturaElettronicaHeader->CedentePrestatore->Sede;
        $datiAnagrafici =  $xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici;
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
            'supplier' => 1,
        ];

        $company = Company::create($data);
        if($company->province)
        {
            $company->update(['city_id' => City::getCityIdFromData($company->province, $company->nation, $company->city )]);
        }
        return $company;
    }

    public function exists($xml)
    {

        $anno = $this->getData($xml)->format('Y');

        $cost = Cost::where('anno', $anno)
                ->where('numero', $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero)
                ->where('company_id', $this->getCompanyId($xml))
                ->first();

        if(!is_null($cost))
        {
            return $cost;
        }

        $cost = Cost::where('anno', $anno)
                    ->where('numero', $this->getNumero($xml))
                    ->where('totale', $this->getTotale($xml))
                    ->first();
        if(!is_null($cost))
        {
            return $cost;
        }

        $cost = Cost::where('anno', $anno)
                    ->where('numero', $this->getNumeroV2($xml))
                    ->where('totale', $this->getTotale($xml))
                    ->first();

        if(!is_null($cost))
        {
            return $cost;
        }

        $cost = Cost::where('data', $this->getData($xml))
                    ->where('company_id', $this->getCompanyId($xml))
                    ->where('totale', $this->getTotale($xml))
                    ->first();

        if(!is_null($cost))
        {
            return $cost;
        }

        return null;

    }

    public function add($xml, $company, $id)
    {
        $dati = $this->getPercIvaImponibileTotale($xml);
        $date = $this->getScadenzaRate($xml);

        return Cost::create([
            "company_id" => $company->id,
            "expense_id" => $this->idSpesaDefault(),
            "numero" => $this->getNumero($xml),
            "anno" => $this->getData($xml)->format('Y'),
            "data" => $this->getData($xml)->format('d/m/Y'),
            "imponibile" => $dati->imponibile,
            "iva" => $dati->perc_iva,
            "totale" => $dati->totale,
            "data_scadenza" => $date->scadenza,
            "rate" => $date->rate,
            "fe_id" => $this->getFeId($id)
        ]);
    }




    public function getPercIvaImponibileTotale($xml)
    {
        $imponibile = 0;
        $iva = 0;
        $perc_iva = 0;
        foreach ($xml->FatturaElettronicaBody->DatiBeniServizi->DatiRiepilogo as $dr)
        {
            $imponibile += floatval($dr->ImponibileImporto);
            $iva += floatval($dr->Imposta);
        }

        if ($imponibile != 0)
        {
            $perc_iva = 100 * abs($iva) / abs($imponibile);
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

        return (object) [
                'perc_iva' => $this->decimal($perc_iva),
                'imponibile' => $this->decimal($imponibile),
                'totale' => $this->decimal($totale),
            ];
    }

    public function getScadenzaRate($xml)
    {

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
                    $scadenza = $this->getData($xml);
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
            $scadenza = $this->getData($xml);
        }

        return (object) [
                'scadenza' => $scadenza->format('d/m/Y'),
                'rate' => ($rate == '') ? null : $rate,
            ];
    }



    private function rename($file, $cost)
    {

        if(get_class($file) == "Illuminate\Http\UploadedFile")
        {
            $originaFileName = $file->getClientOriginalName();
            $arr2 = explode('.', $originaFileName);
            $newFileName = $arr2[0]."_".$cost->id.".".$arr2[1];
            $newFile = $file->getFilename();
            $dbFileName = $cost->anno.'/'.$newFileName;
        }
        else
        {
            $file = $file->getRealPath();
            $arr = explode('/', $file);
            $originaFileName = end($arr);

            $arr2 = explode('.', $originaFileName);
            $newFileName = $arr2[0]."_".$cost->id.".".$arr2[1];
            $newFile = str_replace($originaFileName, $newFileName, $file);
            $dbFileName = $cost->anno.'/'.$newFileName;
        }

        $path = 'public/fe/ricevute/'.$cost->anno.'/';

        if(get_class($file) == "Illuminate\Http\UploadedFile")
        {
            Storage::disk('public')->putFileAs(
                'fe/ricevute/'.$cost->anno.'', $file, $newFileName
            );
        }
        else
        {
            if (file_exists($newFile))
            {
                unlink($newFile);
            }
            try
            {
                Storage::move($path.$originaFileName, $path.$newFileName);
            }
            catch(\Exception $e)
            {
                dd($e, $path.$originaFileName, $path.$newFileName);
            }
        }



        if(!$cost->media()->where('filename', $dbFileName)->exists())
        {
            try
            {
                Media::create([
                    'description' => 'Fattura XML '.$cost->numero,
                    'mime' => 'doc',
                    'filename' => $dbFileName,
                    'mediable_id' => $cost->id,
                    'mediable_type' => $cost->full_class,
                    'media_order' => 1,
                    'size' => round(Storage::size($path.$newFileName)/1000)
                ]);

            }
            catch (\Exception $e)
            {
                dd($e, $cost);
            }
        }
        return $dbFileName;
    }

}
