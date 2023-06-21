<?php

namespace App\FeiC;

use Areaseb\Core\Models\{City, Company, Cost, Country, Exemption, Invoice as Fatture, Item, Media, Setting};
use \Carbon\Carbon;
use \Log;
use \Exception;
use App\FeiC\Client;
use FattureInCloud\Api\ClientsApi;
use FattureInCloud\Api\IssuedDocumentsApi;
use FattureInCloud\Api\SuppliersApi;
use FattureInCloud\Configuration;
use GuzzleHttp\Client as GuzzleHttpClient;
use FattureInCloud\OAuth2\OAuth2AuthorizationCodeManager;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Message;

class FeiC extends Primitive
{
    public $config;
    public $token;
    public $status;
    public $company_id;

    public function __construct() {
        $settings = Setting::fe();

        $this->token = $settings->token;
        $this->company_id = $settings->company_id;
        $this->status = config('fe.status_feic');

        $this->config = Configuration::getDefaultConfiguration()->setAccessToken($this->token);
    }
}
