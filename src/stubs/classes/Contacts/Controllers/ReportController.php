<?php

namespace App\Classes\Contacts\Controllers;

use Illuminate\Http\Request;
use App\Classes\Contacts\{Contact, Newsletter, Report};
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class ReportController extends Controller
{

    public function index(Newsletter $newsletter)
    {

        $stats = Report::stats($newsletter);
        $reports = Report::where('newsletter_id', $newsletter->id)->get();
        return view('models.contacts.reports.index', compact('newsletter', 'reports', 'stats'));
    }

    public function show()
    {
        dd('report single user');
    }


//newsletters/{newsletter}/reports/aperte
    public function showOpen(Newsletter $newsletter)
    {
        $contactIds = Report::where('newsletter_id', $newsletter->id)->aperte()->pluck('contact_id');
        $contacts = Contact::whereIn('id', $contactIds)->get();
        return view('models.contacts.reports.show-open', compact('contacts', 'newsletter'));
    }

//newsletters/{newsletter}/reports/errore
    public function showErrore(Newsletter $newsletter)
    {
        $contactIds = Report::where('newsletter_id', $newsletter->id)->errore()->pluck('contact_id');
        $contacts = Contact::whereIn('id', $contactIds)->get();
        return view('models.contacts.reports.show-error', compact('contacts', 'newsletter'));
    }

//newsletters/{newsletter}/reports/unsubscribed
    public function showUnsubscribed(Newsletter $newsletter)
    {
        $contactIds = Report::where('newsletter_id', $newsletter->id)->unsubscribed()->pluck('contact_id');
        $contacts = Contact::whereIn('id', $contactIds)->get();
        return view('models.contacts.reports.show-unsubscribed', compact('contacts', 'newsletter'));
    }




//tracker{?r=}
    public function tracker()
    {
        if(request()->has('r'))
        {
            $report = Report::identify(request('r'));
            if($report)
            {
                if($report->opened == 0)
                {
                    $report->opened = 1;
                    $report->opened_at = Carbon::now();
                    $report->save();
                }
            }
        }
        return response()->file(public_path('img/image.png'));
    }

//unsubscribe{?r=}
    public function unsubscribe()
    {
        if(request()->has('r'))
        {
            $report = Report::identify(request('r'));
            if($report)
            {
                $report->contact()->update(['subscribed' => false]);
                $report->unsubscribed = true;
                $report->save();
                return 'you are now unsubscribed';
            }
            return 'nothing';
        }
        return 'nothing';
    }


}
