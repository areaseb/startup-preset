<?php

namespace App\Fe\Auth;

use App\Fe\Auth\Response;
use \Log;

class Request
{
    private $url;
    private $headers;

    function __construct($url, $headers = null)
    {
        $this->headers = is_null($headers) ? ['Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'] : $headers;
        $this->url = $url;
    }

    public function addHeader($name, $value)
	{
		$this->headers[$name] = $value;
		return $this;
	}


    public function send($data = null, $json = false)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);

        if (!is_null($data))
        {
            if (!$json)
            {
                curl_setopt($ch, CURLOPT_POST, true);
            }
            else
            {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        else
        {
            //get
        }

        $http_header = array();
        foreach ($this->headers as $name => $value)
        {
            $http_header[] = $name . ": " . $value;
        }
        if (count($http_header) > 0)
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
        }

        curl_setopt($ch, CURLOPT_HEADER, true); // return headers
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return web page
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow redirects
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10); // stop after 10 redirects
        curl_setopt($ch, CURLOPT_ENCODING, ""); //supported encoding types (identity|deflate|gzip)
        if (substr($this->url, 4, 1) == 's')
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $results = [
            'header' => substr($response, 0, $header_size),
            'body' => substr($response, $header_size),
            'error' => curl_error($ch),
            'status' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'url' => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL)
        ];

        curl_close($ch);

        return new Response($results);
    }

    public function get()
    {
        return $this->send();
    }

    public function post($data)
    {
        return $this->send($data);
    }

    public function postJson($data)
    {
        $this->addHeader('Content-Type', 'application/json;charset=UTF-8');
        $json_data = json_encode($data);
        return $this->send($json_data, true);
    }


}
