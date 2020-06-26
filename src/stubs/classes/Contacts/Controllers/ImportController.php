<?php

namespace App\Classes\Contacts\Controllers;

use App\Classes\Contacts\{Company, Contact};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Classes\Csv;

class ImportController extends Controller
{

//imports/{model} - GET
    public function importForm($model)
    {
        if($model == 'contacts')
        {
            $fields = ['nome', 'cognome', 'cellulare', 'email', 'indirizzo', 'cap', 'citta', 'provincia', 'nazione', 'tipo'];
            $type = 'Contatti';
            return view('extra.imports.form', compact('type', 'fields'));
        }

        abort(404);
    }

//imports/{model} - POST
    public function importUpload(Request $request, $model)
    {
        $dataCsv = Csv::read($request->file);
        $header = explode(',', $request->header);

        if($model == 'contacts')
        {
            foreach($dataCsv as $row)
            {
                $data['user_id'] = null;
                $data['company_id'] = null;
                foreach($header as $key => $field)
                {
                    $data[$field] = $row[$key];
                }
                $contact = new Contact;
                Contact::createOrUpdate($contact, $data);
            }
            return 'done';
        }

        return 'error';
    }

//imports/peek - POST
    public function peek(Request $request)
    {
        return Csv::peek($request->file);
    }
}
