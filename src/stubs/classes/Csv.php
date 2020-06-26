<?php

namespace App\Classes;

use Illuminate\Database\Eloquent\Model;

class Csv extends Model
{

    //find delimiter in csv file
    public static function getDelimiter($csvFile)
    {
        $delimiters = array(
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0
        );

        $handle = fopen($csvFile, "r");
        $firstLine = fgets($handle);
        fclose($handle);

        foreach ($delimiters as $delimiter => &$count)
        {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }


    //check if firstLine is a header
    public static function hasHeader($csvFile)
    {
        $handle = fopen($csvFile, "r");
        $firstLine = fgets($handle);
        fclose($handle);

        if(strpos($firstLine, "@") !== false)
        {
            return false;
        }

        return true;
    }

    //get the header
    public static function getHeader($csvFile)
    {
        $delimiter = self::getDelimiter($csvFile);
        $headerValues = [];

        if (($handle = fopen($csvFile, "r")) !== FALSE)
        {
           while(($data = fgetcsv($handle, 1000, $delimiter)) !== false)
           {
                 $headerValues = $data;
                 break;
           }
        }

        return $headerValues;
    }

    //return the first 4 lines
    public static function peek($csvFile)
    {
        $delimiter = self::getDelimiter($csvFile);
        $count = 0;
        $hasHeader = self::hasHeader($csvFile);
        $peek = [];
        if (($handle = fopen($csvFile, "r")) !== FALSE)
        {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if($hasHeader && $count > 0)
                {
                    if($count <= 4)
                    {
                        $peek[$count] = $data;
                    }
                    else
                    {
                        break;
                    }
                }
                elseif(!$hasHeader)
                {
                    if($count <= 3)
                    {
                        $peek[$count] = $data;
                    }
                    else
                    {
                        break;
                    }
                }
                $count++;
            }
            fclose($handle);
        }
        return $peek;
    }

    public static function read($csvFile)
    {
        $delimiter = self::getDelimiter($csvFile);
        $count = 0;
        $hasHeader = self::hasHeader($csvFile);
        $peek = [];
        if (($handle = fopen($csvFile, "r")) !== FALSE)
        {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if($hasHeader && $count > 0)
                {
                    $peek[$count] = $data;
                }
                elseif(!$hasHeader)
                {
                    $peek[$count] = $data;
                }
                $count++;
            }
            fclose($handle);
        }
        return $peek;
    }





/*

$headers = array(
    "Content-type" => "text/csv",
    "Content-Disposition" => "attachment; filename=file.csv",
    "Pragma" => "no-cache",
    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
    "Expires" => "0"
);

$contacts = Contact::all();
$columns = array('Nome', 'Cognome', 'Cellulare', 'Email', 'Indirizzo', 'Cap', 'Citta', 'Provincia', 'Nazione', 'Lingua');

$callback = function() use ($contacts, $columns)
{
    $file = fopen('php://output', 'w');
    fputcsv($file, $columns);

    foreach($contacts as $contact) {
        fputcsv($file, array(
            $contact->nome,
            $contact->cognome,
            $contact->cellulare,
            $contact->email,
            $contact->indirizzo,
            $contact->cap,
            $contact->citta,
            $contact->provincia,
            $contact->nazione,
            $contact->lingua
        ));
    }
    fclose($file);
};
return Response::stream($callback, 200, $headers);

*/


}
