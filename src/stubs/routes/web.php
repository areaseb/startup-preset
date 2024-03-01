<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Areaseb\Core\Http\Middleware\SaveTracking;
use Areaseb\Core\Http\Controllers\{LoginController, PagesController, ReportController};

Route::get('login', [LoginController::class, 'login'])->name('login');
Route::post('login', [LoginController::class, 'loginPost'])->name('loginPost');

//newsletter tracking
Route::get('tracker', [ReportController::class, 'tracker']);
Route::get('unsubscribe', [ReportController::class, 'unsubscribe']);
Route::get('track', [ReportController::class, 'track']);

use Areaseb\Core\Models\{Client, Contact, Company, NewsletterList, Setting, Note};
Route::get('register-lead', function(){

    $url = 'https://www.'.Setting::base()->sitoweb;
    $email = strtolower(request('email'));
    $nome = ucfirst(strtolower(request('nome')));
    $cognome = ucfirst(strtolower(request('cognome')));
    if(request('message') != ''){
    	$nota = trim(request('message'));
    } elseif(request('your-message') != ''){
    	$nota = trim(request('your-message'));    	
    }
    

    if(!Contact::where('email', request('email'))->exists())
    {
        $company = new Company;
            $company->email = $email;
            $company->rag_soc = $nome . ' ' . $cognome;
            $company->mobile = request('telefono');
            $company->private = true;
            $company->origin = "Sito";
            $company->client_id = Client::Lead()->id;
        $company->save();

        $contact = new Contact;
            $contact->email = $email;
            $contact->nome = $nome;
            $contact->cognome = $cognome;
            $contact->cellulare = request('telefono');
            $contact->subscribed = intval(request('newsletter'));
            $contact->origin = "Sito";
            $contact->company_id = $company->id;
        $contact->save();


        if(request('message'))
        {
            Note::create([
                'company_id' => $company->id,
                'description' => $nota
            ]);
        }

        if(request('your-message'))
        {
            Note::create([
                'company_id' => $company->id,
                'description' => $nota
            ]);
        }


        if(intval(request('newsletter')))
        {
            $list = NewsletterList::firstOrCreate(['nome' => 'Contatti da sito', 'owner_id' => 1]);
            $contact->lists()->attach($list->id);
        }
    }
    else
    {
        $company = Company::where('email', $email)->first();

        if($company)
        {
            if(request('message'))
            {
                Note::create([
                    'company_id' => $company->id,
                    'description' => $nota
                ]);
            }

            if(request('your-message'))
            {
                Note::create([
                    'company_id' => $company->id,
                    'description' => $nota
                ]);
            }

        }
        else
        {
            $contact = Contact::where('email', $email)->first();
            $company = new Company;
                $company->email = $email;
                $company->rag_soc = $contact->nome . ' ' . $contact->cognome;
                $company->mobile = $contact->cellulare;
                $company->private = true;
                $company->address = $contact->indirizzo;
                $company->origin = "Sito";
                $company->client_id = Client::Lead()->id;
                $company->zip = $contact->cap;
                $company->nation = $contact->nazione;
                $company->lang = $contact->lingua;
                $company->province = $contact->provincia;
                $company->city = $contact->citta;
            $company->save();

            $contact->update(['company_id' => $company->id]);


            if(request('message'))
            {
                Note::create([
                    'company_id' => $company->id,
                    'description' => $nota
                ]);
            }

            if(request('your-message'))
            {
                Note::create([
                    'company_id' => $company->id,
                    'description' => $nota
                ]);
            }

        }
    }
    return redirect($url.'/grazie');
});

Route::get('pdf-footer', function(){
    return view('areaseb::pdf.invoices.footer');
})->name('pdf.footer');
Route::get('pdf-header', function(){
    return view('areaseb::pdf.invoices.header');
})->name('pdf.header');

Route::get('addevent/{token}', [\Areaseb\Core\Http\Controllers\EventController::class, 'createFromToken']);
Route::post('addevent/{token}', [\Areaseb\Core\Http\Controllers\EventController::class, 'storeFromToken']);
