<?php

namespace App\Fe\Auth;

use App\Fe\Auth\Config;
use App\Fe\Auth\Request;
use \Log;
use \Exception;

class Authenticate
{

    public static function getToken()
    {
        try
        {
            $config = new Config;
            $request = new Request($config->auth());
            $post_data = "grant_type=password&username={$config->user}&password={$config->pwd}";
            $response = $request->post($post_data);

            if ($response->status == 200)
            {
                $data = json_decode($response->body);
                return $data->access_token;
            }
            else
            {
                if ($response->status == 400)
                {
                    $data = json_decode($response->body);
                    Log::channel('fe')->critical("getToken()/Authenticate errore nella risposta http: codice={$data->error}; descrizione={$data->error_description} | url: $url ; posted data: $post_data");
                }
                else
                {
                    Log::channel('fe')->critical("getToken()/Authenticate errore nella risposta http: status={$response->status}; body={$response->body}");
                }
            }
        }
        catch(Exception $e)
        {
            Log::channel('fe')->critical("getToken()/Authenticate: Exception");
			Log::channel('fe')->critical($e);
			//Log::channel('fe')->critical("url: $config->auth() ; posted data: $post_data");
        }
        return false;
    }

}
