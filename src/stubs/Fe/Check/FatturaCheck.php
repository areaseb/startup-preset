<?php

namespace App\Fe\Check;

use \Log;
use \Exception;

class FatturaCheck
{


    public function __construct($arr)
    {
        $this->arr = $arr;
    }

    public function getXml($arr)
    {
        if(isset($this->arr['filename']))
        {
            return \Storage::disk('public')->get('fe/inviate/'.$this->arr['filename']);
        }
        elseif(isset($this->arr['xml']))
        {
            return $this->arr['xml']->asXML();
        }
        else
        {
            //get xml;
        }
    }

    public function init()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fatturacheck.p.rapidapi.com/API/FatturaCheckAPI');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['XML' => $this->getXml($this->arr)]);

        $headers = array();
        $headers[] = 'X-Rapidapi-Host: fatturacheck.p.rapidapi.com';
        $headers[] = 'X-Rapidapi-Key: 3939dd4d58mshf7293affccd8c80p1f983ejsn839261357b9f';
        $headers[] = 'Content-Type: multipart/form-data';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return [
                'is_empty' => false,
                'curl_error' => curl_error($ch)
            ];
        }
        curl_close($ch);

        return $this->response($result);
    }


    public function response($response)
    {
        $response = json_decode($response);

        if(count($response))
        {
            $data = $response[0];
            if($data->IsValid)
            {
                return [
                    'is_empty' => false,
                    'isValid' => true
                ];
            }

            $errors = [];
            for($x = 0 ; $x < $data->ErrorCount; $x++)
            {
                $errors[$x] = $data->Errors[$x]->ErrorMessage;
            }
            return [
                'is_empty' => false,
                'isValid' => false,
                'errors' => $errors
            ];

        }
        return ['is_empty' => true];
    }


}
