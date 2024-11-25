<?php

namespace App\FeiC\Actions;

use Areaseb\Core\Models\{Client, Company, City, Country, Setting, Cost, Invoice, Item, Exemption, Expense, Media};
use App\FeiC\FeiC;
use App\FeiC\Xml\Xml;
use \Exception;
use \Carbon\Carbon;
use FattureInCloud\Api\ReceivedDocumentsApi;
use FattureInCloud\Api\IssuedEInvoicesApi;
use GuzzleHttp\Client as GuzzleHttpClient;
use \Storage;

/**
 * Receive costs (fatture acquisti)
 */
class Receive extends FeiC
{
    /**
     * Get expenses
     */
    public function receive() {	
        $apiInstance = new ReceivedDocumentsApi(
            new GuzzleHttpClient(),
            $this->config,
        );

        $settings = Setting::fe();

        try {
            // Receive documents
            // https://github.com/fattureincloud/fattureincloud-php-sdk/blob/903df1cc56e7358b949ef39ee67ec09fb59cdba9/docs/Api/ReceivedDocumentsApi.md#listreceiveddocuments            
            $type = 'expense';
            $fields = 'invoice_number,e_invoice';
            $fieldset = 'detailed';
            $sort = '-date';
            $per_page = $settings->max_receive;

            if ($per_page < 5)
                $per_page = 5; // Min value

            $response = $apiInstance->listReceivedDocuments($this->company_id, $type, $fields, $fieldset, $sort, null, $per_page);
        } catch (Exception $e) {
            echo 'Exception when calling IssuedDocumentsApi->listIssuedDocuments: ', $e->getMessage(), PHP_EOL;
        }

        $invoices = $response['data'];

        foreach($invoices as $invoice)
        {
            $cost = $this->costExists($invoice);

            if(is_null($cost))
            {
                // Create invoice
                $cost = $this->addCost($invoice);

                if($cost)
                {
                    $this->notify($cost, "costo inserito correttamente", 'info');
                    $cost->update(['data_ricezione' => Carbon::parse($invoice['created_at'])->format('d/m/Y')]);
                } else {
                    $this->notify($this, "RECEIVE problem getting invoice with id=".$invoice['id'], 'info');
                }
            }
            else
            {
                $this->notify($cost, "costo ".trim($invoice['id'])." già presente", 'info');
                $cost->update(['data_ricezione' => Carbon::parse($invoice['created_at'])->format('d/m/Y')]);
            }
        }

        $this->notify($this, "terminato", 'info');
        $this->updateDate();

        return 'done';
    }

    public function costExists($invoice) {
        $cost = Cost::where('data', Carbon::parse($invoice['date'])->format('Y-m-d'))
                ->where('fe_id', $invoice['id'])
                ->first();       

        return $cost;
    }

    public function addCost($invoice)
    {
        $company = $this->getOrAddCompany($invoice, 1);   

        $date = Carbon::parse($invoice['date']);
        $imponibile = $invoice['amount_net'];
        $iva = $invoice['amount_vat'];
        $totale = $imponibile + $iva;

        $scadenza = '';
        $rate = '';
        $paid = false;
        if (isset($invoice['payments_list']))
        {
            $date_pays = [];
            foreach ($invoice['payments_list'] as $payment)
            {
                if (isset($payment['due_date']))
                {
                    $date_pays[] = $payment['due_date'];
                }

                $paid = $payment['status'] == 'paid';
            }
            if (count($date_pays) > 0)
            {
                sort($date_pays);
                $scadenza = end($date_pays);
                $scadenza = Carbon::parse($scadenza);

                if( $scadenza->diffInYears(Carbon::today(), true) > 1 )
                {
                    $scadenza = $date;
                }

                reset($date_pays);
                if(count($date_pays) > 1)
                {
                    for ($i=0; $i<count($date_pays); $i++)
                    {
                        $date_pays[$i] = Carbon::parse($date_pays[$i])->format('d/m/Y');
                    }
                    $rate = implode(';', $date_pays);
                }
            }
        }

        if ($scadenza == '')
        {
            $scadenza = $date;
        }
		

        $cost = Cost::create([
            "company_id" => $company->id,
            "expense_id" => $this->idSpesaDefault(),
            "numero" => $invoice['invoice_number'],
            "anno" => $date->format('Y'),
            "data" => $date->format('d/m/Y'),
            "imponibile" => $this->decimal($imponibile),
            "iva" => $this->decimal($iva),
            "totale" => $this->decimal($totale),
            "data_scadenza" => $scadenza->format('d/m/Y'),
            "rate" => ($rate == '') ? null : $rate,
            "fe_id" => $invoice['id'],
            'saldato' => $paid,
        ]);
        

        $this->notify($cost, "RECEIVE inserito nuovo costo da ".$company->rag_soc, 'info');

        if(isset($invoice['attachment_url']))
        {
            try
            {
                $filename = $invoice['id'].'-'.date('Y').'.pdf';
                $mediable_type = Cost::class;
        
                $type_path = '';
        
                $content =  file_get_contents($invoice['attachment_url']);
                if ( \Storage::disk('local')->put('public/costs/docs/'.$type_path.$filename, $content) === false )
                {
                    Log::channel('fe')->error("errore salvando il file {$filename}");
                    dd('File download error');
                    return false;
                }
       
                if(!$cost->media()->where('filename', $filename)->exists())
                {
                    Media::create([
                        'description' => 'Fattura PDF '.$cost->company->rag_soc,
                        'mime' => 'pdf',
                        'filename' => $filename,
                        'mediable_id' => $cost->id,
                        'mediable_type' => $mediable_type,
                        'media_order' => 2,
                        'size' => Storage::size('public/costs/docs/'.$type_path.$filename)
                    ]);
                }
      
                return $cost;
            }
            catch(\Exception $e)
            {
                $this->notify($cost, "RECEIVE errore scaricando il pdf " . $invoice['attachment_url'], 'info');
            }
        }

        return $cost;
    }

    /**
     * @return [str] [date YYYY-MM-DD]
     */
    private function updateDate()
    {
        $setting = Setting::where('model', 'Fe')->first();
            $fields = $setting->fields;
            $fields['last_sync'] = Carbon::today()->format('Y-m-d');
            $setting->fields = $fields;
        $setting->save();
    }

}
