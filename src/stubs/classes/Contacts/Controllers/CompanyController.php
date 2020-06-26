<?php

namespace App\Classes\Contacts\Controllers;

use App\Classes\Contacts\Company;
use App\Classes\Contacts\Requests\{StoreCompany, EditCompany};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Classes\{City, Country};

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Company::query();
        if(request()->input())
        {
            foreach(request()->input() as $key => $value)
            {
                $query = $query->where($key, $value);
            }
            $companies = $query->get();
        }
        else
        {
            $companies = Company::all();
        }


        return view('models.contacts.companies.index', compact('companies'));
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
        return view('models.contacts.companies.create', compact('provinces', 'countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompany $request)
    {
        $company = Company::create($request->except(['_method']));
        $company->update(['city_id' => City::getCityIdFromData($request->provincia, $request->nazione)]);
        return redirect('companies')->with('message', 'Azienda Creata');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Contacts\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        return view('models.contacts.companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Contacts\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        return view('models.contacts.companies.edit', compact('provinces', 'countries', 'company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Contacts\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(EditCompany $request, Company $company)
    {
        Company::where('id', $company->id)->update($request->except(['_token', '_method']));
        Company::where('id', $company->id)->update(['city_id' => City::getCityIdFromData($request->provincia, $request->nazione)]);
        return redirect('companies')->with('message', 'Azienda Aggiornata');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Contacts\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        //
    }
}
