<?php

namespace App\Classes\Contacts\Mail\Newsletter;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfficialNewsletter extends Mailable
{
    use Queueable, SerializesModels;

    public $sender;
    public $recipient;
    public $subject;
    public $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sender, $recipient, $subject, $content)
    {
        $this->sender = $sender;
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->sender['address'], $this->sender['name'])
                    ->to($this->recipient)
                    ->subject($this->subject)
                    ->view('emails.contacts.newsletters.newsletter');
    }
}
