<?php

namespace App\Http\Controllers;

use App\Classes\Contacts\{Company, Contact};
use App\Classes\{City, Country, Role, User};
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index()
    {
        $roles = Role::all();
        $users = User::all();
        return view('models.users.index', compact('roles', 'users'));
    }

    public function create()
    {
        $roles = Role::pluck('name', 'id');
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $companies[''] = '';
        $companies += Company::pluck('rag_soc', 'id')->toArray();
        return view('models.users.create', compact('roles', 'provinces', 'countries', 'companies'));
    }

    public function store()
    {

        $this->validate(request(),[
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'nome' => 'required',
            'cognome' => 'required',
            'role_id' => 'required'
        ]);

        $user = new User;
            $user->email = request('email');
            $user->password = bcrypt(request('password'));
        $user->save();

        $user->roles()->attach(request('role_id'));

        $contact = Contact::createOrUpdate(new Contact, request()->input(), $user->id);

        if(request('email'))
        {
            //send notification to new User
        }


        return request()->input();
    }

    public function edit($id)
    {
        $roles = Role::pluck('name', 'id');
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $companies[''] = '';
        $companies += Company::pluck('rag_soc', 'id')->toArray();
        $element = User::findOrFail($id);
        return view('models.users.edit', compact('roles', 'provinces', 'countries', 'companies', 'element'));
    }

    public function update($id)
    {
        $user = User::findOrFail($id);

        $this->validate(request(),[
            'email' => 'required|string|email|unique:users,email,'.$user->id.',id',
            'password' => 'required|min:8',
            'nome' => 'required',
            'cognome' => 'required',
            'role_id' => 'required'
        ]);

        $user->email = request('email');
        $user->save();

        $user->roles()->sync(request('role_id'));

        $contact = Contact::createOrUpdate(new Contact, request()->input(), $user->id);

        return request()->input();
    }

}
