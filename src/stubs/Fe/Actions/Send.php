<?php

namespace App\Fe\Actions;

use Areaseb\Core\Models\{Invoice, Item, Exemption, Company, Media};
use App\Fe\Xml\Xml;
use Areaseb\Core\Models\Fe\FatturaCheck;
use App\Fe\Primitive;
use App\Fe\Auth\{Authenticate, Config, Request, Response};
use \Log;
use \Exception;


class Send extends Primitive
{

    public function __construct(Invoice $invoice, $cedente)
    {
        $this->invoice = $invoice;
        $this->client = $invoice->company;
        $this->items = $invoice->items;
        $this->trasmittente = config('fe.trasmittente');
        $this->piva_trasmittente = config('fe.piva_trasmittente');
        $this->cedente = $cedente;
        $this->types = config('fe.types');
        $this->sendables = config('fe.sendables');
        $this->payment_methods = config('fe.payment_methods');
        $this->version = config('fe.version');
        $this->xml = new Xml;
    }

    public function init()
    {

        if(in_array($this->invoice->sendable, $this->sendables))
        {
            $imponibile_testa = $this->invoice->imponibile;
            $imponibile_testa = $this->invoice->iva;

            try
            {
            	if($this->invoice->tipo_doc == 'Pu'){
                	$template = $this->xml->createXml('FPA12');
                } else {
                	$template = $this->xml->createXml($this->version);
                }
                $header = $template->FatturaElettronicaHeader;
                $body = $template->FatturaElettronicaBody;

                    $this->datiTrasmissione($header);
                    $this->datiCedente($header);
                    $this->datiCommittente($header);
                    $this->datiGeneraliDocumento($body);
                    $this->datiBeniServizi($body);
                    $this->datiPagamento($body);
//dd($this->xml->saveXml($template, 'inviate'));

                $fcResponse = (new FatturaCheck(['xml' => $template]))->init();

                if(!$fcResponse['is_empty'])
                {
                    if(!$fcResponse['isValid'])
                    {
                        return $fcResponse['errors'][0];
                    }
                }

                $response = $this->call($this->xml->saveXml($template, 'inviate'));


                try
                {
                    $result = $this->checkResponse($response);
                    if(is_null($result))
                    {
                        Log::channel('fe')->error('$this->checkResponse($response) is null' );
                    }
                    if(is_array($result))
                    {
                        Log::channel('fe')->error('$this->checkResponse($response) is array' );
                    }

                    if (is_a($result, 'Response')) {
                      Log::channel('fe')->error('$this->checkResponse($response) is of Class Response' );
                    }

                    if (is_object($result)) {
                      Log::channel('fe')->error('$this->checkResponse($response) is an Object' );
                    }
                }
                catch(\Exception $e)
                {
                    $this->notify($this->invoice, "SEND: eccezione in response");
                    Log::channel('fe')->error($e);
                    return 'error in response';
                }

                try
                {
                    $dt = $header->DatiTrasmissione;
                    $filename = $this->invoice->data->format('Y').'/'. $dt->IdTrasmittente->IdPaese . $dt->IdTrasmittente->IdCodice . '_' . $this->invoice->id . '.xml';

                    if(!$this->invoice->media()->where('filename', $filename)->exists())
                    {
                        Media::create([
                            'description' => 'Fattura XML '.$this->invoice->numero,
                            'mime' => 'doc',
                            'filename' => $filename,
                            'mediable_id' => $this->invoice->id,
                            'mediable_type' => get_class($this->invoice),
                            'media_order' => 1,
                            'size' => 4
                        ]);
                    }
                    return 'done';
                }
                catch(\Exception $e)
                {
                    $this->notify($this->invoice, "Xml non salvato");
                    Log::channel('fe')->error($e);
                    return 'error saving file';
                }

            }
            catch(Exception $e)
            {
                $this->notify($this->invoice, "SEND: eccezione (vedi log)");
                Log::channel('fe')->error($e);
                return 'error';
            }
        }

        $message = "SEND: fattura (id=" .$this->invoice->id . ") non inviabile: sendables=".$this->invoice->sendable;
        $this->notify($this->invoice, $message);
        return 'not sendable';
    }

    public function call($xml)
    {
        $request = new Request(
            (new Config)->sendInvoice(),
             ['Content-Type' => 'application/json;charset=UTF-8']
         );
        $request->addHeader('Authorization', 'Bearer ' .  Authenticate::getToken());

        $arr = [
            'dataFile' => base64_encode($xml),
            'credential' => '',
            'domain' => ''
        ];

        return $request->send(json_encode($arr), true);
    }

