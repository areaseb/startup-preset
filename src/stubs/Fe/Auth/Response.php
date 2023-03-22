<?php

namespace App\Fe\Auth;

use \Log;

class Response
{
    public $headers;
    public $status;
    public $body;
    public $error;
    public $url;

    function __construct($results)
	{
		$this->status = $results['status'];
		$this->error = $results['error'];
		$this->url = $results['url'];

		$this->headers = array();
		$header_array = explode("\n", $results['header']);
		for ($n=0; $n<count($header_array); $n++)
		{
			$items =  explode(':', $header_array[$n]);
			if (count($items) > 1 && trim($items[0]) != '')
			{
				$this->headers[trim($items[0])] = trim($items[1]);
			}
		}
		$this->body = $results['body'];
	}

    public function toString($linefeed = '<br />')
	{
		$ret = 'status: ' . $this->status . $linefeed;
		$ret .=  'error: ' . $this->error . $linefeed;
		$ret .=  "headers:$linefeed";
		foreach ($this->headers as $key => $value)
		{
			$ret .=  "$key=$value$linefeed";
		}
		$ret .=  "body:$value$linefeed";
		$ret .=  $this->body;
		return $ret;
	}


}
