<?php

namespace App\FeiC;

use Areaseb\Core\Models\{Client, Company, Exemption, Expense, Invoice, Item, Product, City, Notification, Setting, Country};
use \Carbon\Carbon;
use \Log;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Primitive extends Model
{
    public function getOrAddCompany($invoice) {
        // Check if provider exists
        $fe_company = $invoice['entity'];

        $fe_id = $fe_company['id'];
        $piva = $fe_company['vat_number'];
        $cf = $fe_company['tax_code'];

        $company = Company::where('fe_id', $fe_id)->first();

        if (!$company && $piva)
            $company = Company::where('piva', $piva)->first();

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
            $nation_from_name = Country::where('nome', $fe_nation_name)->orWhere('name', $fe_nation_name)->first();

            if ($nation_from_name)
                $nation = $nation_from_name->iso2;
            else
                $nation = null;

            $company = new Company;
            $company->fe_id = $fe_company['id'];
            $company->rag_soc = $fe_company['name'];
            $company->piva = $fe_company['vat_number'];
            $company->cf = $fe_company['tax_code'];
            $company->address = $fe_company['address_street'];
            $company->zip = $fe_company['address_postal_code'];
            $company->city = $fe_company['address_city'];
            $company->province = $province;
            $company->nation = $nation;
            $company->client_id = 3;
            $company->save();
        }
        else
        {
            $this->notify($this, "RECEIVE azienda ({$fe_company['name']}) già presente", 'info');
        }

        return $company;
    }



    // Old

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notificationable');
    }

    public function notify($model, $message, $error = null)
    {

        if(is_null($error))
        {
            Log::channel('fe')->error($message);
        }
        else
        {
            Log::channel('fe')->info($message);
        }

        $message_length = strlen(strval($message));

        if($message_length > 191)
        {
            $body = strval($message);
            $name = substr(strval($message), 0, 100) . " ...";
        }
        else
        {
            $body = null;
            $name = strval($message);
        }

        $user_id = 1;
        if(\Auth::user())
        {
            $user_id = auth()->user()->id;
        }


        if(is_null($error))
        {
            return Notification::create([
                'name' => $name,
                'body' => $message,
                'notificationable_id' => isset($model->id) ? $model->id : null,
                'notificationable_type' => get_class($model),
                'error' => is_null($error) ? true : false,
                'user_id' => $user_id
            ]);
        }

    }

    public function fullClass($model)
    {
        $arr = explode("\\", get_class($model));
        $class =  end($arr);
        return "Areaseb\\Core\\Models\\".$class;
    }

    public function decimal($n)
    {
        return number_format(floatval($n), 2, '.', '');
    }

    public function startsWith($haystack, $needle)
	{
	    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
	}

	public function endsWith($haystack, $needle)
	{
	    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}

    public function stripP7mIfExists($filename)
    {
        if( $this->endsWith($filename, '.p7m') )
        {
            return substr($filename, 0, strrpos($filename, '.p7m'));
        }
        return $filename;
    }

    /**
     * transform ARUBA numero progressivo fattura in numero per il CRM
     * @param  [string] $str
     * @return [string]
     */
    public function getCrmId($str)
    {
        $str = preg_replace("/[^0-9]/", "", $str );
        return substr($str, 0, -2);
    }

    /**
     * get keys from config.fe.staus
     * @param  [arr] $arr
     * @return [int]
     */
    public function getStatusKeys($arr)
    {
        return array_keys($arr);
    }

    /**
     * get int staus from value
     * @param  [arr] $arr
     * @param  [string] $value
     * @return [int]
     */
    public function getStatusKeysFromValue($arr, $value)
    {
        return array_search($value, $arr);
    }

    /**
     * check if we have an invoice in the CRM with number and date
     * @param  [obj] $invoice [obj from Xml]
     * @param  [str] $filename [original filename]
     * @param  [boolean] $sync [true || null]
     * @return [collection]
     */
    public function doesInvoiceExists($invoice, $filename, $sync = null)
    {
    	if(strpos($invoice->number, '#') > 0){
    		list($num) = explode('/', $invoice->number);

    		$i = Invoice::where('numero', $num)
	                    ->whereDate('data', Carbon::parse($invoice->invoiceDate)->format('Y-m-d'))
	                    ->where('fe_id', $this->getCodeFromFilename($filename))
	                    ->where('tipo', 'U')
	                    ->first();

    	} else {
	        $num = $this->getCrmId($invoice->number);

	        $i = Invoice::where('numero', $num)
	                    ->whereDate('data', Carbon::parse($invoice->invoiceDate)->format('Y-m-d'))
	                    ->where('fe_id', $this->getCodeFromFilename($filename))
	                    ->first();
	    }

        if($sync)
        {
            return $i;
        }


        if(!is_null($i))
        {
            return $i;
        }

        return Invoice::where('numero', $this->getCrmId($invoice->number))
                    ->whereDate('data', Carbon::parse($invoice->invoiceDate)->format('Y-m-d'))
                    ->first();
    }


    /**
     * add company
     * @param [obj] $Sede    [from xml]
     * @param [str] $code
     * @param [obj] $subject [receiver or sender]
     * @param [arr] $arr    [prebuild arr]
     */
    public function addCompany($header, $code, $subject, $arr)
    {
        if(isset($arr['supplier']))
        {
            if($arr['supplier'])
            {
                $sede = $header->CedentePrestatore->Sede;
            }
            else
            {
                $sede = $header->CessionarioCommittente->Sede;
            }
        }
        else
        {
            $sede = $header->CessionarioCommittente->Sede;
        }

        $citta = ucwords(strtolower($sede->Comune));

        $data = [
            'rag_soc' => $subject->description,
            'piva' => $code,
            'cf' => $code,
            'address' => ucwords(strtolower($sede->Indirizzo)),
            'zip' => $sede->CAP,
            'city' => $citta,
            'province' => City::provinciaFromSigla($sede->Provincia, $sede->CAP),
            'nation' => $this->getCountry($subject),
            'lang' => ($this->getCountry($subject) == 'IT') ? 'it' : 'en',
            'sdi' => $header->DatiTrasmissione->CodiceDestinatario
        ] + $arr;


        $company = Company::create($data);
        if($company->provincia)
        {
            $company->update(['city_id' => City::getCityIdFromData($company->province, $company->nation, $company->city )]);
        }

        Log::channel('fe')->info("inserita nuova azienda con({$subject->description})");
        return $company;
    }

    /**
     * default IT if nation is missing
     * @param  [obj] $subject
     * @return [str]
     */
    public function getCountry($subject)
    {
        $country = $subject->countryCode;
        if($country == '' || is_null($country))
        {
            return 'IT';
        }
        return $country;
    }

    /**
     * check existance of PIVA or CF and return
     * @param  [type] $subject [description]
     * @return [type]          [description]
     */
    public function getFiscalCode($subject)
    {
        $code_vat = (isset($subject->vatCode) && $subject->vatCode != '') ? $subject->vatCode : '';
        $code_fs = (isset($subject->fiscalCode) && $subject->fiscalCode != '') ? $subject->fiscalCode : '';
        return ($code_vat != '') ? $code_vat : $code_fs;
    }

    /**
     * get 5 rnd chars from filename
     * @param  [str] fe file name (with or without .p7m)
     * @return [str]
     */
    public function getCodeFromFilename($str)
    {
        if(strpos($str, '.p7m'))
        {
            $str = str_replace('.p7m', '', $str);
        }

        $arr = explode('.',$str);
        return substr($arr[0],-5,5);
    }
    /**
     * get fe_id for all connettori
     * @param  [null|string] $str
     * @return [null|string]
     */
    public function getFeId($str)
    {
        if(is_null($str))
        {
            return null;
        }
        if(strpos($str,'.xml') !== false)
        {
            return $this->getCodeFromFilename($str);
        }
        return $str;
    }

    /**
     * check if obj is SimpleXMLElement
     * @param  [type]  $obj [description]
     * @return boolean      [description]
     */
    public function isXmlElement($obj)
    {
        if(is_object($obj))
        {
            if( get_class($obj) == "SimpleXMLElement" )
            {
                return $obj;
            }
        }
        return false;
    }

    /**
     * get our filename from sdi filename
     * @param  [str] fe file name (with or without .p7m)
     * @return [str]
     */
    public function getCrmFilename($str, $invoice)
    {
        $arr1 = explode('.',$str);
        $arr2 = explode('_',$arr1[0]);
        return $arr2[0].'_'.$invoice->id.".xml";
    }

    /**
     * return id expense "Da Categorizzare"
     * @return [int]
     */
    public function idSpesaDefault()
    {
        $default = Expense::where('nome', 'Da Categorizzare')->first();
        if($default)
        {
            return $default->id;
        }
        return Expense::create(['nome' => 'Da Categorizzare'])->id;
    }



