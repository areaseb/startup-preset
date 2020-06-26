<?php

namespace App\Classes\Contacts\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Classes\Contacts\{Contact, NewsletterList};

class NewsletterListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lists = NewsletterList::latest()->get();
        return view('models.contacts.lists.index', compact('lists'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('models.contacts.lists.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $newsletterList = new NewsletterList;
            $newsletterList->nome = $request->nome;
        $newsletterList->save();

        foreach(Contact::filter($request)->get() as $contact)
        {
            $contact->lists()->attach($newsletterList->id);
        }

        return back()->with('message', 'Lista Creata');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, NewsletterList $list)
    {
        $lists = NewsletterList::all();
        if($request->get('sort'))
        {
            $arr = explode('|', $request->sort);
            if($arr[0] == 'statistiche')
            {
                dd('todo');
            }
            else
            {
                $contacts = $list->contacts()->orderBy($arr[0], $arr[1])->get();
            }
        }
        else
        {
            $contacts = $list->contacts;
        }



        $options = '';
        foreach($lists as $value)
        {
            if($value->id != $list->id)
            {
                $options .= '<option value="'.$value->id.'">'.$value->nome.'</option>';
            }
        }
        return view('models.contacts.lists.show', compact('list', 'options', 'contacts'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function edit(NewsletterList $list)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NewsletterList $list)
    {
        //
    }

    public function updateContacts(Request $request, NewsletterList $list)
    {
        $contacts = Contact::whereIn('id', $request->contact_id)->get();
        if($request->action == 'remove')
        {
            foreach($contacts as $contact)
            {
                $contact->lists()->detach($request->target_list_id);
            }
            return 'done';
        }
        elseif($request->action == 'copy')
        {
            foreach($contacts as $contact)
            {
                $contact->lists()->syncWithoutDetaching($request->target_list_id);
            }
            return 'done';
        }
        elseif($request->action = 'move')
        {
            foreach($contacts as $contact)
            {
                $contact->lists()->detach($list->id);
                $contact->lists()->attach($request->target_list_id);
            }
            return 'done';
        }
        return null;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function destroy(NewsletterList $list)
    {
        $list->delete();
        return 'done';
    }


//lists/{list}/contacts/{contact}
    public function removeContactFromList(NewsletterList $list, Contact $contact)
    {
        $contact->lists()->detach($list->id);
        return 'done';
    }

}
