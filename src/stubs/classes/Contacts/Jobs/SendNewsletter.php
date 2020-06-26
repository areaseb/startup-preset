<?php

namespace App\Classes\Contacts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Classes\Contacts\{Newsletter, Report};
use App\Classes\Contacts\Mail\Newsletter\OfficialNewsletter;

class SendNewsletter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;

    protected $sender;
    protected $contacts;
    protected $newsletter;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sender, $contacts, Newsletter $newsletter)
    {
        $this->sender = $sender;
        $this->contacts = $contacts;
        $this->newsletter = $newsletter;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->contacts as $contact)
        {
            if( !Report::where('newsletter_id', $this->newsletter->id)->where('contact_id', $contact->id)->exists() )
            {
                $report = Report::create([
                    'newsletter_id' => $this->newsletter->id,
                    'contact_id' => $contact->id,
                    'identifier' => str_random(16)
                ]);

                try
                {
                    \Mail::send(new OfficialNewsletter(
                        $this->sender,
                        $contact->email,
                        $this->newsletter->oggetto,
                        $this->newsletter->addTrackingAndPersonalize($report->identifier, $contact)
                    ));
                }
                catch(\Exception $e)
                {
                    $report->update([
                        'delivered' => 0,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

    }
}
