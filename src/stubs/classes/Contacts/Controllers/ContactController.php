<?php

namespace App\Classes\Contacts\Controllers;

use App\Classes\Contacts\{Contact, Company, NewsletterList};
use App\Classes\Contacts\Requests\{EditContact, StoreContact};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Classes\{City, Country, User};
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()->input())
        {
            $query = Contact::filter(request());
        }
        else
        {
            $query = Contact::query();
        }

        $contacts = $query->paginate(100);

        return view('models.contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $companies[''] = '';
        $companies += Company::pluck('rag_soc', 'id')->toArray();
        $users[''] = '';
        $users += User::with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
        $lists = NewsletterList::pluck('nome', 'id')->toArray();
        return view('models.contacts.create', compact('provinces', 'countries', 'companies', 'users', 'lists'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreContact $request)
    {
        Contact::createOrUpdate(new Contact, request()->input());
        return redirect('contacts')->with('message', 'Contatto Creato');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Contacts\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function show(Contact $contact)
    {
        return view('models.contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Contacts\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function edit(Contact $contact)
    {
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $companies[''] = '';
        $companies += Company::pluck('rag_soc', 'id')->toArray();
        $users[''] = '';
        $users += User::with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
        $lists = NewsletterList::pluck('nome', 'id')->toArray();
        return view('models.contacts.edit', compact('provinces', 'countries', 'companies', 'users', 'contact', 'lists'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Contacts\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function update(EditContact $request, Contact $contact)
    {
        Contact::createOrUpdate($contact, request()->input());
        return redirect('contacts')->with('message', 'Contatto Aggiornato');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Contacts\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact)
    {
        return 'done';
    }

//contacts-validate-file
    public function validateFile(Request $request)
    {
        $this->validate(request(), [
            'file' => 'mimes:csv'
        ]);
    }

}
