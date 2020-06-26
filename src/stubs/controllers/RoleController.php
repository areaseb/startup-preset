<?php

namespace App\Http\Controllers;

use App\Classes\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{

    public function create()
    {
        return view('models.roles.create');
    }

    public function store()
    {
        Role::create([
            'name' => request('name'),
            'slug' => str_slug(request('name'))
        ]);
        return back()->with('message', 'Ruolo aggiunto');
    }

}
