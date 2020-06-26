<?php

namespace App\Classes\Contacts\Controllers;

use App\Classes\Contacts\{Newsletter, NewsletterList, Contacts, Template};
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Classes\Setting;
use App\Classes\Contacts\Jobs\{SendNewsletter, SendTestNewsletter, SendNewsletterCompleted};
use Carbon\Carbon;

class NewsletterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $newsletters = Newsletter::latest()->get();
        return view('models.contacts.newsletters.index', compact('newsletters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $lists = [0 => 'Tutti i Contatti']+NewsletterList::pluck('nome', 'id')->toArray();
        $templates = ['' => '']+Template::pluck('nome', 'id')->toArray();
        return view('models.contacts.newsletters.create', compact('lists', 'templates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newsletter = new Newsletter;
            $newsletter->nome = $request->nome;
            $newsletter->oggetto = $request->oggetto;
            $newsletter->descrizione = $request->descrizione;
            $newsletter->template_id = $request->template_id;
        $newsletter->save();



        // $newsletter = Newsletter::find(2);
        // $list = NewsletterList::find(1);
        $newsletter->lists()->attach($request->list_id);

        return redirect('newsletters')->with('message', 'Newsletter Creata');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function show(Newsletter $newsletter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function edit(Newsletter $newsletter)
    {
        $lists = [0 => 'Tutti i Contatti']+NewsletterList::pluck('nome', 'id')->toArray();
        $templates = ['' => '']+Template::pluck('nome', 'id')->toArray();
        return view('models.contacts.newsletters.edit', compact('lists', 'templates', 'newsletter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Newsletter $newsletter)
    {

        $newsletter->nome = $request->nome;
        $newsletter->oggetto = $request->oggetto;
        $newsletter->descrizione = $request->descrizione;
        $newsletter->template_id = $request->template_id;
        $newsletter->save();

        $newsletter->lists()->sync($request->list_id);

        return redirect('newsletters')->with('message', 'Newsletter Aggiornata');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function destroy(Newsletter $newsletter)
    {
        return redirect('newsletters')->with('message', 'Newsletter Eliminata');
    }

//newsletter/{id}/send-test - GET
    public function test(Newsletter $newsletter)
    {
        return view('models.contacts.newsletters.send-test', compact('newsletter'));
    }

//newsletter/{id}/send-test - POST
    public function sendTest(Newsletter $newsletter)
    {
        foreach(Setting::defaultTestEmail(request('email')) as $recipient)
        {
            $args = [
                'sender' => Setting::defaultSendFrom(),
                'recipient' => $recipient,
                'subject' => $newsletter->oggetto,
                'content' => $newsletter->template->contenuto
            ];
            dispatch( (new SendTestNewsletter($args))->delay(Carbon::now()->addSeconds(5)) );
        }

        return redirect('newsletters')->with('message', 'Test email spedita');
    }

//newsletter/{id}/send - GET
    public function send(Newsletter $newsletter)
    {
        $sender = Setting::defaultSendFrom();
        return view('models.contacts.newsletters.send', compact('newsletter', 'sender'));
    }

//newsletter/{id}/send - POST
    public function sendOfficial(Newsletter $newsletter)
    {
        $sender = Setting::defaultSendFrom();
        $delay = Carbon::now()->addSeconds(5);

        foreach($newsletter->lists as $list)
        {
            $list->contacts()->chunk(4, function($contacts) use(&$delay, $sender, $newsletter) {
                dispatch( (new SendNewsletter($sender, $contacts, $newsletter))->delay($delay->addSeconds(15)) );
            });
        }

        dispatch( (new SendNewsletterCompleted('Campagna spedita', $newsletter->id))->delay($delay->addSeconds(15)) );

        return back()->with('message', 'Processo iniziato. Vi notificheremo quando sarà consluso.');
    }



}
