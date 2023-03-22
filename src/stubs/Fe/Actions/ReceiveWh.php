<?php

namespace App\Fe\Actions;

use Areaseb\Core\Models\{Cost, Invoice, Item, Exemption, Expense, City, Media, Setting, Client,Company};
use App\Fe\Xml\Xml;
use App\Fe\Actions\UploadIn;
use \Log;
use \Exception;
use \Storage;
use \Carbon\Carbon;

class ReceiveWh extends UploadIn
{

    public function __construct($xml, $filename)
    {
        $this->xml = $xml;
        $this->filename = $filename;
    }

    public function main()
    {
        $company = $this->companyExists($this->xml);

        if(is_null($company))
        {
            $company = $this->createCompany($this->xml);
            Log::channel('fe')->info("inserita nuova azienda con({$company->rag_soc})");
        }
        $cost = $this->exists($this->xml);

        if(is_null($cost))
        {
            $cost = $this->add($this->xml, $company, $this->filename);
            $this->notify($cost, "costo inserito correttamente", 'info');
            $cost->update(['data_ricezione' => Carbon::today()->format('d/m/Y')]);

        }

        if(isset($xml->FatturaElettronicaBody->Allegati))
        {
            return (new Xml)->savePdf($this->xml, $cost);
            $this->notify($cost, "RECEIVEWH errore creando il pdf $number");
        }

        return $this->moveInFolder($this->filename, $cost);
    }


    private function moveInFolder($filename, $cost)
    {

        if( $this->endsWith($filename, '.p7m') )
        {
            $filename = substr($filename, 0, strrpos($filename, '.p7m'));
        }

        $path = 'public/fe/ricevute/'.$cost->anno.'/';
        $arr = explode('.', $filename);
        $newFileName = $arr[0]."_".$cost->id.".".$arr[1];

        try
        {
            Storage::move('public/fe/ricevute/'.$filename, $path.$newFileName);
        }
        catch(\Exception $e)
        {
            dd($e, 'public/fe/ricevute/'.$filename, $path.$newFileName);
        }
        $dbFileName = $cost->anno.'/'.$newFileName;

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
