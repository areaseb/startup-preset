<?php

namespace App\FeiC\Actions;

use Areaseb\Core\Models\{Invoice, Item, Exemption, Company, Country, Media, Setting};
use App\Fe\Xml\Xml;
use Areaseb\Core\Models\Fe\FatturaCheck;
use App\Fe\Primitive;
use App\Fe\Auth\{Authenticate, Config, Request, Response};
use App\FeiC\FeiC;
use \Log;
use \Exception;
use FattureInCloud\Api\IssuedDocumentsApi;
use FattureInCloud\Api\IssuedEInvoicesApi;
use FattureInCloud\Model\CreateIssuedDocumentRequest;
use GuzzleHttp\Client;

class Send extends FeiC
{
    public $invoice;

    public function __construct($invoice) {
        parent::__construct();

        $this->invoice = $invoice;
    }

    public function send() {
        $test = env('TEST_MODE', true);

        $apiInstance = new IssuedDocumentsApi(
            new Client(),
            $this->config
        );

        $company_id = $this->company_id;
        $document = new CreateIssuedDocumentRequest;

        // API: https://github.com/fattureincloud/fattureincloud-php-sdk/blob/master/docs/Api/IssuedDocumentsApi.md#createissueddocument
        // Model: https://developers.fattureincloud.it/api-reference/#post-/c/-company_id-/issued_documents

        $company = $this->invoice->company;

        // Invoice main structure
        $data = [
            "type" => "invoice",
            "amount_net" => $this->invoice->imponibile,
            "amount_vat" => $this->invoice->iva,
            "date" => $this->invoice->data,
            "next_due_date" => $this->invoice->data_scadenza,
            "e_invoice" => true,
        ];

        // Invoice client
        $data["entity"] = [
            "name" => $company->rag_soc,
            "vat_number" => $company->piva,
            "tax_code" => $company->cf,
            "address_street" => $company->address,
            "address_postal_code" => $company->zip,
            "address_city" => $company->city,
            "address_province" => $company->province,
            "country" => Country::where('iso2', $company->nation)->first()->nome,
        ];

        // Add invoice items
        $data['items_list'] = [];
        foreach($this->invoice->items as $item) {
            $data['items_list'][] = [
                "product_id" => $item->product_id,
                "code" => $item->product->codice,
                "name" => $item->product->nome,
                "net_price" => $item->importo,
                "gross_price" => $item->importo + $item->sconto,
                "discount" => $item->sconto,
                "discount_highlight" => false,
                "qty" => $item->qta,
                "description" => $item->product->description,
            ];
        }

        $data['ei_data'] = [];

        // Set payment information
        // https://developers.fattureincloud.it/docs/guides/invoice-creation/#3%EF%B8%8F%E2%83%A3-step-three-e-invoice
        $data['payments_list'] = [];
        $payment = $this->invoice->pagamento;

        if($this->invoice->rate)
        {
            $rate = explode(',', $this->invoice->rate);
            $n_rate = count($rate);
            $total = $this->decimal($this->invoice->total);
            $amount_rata = $this->decimal($total / $n_rate);
            $amount_payed = 0;

            $payment_method = 'TP01';
            for($nr=0;$nr<$n_rate;$nr++)
            {
                $new_payment = [];

                $scadenza_rata = \Carbon\Carbon::createFromFormat('d/m/Y', trim($rate[$nr]))->format('Y-m-d');

                $new_payment['due_date'] = $scadenza_rata;

                if($nr == ($n_rate -1))
                {
                    $new_payment['amount'] = $this->decimal($total - $amount_payed);
                }
                else
                {
                    $new_payment['amount'] = $amount_rata;
                }

                $amount_payed +=  $amount_rata;

                // Attach payment to invoice request
                $data['payments_list'][] = $new_payment;
            }
        }
        else
        {
            $payment_method = 'TP02';

            $new_payment = [];
            $new_payment['amount'] = $this->decimal($this->invoice->total);
            $new_payment['due_date'] = $this->invoice->data_scadenza->format('Y-m-d');

            // Attach payment to invoice request
            $data['payments_list'][] = $new_payment;
        }

        // Add IBAN
        $settings = Setting::fe();
        if ($settings->IBAN != '')
        {
            $data['ei_data']['bank_iban'] = $settings->IBAN;
        }

        $data['ei_data']['payment_method'] = $payment_method;

        // Set invoice data
        $document->setData($data);

        try {
            if (!$test) {
                // Create invoice
                // https://github.com/fattureincloud/fattureincloud-php-sdk/blob/master/docs/Api/IssuedDocumentsApi.md#createissueddocument
                $result = $apiInstance->createIssuedDocument($company_id, $document);

                $document_id = $result['data']['id'];
            } else {
                // Test data
                $document_id = 99;
            }
        } catch (Exception $e) {
            echo 'Exception when calling IssuedDocumentsApi->createIssuedDocument => ', $e->getMessage(), PHP_EOL;
        }

        if (!$test) {
            // Verify invoice
            // https://github.com/fattureincloud/fattureincloud-php-sdk/blob/master/docs/Api/IssuedEInvoicesApi.md#verifyeinvoicexml
            $apiInstance = new IssuedEInvoicesApi(
                new Client(),
                $this->config
            );

            try {
                $result = $apiInstance->verifyEInvoiceXml($this->company_id, $document_id);

                if (!$result['data']['success']) {
                    // Invoice invalid
                    $this->notify($this->invoice, 'Invalid e_invoice XML', 'info');
                }
            } catch (Exception $e) {
                echo 'Exception when calling IssuedEInvoicesApi->verifyEInvoiceXml: ', $e->getMessage(), PHP_EOL;
            }

            // Send einvoice
            // https://github.com/fattureincloud/fattureincloud-php-sdk/blob/master/docs/Api/IssuedEInvoicesApi.md#sendeinvoice
            try {
                $result = $apiInstance->sendEInvoice($this->company_id, $document_id);
            } catch (Exception $e) {
                echo 'Exception when calling IssuedEInvoicesApi->sendEInvoice: ', $e->getMessage(), PHP_EOL;
            }
        }

        return 'done';
    }
}