    public function checkResponse($response)
    {
        if ($response->status == 200)
        {
            $data = json_decode($response->body);

            if ($data->errorCode == "0000")
            {
                Log::channel('fe')->info("send(): fattura inviata: id=".$this->invoice->id);
                $this->invoice->update([
                    'status' => 1, //status = presa in carico (1)
                    'fe_id' => $this->getCodeFromFilename($data->uploadFileName)
                ]);
                return true;
            }
            else
            {
                $this->notify($this->invoice, "SEND: errore nella risposta http: codice={$data->errorCode}; descrizione={$data->errorDescription}");
                return false;
            }
        }

        $this->notify($this->invoice, "SEND: errore nella risposta http");
        return false;
    }

    public function datiTrasmissione($header)
    {
        $DatiTrasmissione = $header->DatiTrasmissione;

        if ($this->trasmittente)
        {
            $DatiTrasmissione->IdTrasmittente->IdPaese = $this->cedente->nazione;
            $DatiTrasmissione->IdTrasmittente->IdCodice = $this->cedente->piva;
        }
        else
        {
            $DatiTrasmissione->IdTrasmittente->IdPaese = 'IT';
            $DatiTrasmissione->IdTrasmittente->IdCodice = $this->piva_trasmittente;
        }

        if (!is_null($this->client->sdi))
        {
            $DatiTrasmissione->CodiceDestinatario = $this->client->sdi;
        }
        else
        {
            if ($this->client->pec != '')
            {
                $DatiTrasmissione->addChild('PECDestinatario', $this->client->pec);
            }
        }

        $DatiTrasmissione->ProgressivoInvio = $this->invoice->id;
        if($this->invoice->tipo_doc == 'Pu')
        {
            $DatiTrasmissione->FormatoTrasmissione = 'FPA12';
        } else {
        	$DatiTrasmissione->FormatoTrasmissione = $this->version;
        }
        

        return true;
    }

    public function datiCedente($header)
    {
        $DatiAnagrafici = $header->CedentePrestatore->DatiAnagrafici;
        $Sede = $header->CedentePrestatore->Sede;

        $DatiAnagrafici->IdFiscaleIVA->IdPaese = $this->cedente->nazione;
        $DatiAnagrafici->IdFiscaleIVA->IdCodice = $this->cedente->piva;
        $DatiAnagrafici->Anagrafica->Denominazione = $this->cedente->rag_soc;
        $DatiAnagrafici->RegimeFiscale = $this->cedente->regime;

        $Sede->Indirizzo = $this->cedente->indirizzo;
        $Sede->CAP = $this->cedente->cap;
        $Sede->Comune = $this->cedente->citta;
        $Sede->Provincia = $this->cedente->prov;
        $Sede->Nazione = $this->cedente->nazione;

        return true;
    }

    public function datiCommittente($header)
    {
        $DatiAnagrafici = $header->CessionarioCommittente->DatiAnagrafici;
        $Sede = $header->CessionarioCommittente->Sede;

        if($this->client->piva != "")
        {
            if(!$this->client->private)
            {
                $IdFiscaleIVA = $DatiAnagrafici->addChild('IdFiscaleIVA');
                $IdFiscaleIVA->addChild('IdPaese', $this->client->nation);
                $IdFiscaleIVA->addChild('IdCodice', $this->client->clean_piva);
            }
        }
        else
        {
            $DatiAnagrafici->addChild('CodiceFiscale', $this->client->cf);
        }

        if($this->client->private)
        {
            $DatiAnagrafici->addChild('CodiceFiscale', $this->client->cf);
        }
        $Anagrafica = $DatiAnagrafici->addChild('Anagrafica');

        if(strpos($this->client->rag_soc, 'À') !== false)
        {
            $Anagrafica->addChild('Denominazione', $this->client->rag_soc);
        }
        else
        {
            $Anagrafica->addChild('Denominazione', htmlentities($this->client->rag_soc));
        }


        $Sede->Indirizzo = $this->client->address;
        $Sede->CAP = $this->client->zip;
        $Sede->Comune = $this->client->city;
        if ($this->client->nazione == 'IT')
        {
            $Sede->addChild('Provincia', $this->client->prov);
        }
        $Sede->addChild('Nazione', $this->client->nation);
    }

