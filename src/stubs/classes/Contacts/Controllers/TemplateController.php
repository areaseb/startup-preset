<?php

namespace App\Classes\Contacts\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Classes\Contacts\Template;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $templates = Template::latest()->get();
        return view('models.contacts.templates.index', compact('templates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $template = new Template;
            $template->nome = $request->nome;
            $template->contenuto_html = $request->contenuto_html;
            $template->contenuto = $request->contenuto_html;
        $template->save();
        return $template->id;
    }

    public function destroy(Template $template)
    {
        $template->delete();
        return 'done';
    }


//templates/{id}
    public function show(Template $template)
    {
        return view('models.contacts.templates.show', compact('template'));
    }

    public function html(Template $template)
    {
        return view('models.contacts.templates.html', compact('template'));
    }



    public function update(Request $request, Template $template)
    {
        $template->contenuto_html = $request->contenuto_html;
        $template->contenuto = $request->contenuto_html;
        $template->save();
        return 'done';
    }



}
