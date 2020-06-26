<?php

namespace App\Classes\Contacts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Classes\Contacts\Mail\Newsletter\TestNewsletter;


class SendTestNewsletter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $args;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($args)
    {
        $this->args = $args;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Mail::send(new TestNewsletter(
            $this->args['sender'],
            $this->args['recipient'],
            $this->args['subject'],
            $this->args['content'])
        );
    }
}