    public function datiGeneraliDocumento($body)
    {
        $DatiGeneraliDocumento = $body->DatiGenerali->DatiGeneraliDocumento;

        if (!array_key_exists($this->invoice->tipo, $this->types))
        {
            $this->notify($this->invoice, "SEND: datiGeneraliDocumento(): tipo documento non gestito: ".$this->invoice->tipo);
            return false;
        }

        $DatiGeneraliDocumento->TipoDocumento = $this->types[$this->invoice->tipo];
        $DatiGeneraliDocumento->Data = $this->invoice->data->format('Y-m-d');

        if(intval($this->invoice->numero))
        {
        	if($this->invoice->tipo_doc == 'Pu')
	        {
	            $DatiGeneraliDocumento->Numero = 'FPA '.$this->invoice->numero.'/'.$this->invoice->data->format('y');
	        } else {
	        	$DatiGeneraliDocumento->Numero = 'FPR '.$this->invoice->numero.'/'.$this->invoice->data->format('y');
	        }            
        }
        else
        {
            $DatiGeneraliDocumento->Numero = $this->invoice->numero;
        }

        if( ($this->invoice->bollo > 0) )
        {
            $linea = $DatiGeneraliDocumento->addChild('DatiBollo');
            $linea->addChild('BolloVirtuale', "SI");
            $linea->addChild('ImportoBollo', $this->decimal($this->invoice->bollo));
        }

        $DatiGeneraliDocumento->addChild('ImportoTotaleDocumento', $this->decimal($this->invoice->total));

        if ($this->invoice->pa_n_doc)
        {
            $DatiGeneraliDocumento = $body->DatiGenerali->addChild('DatiOrdineAcquisto');
            $DatiGeneraliDocumento->addChild('IdDocumento', $this->invoice->pa_n_doc);
            if ($this->invoice->pa_cup)
            {
                $DatiGeneraliDocumento->addChild('CodiceCUP', $this->invoice->pa_cup);
            }
            if ($this->invoice->pa_cig)
            {
                $DatiGeneraliDocumento->addChild('CodiceCIG', $this->invoice->pa_cig);
            }
        }
    }

    public function datiBeniServizi($body)
    {
        $DatiBeniServizi = $body->DatiBeniServizi;

        foreach($this->items as $n => $item)
        {


            //$descrizione = $item->product->nome . ' ' .$item->descrizione;

            $descrizione = $item->descrizione;
            if(is_null($item->descrizione) || $item->descrizione == '')
            {
                $descrizione =  $item->product->nome;
            }
            
            $descrizione = $this->cleanDescription($descrizione);

            $linea = $DatiBeniServizi->addChild('DettaglioLinee');
            $linea->addChild('NumeroLinea', ($n+1));
            $linea->addChild('Descrizione', $descrizione);
            $linea->addChild('Quantita', $this->decimal($item->qta));
            $linea->addChild('PrezzoUnitario', $this->decimal($item->importo));

            if ($item->sconto > 0)
            {
                $scmag = $linea->addChild('ScontoMaggiorazione');
                $scmag->addChild('Tipo', "SC");
                $scmag->addChild('Percentuale', $this->decimal($item->sconto));
            }

            $linea->addChild('PrezzoTotale', $this->decimal($item->totale_riga));
            $linea->addChild('AliquotaIVA', $this->decimal($item->perc_iva));
            if (!is_null($item->exemption_id))
            {
                $linea->addChild('Natura', $item->exemption->codice);
                //$linea->addChild('RiferimentoNormativo', $item->exemption->nome);
            }

        }

        // if ($pay_method == 'RB**')
        // {
        //     $linea = $DatiBeniServizi->addChild('DettaglioLinee');
        //     $linea->addChild('NumeroLinea', ($this->items()->count() + 1));
        //     $linea->addChild('TipoCessionePrestazione', "AC");
        //     $linea->addChild('Descrizione', "spese di incasso");
        //     $linea->addChild('PrezzoUnitario', $this->decimal($this->invoice->spese));
        //     $linea->addChild('PrezzoTotale', $this->decimal($this->invoice->spese));
        //     $linea->addChild('AliquotaIVA', "0.00");
        //     $linea->addChild('Natura', "N2");//need changing
        // }

        foreach ($this->invoice->items_grouped_by_ex as $n => $group)
        {
            $linea = $DatiBeniServizi->addChild('DatiRiepilogo');


			if($this->invoice->split_payment)
            {
                $linea->addChild('AliquotaIVA', $this->decimal($group->perc_iva));
                $linea->addChild('ImponibileImporto', $this->decimal($group->imponibile));
                $linea->addChild('Imposta', $this->decimal($group->iva));
                $linea->addChild('EsigibilitaIVA', "S");
                $linea->addChild('RiferimentoNormativo', "Scissione dei pagamenti art. 17 ter DPR 633/72");
            }
            else
            {
	            if(is_null($group->exemption_id))
	            {
	                $linea->addChild('AliquotaIVA', $this->decimal($group->perc_iva));
	                $linea->addChild('ImponibileImporto', $this->decimal($group->imponibile));
	                $linea->addChild('Imposta', $this->decimal($group->iva));
	                $linea->addChild('EsigibilitaIVA', "I");
	            }
	            else
	            {
	                $rn = $group->riferimento_normativo;
	                if($rn)
	                {
	                    if(strlen($rn) > 96)
	                    {
	                        $rn = substr($rn, 0, 96).'...';
	                    }
	                }

	                $linea->addChild('AliquotaIVA', $this->decimal($group->perc_iva));
	                $linea->addChild('Natura', $group->natura);
	                $linea->addChild('ImponibileImporto', $this->decimal($group->imponibile));
	                $linea->addChild('Imposta', $this->decimal($group->iva));
	                $linea->addChild('RiferimentoNormativo', $rn);
	            }
	        }
        }

    }
<<<<<<< HEAD
	
