<?php

namespace App\Fe\Auth;

use Areaseb\Core\Models\Setting;
use \Log;

class Config
{

    public function __construct()
	{
        $this->env = config('fe.env');
        $this->domain = Setting::fe()->domain_sdi;
        $this->user = Setting::fe()->user_sdi;
        $this->pwd = Setting::fe()->pwd_sdi;
        $this->piva = Setting::fe()->piva;
        $this->nazione = Setting::fe()->nazione;
        $this->startDateReceive = Setting::fe()->last_receive.'T00:00:00.000Z';
        $this->maxReceive = Setting::fe()->max_receive;
        $this->startDateSync = Setting::fe()->last_sync.'T00:00:00.000Z';
        $this->maxSync = Setting::fe()->max_sync;
	}

    public function getHost($auth = false)
    {
        if($this->env == 1)
        {
            $subdomain = $auth ? 'auth' : 'ws';
        }
        elseif($this->env == 0)
        {
            $subdomain = $auth ? 'demoauth' : 'demows';
        }
        else
        {
            $subdomain = 'testws';
        }
        return 'https://'.$subdomain.'.'.$this->domain;
    }

    public function auth()
    {
        return $this->getHost(true) . '/auth/signin';
    }

    public function sendInvoice()
    {
        return $this->getHost() . '/services/invoice/upload';
    }

    public function getInvoiceListIn()
    {
        $query = "?username=".$this->user."&countryReceiver=".$this->nazione."&vatcodeReceiver=".$this->piva;
        $query .= "&startDate=".$this->startDateReceive."&size=".$this->maxReceive;
        return $this->getHost() . "/services/invoice/in/findByUsername".$query;
    }

    public function getInvoiceListOut()
    {
        $query = "?username=".$this->user."&countrySender=".$this->nazione."&vatcodeSender=".$this->piva;
        $query .= "&startDate=".$this->startDateSync."&size=".$this->maxSync;
        return $this->getHost() . "/services/invoice/out/findByUsername".$query;
    }

    public function getInvoiceIn($id)
    {
        return $this->getHost() . "/services/invoice/in/$id";
    }

    public function getInvoiceOut($id)
    {
        return $this->getHost() . "/services/invoice/out/$id";
    }


    public function getInvoiceById($mode, $id_fe, $token)
    {
		try
		{
            $url = ($mode == 'in') ? $this->getInvoiceIn($id_fe) : $this->getInvoiceOut($id_fe);
			$request = new Request($url);
			$request->addHeader('Accept', 'application/json');
			$request->addHeader('Authorization', 'Bearer ' .  $token);
			$response = $request->get();

			Log::channel('fe')->info("CONFIG ottenimento fattura $mode con id: $id_fe)");

			if ($response->status == 200)
			{
				return json_decode($response->body);
			}

			if ($response->status == 400)
			{
				$data = json_decode($response->body);
				Log::channel('fe')
                    ->error("CONFIG getInvoiceById($mode,$id_fe) errore nella risposta http: codice={$data->error}; descrizione={$data->error_description}; url: {$url}");
                return false;
            }
			Log::channel('fe')->error("CONFIG getInvoiceById($mode,$id_fe) errore nella risposta http: status={$response->status}; body={$response->body}; url: {$url}");
            return false;

		}
		catch(Exception $e)
		{
			Log::channel('fe')->error("CONFIG getInvoiceById($mode): Exception (see below).");
			Log::channel('fe')->error($e);
			//Log::channel('fe')->error("url called: $url");
            return false;
		}
    }


    public function getTrasmittente()
    {
        if($this->trasmittente)
        {
            return $this->trasmittente;//todo
        }
        $arr = [
            'nazione' => 'IT',
            'piva' => $this->piva_trasmittente,

        ];
    }

    public function getDatiAnagraficiCommittente()
    {
        return Setting::fe();
    }


}