// XML HELPER FUNCTIONS


    public function getFormatoTrasmissione($xml)
    {
        return ucfirst(strtolower(substr($xml->FatturaElettronicaHeader->DatiTrasmissione->FormatoTrasmissione, 1, 2)));
    }

    public function getTipoDocumento($xml)
    {
        return array_search($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->TipoDocumento, config('fe.types'));
    }

    public function getMetodoPagamento($xml)
    {
        if (isset($xml->FatturaElettronicaBody->DatiPagamento))
        {
            foreach ($xml->FatturaElettronicaBody->DatiPagamento as $dati)
            {
                foreach ($dati->DettaglioPagamento as $pagamento)
                {
                    if (isset($pagamento->ModalitaPagamento))
                    {
                        return array_search($pagamento->ModalitaPagamento, config('fe.payment_methods'));
                    }
                }
            }
        }
        return "";
    }

    public function getTipoSaldo($xml)
    {
        if (isset($xml->FatturaElettronicaBody->DatiPagamento))
        {
            foreach ($xml->FatturaElettronicaBody->DatiPagamento as $dati)
            {
                foreach ($dati->DettaglioPagamento as $pagamento)
                {
                    if (isset($pagamento->ModalitaPagamento))
                    {
                        if(is_object($pagamento->ModalitaPagamento))
                        {
                            $str = array( (string) $pagamento->ModalitaPagamento )[0];
                            return config('fe.payment_modes')[$str];
                        }
                        return config('fe.payment_modes')[$pagamento->ModalitaPagamento];

                    }
                }
            }
        }
        return "";
    }

    public function getScadenza($xml)
    {
        if (isset($xml->FatturaElettronicaBody->DatiPagamento))
        {
            $date_pays = [];
            foreach ($xml->FatturaElettronicaBody->DatiPagamento as $dati)
            {
                foreach ($dati->DettaglioPagamento as $pagamento)
                {
                    if (isset($pagamento->DataScadenzaPagamento))
                    {
                        $date_pays[] = $pagamento->DataScadenzaPagamento;
                    }
                }
            }

            sort($date_pays);
            return Carbon::parse(end($date_pays));
        }
        return Carbon::parse($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Data);
    }


    public function getRate()
    {
        if (isset($xml->FatturaElettronicaBody->DatiPagamento))
        {
            $date_pays = array();
            foreach ($xml->FatturaElettronicaBody->DatiPagamento as $dati)
            {
                foreach ($dati->DettaglioPagamento as $pagamento)
                {
                    if (isset($pagamento->DataScadenzaPagamento))
                    {
                        $date_pays[] = $pagamento->DataScadenzaPagamento;
                    }
                }
            }
            if (count($date_pays) > 1)
            {
                sort($date_pays);
                for ($i=0; $i<count($date_pays); $i++)
                {
                    $date_pays[$i] = Carbon::parse($date_pays[$i])->format('d/m/Y');

                }
                return implode(';', $date_pays);
            }
        }
        return null;
    }


    public function getBollo($xml)
    {
        $datiGeneraliDocumento = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento;
        if (isset($datiGeneraliDocumento->DatiBollo->ImportoBollo))
        {
            return floatval($datiGeneraliDocumento->DatiBollo->ImportoBollo);
        }
        return '0.00';
    }


    public function getDatiDDT($xml)
    {
        $n_ddt = null;
        $data_ddt = null;
        $dgd = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento;
        if (isset($dgd))
        {
            foreach ($dgd->DatiDDT as $dati)
            {
                if(is_null($n_ddt))
                {
                    $n_ddt = $dati->NumeroDDT;
                }
                if(is_null($data_ddt))
                {
                    $data_ddt = Carbon::parse($dati->DataDDT)->format('d/m/Y');
                }
            }
        }

        return (object) [
            'numero' => $n_ddt,
            'data' => $data_ddt
        ];
    }

    public function getDatiPA($xml)
    {
        $pa_n_doc = null;
        $pa_cup = null;
        $pa_cig = null;
        $pa_data = null;
        if (isset($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->DatiOrdineAcquisto))
        {
            $doa = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->DatiOrdineAcquisto;
            if (isset($doa->IdDocumento))
            {
                $pa_n_doc = $doa->IdDocumento;
            }
            if (isset($doa->CodiceCUP))
            {
                $pa_cup = $doa->CodiceCUP;
            }
            if (isset($doa->CodiceCIG))
            {
                $pa_cig = $doa->CodiceCIG;
            }
            if (isset($doa->Data))
            {
                $pa_data = Carbon::parse($doa->Data)->format('d/m/Y');
            }
        }

        return (object) [
            'numero' => $pa_n_doc,
            'cup' => $pa_cup,
            'cig' => $pa_cig,
            'data' => $pa_data
        ];
    }


    public function getTotale($xml)
    {
        $imponibile = 0;
        $iva = 0;
        $DatiRiepilogo = $xml->FatturaElettronicaBody->DatiBeniServizi->DatiRiepilogo;
        foreach($DatiRiepilogo as $dr)
        {
            $imponibile += floatval($dr->ImponibileImporto);
            $iva += floatval($dr->Imposta);
        }

        return (object) [
            'imponibile' => $this->decimal($imponibile),
            'iva' => $this->decimal($iva),
            'totale' => $this->decimal($iva) + $this->decimal($imponibile)
        ];
    }


    /**
     * [addItems read DettaglioLinee loop and load item in invoice]
     * @param [eloquent] $invoice [description]
     * @param [obj]      $xml
     * @return [boolean]
     */
    public function addItems($invoice, $xml)
    {
        $DettaglioLinee = $xml->FatturaElettronicaBody->DatiBeniServizi->DettaglioLinee;
        if (isset($DettaglioLinee))
		{
            foreach($DettaglioLinee as $dl)
            {
                $iva = 0;
                $iva_perc = intval($dl->AliquotaIVA);
                if ($iva_perc > 0)
                {
                    $iva = floatval(floatval($dl->PrezzoUnitario) * $iva_perc / 100);
                }
                $sconto = 0;
                if (isset($dl->ScontoMaggiorazione->Percentuale))
                {
                    $sconto = floatval($dl->ScontoMaggiorazione->Percentuale);
                }

                $exemption_id = null;
                if (isset($dl->Natura))
                {
                    $exemption_id = Exemption::getIdByCode($dl->Natura);
                }

                $item = new Item;
                    $item->invoice_id = $invoice->id;
                    $item->product_id = Product::default();
                    $item->descrizione = $dl->Descrizione;
                    $item->qta = $this->decimal($dl->Quantita);
                    $item->importo = $this->decimal($dl->PrezzoUnitario);
                    $item->perc_iva = intval($dl->AliquotaIVA);
                    $item->iva = $iva;
                    $item->sconto = $sconto;
                    $item->exemption_id = $exemption_id;
                $item->save();

                $this->notify($invoice, "SYNC aggiunto item" .$item->id. " in fattura ".$invoice->id, 'info');
            }
        }
    }





}