	public function cleanDescription($str)
=======
    
    public function cleanDescription($str)
>>>>>>> 888c9c5b5d2b35ba7fb8f17f578083e2ec25a381
    {
        $str = str_replace('€', 'EUR', $str);
        $str = str_replace('£', 'GBP', $str);
        $str = str_replace('$', 'USD', $str);
        $str = str_replace('©',' Copyright', $str);
        $str = str_replace('®', ' Registered', $str);
        $str = str_replace('™',' Trademark', $str);
        $str = str_replace('&',' e ', $str);
        $str = str_replace('&',' e ', $str);
        $str = str_replace('’', "'", $str);
        return $str;
    }
<<<<<<< HEAD
    
=======

>>>>>>> 888c9c5b5d2b35ba7fb8f17f578083e2ec25a381
    public function datiPagamento($body)
    {
        $DatiPagamento = $body->DatiPagamento;
        if($this->invoice->rate)
        {
            $rate = explode(',', $this->invoice->rate);
            $n_rate = count($rate);
            if($this->invoice->split_payment)
            {
                $total = $this->decimal($this->invoice->imponibile);
            }
            else
            {
                $total = $this->decimal($this->invoice->total);
            }
            $amount_rata = $this->decimal($total / 3);
            $amount_payed = 0;

            $DatiPagamento->CondizioniPagamento = 'TP01';
            for($nr=0;$nr<$n_rate;$nr++)
            {
                $scadenza_rata = \Carbon\Carbon::createFromFormat('d/m/Y', trim($rate[$nr]))->format('Y-m-d');

                $linea = $DatiPagamento->addChild('DettaglioPagamento');
                $linea->addChild('ModalitaPagamento', $this->payment_methods[$this->invoice->pagamento]);
                $linea->addChild('DataScadenzaPagamento', $scadenza_rata);

                if($nr == ($n_rate -1))
                {
                    $linea->addChild('ImportoPagamento', $this->decimal( $total - $amount_payed));
                }
                else
                {
                    $linea->addChild('ImportoPagamento', $amount_rata);
                }
                $amount_payed +=  $amount_rata;
            }
        }
        else
        {
            $DatiPagamento->CondizioniPagamento = 'TP02';
            $linea = $DatiPagamento->addChild('DettaglioPagamento');
            $linea->addChild('ModalitaPagamento', $this->payment_methods[$this->invoice->pagamento]);
            $linea->addChild('DataScadenzaPagamento', $this->invoice->data_scadenza->format('Y-m-d'));
            if($this->invoice->split_payment)
            {
                $linea->addChild('ImportoPagamento', $this->decimal($this->invoice->imponibile));
            }
            else
            {
                $linea->addChild('ImportoPagamento', $this->decimal($this->invoice->total+$this->invoice->rounding));
            }
            if ($this->cedente->IBAN != '')
            {
                $linea->addChild('IBAN', str_replace(' ', '', $this->cedente->IBAN));
            }

        }
    }




}
