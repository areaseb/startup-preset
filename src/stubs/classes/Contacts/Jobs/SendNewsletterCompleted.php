<?php

namespace App\Classes\Contacts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Classes\Notification;
use App\Classes\Contacts\Newsletter;

class SendNewsletterCompleted implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $name;
    public $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($name, $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Notification::create([
            'name' => $this->name,
            'notificationable_id' => $this->id,
            'notificationable_type' => 'App\Classes\Contacts\Newsletter'
        ]);

        Newsletter::find($this->id)->update(['inviata' => 1]);
    }
}
