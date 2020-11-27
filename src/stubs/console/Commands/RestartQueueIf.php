<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \Illuminate\Support\Facades\Artisan;
use Areaseb\Core\Models\Cron;
use \Carbon\Carbon;

class RestartQueueIf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myqueue:restart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Double check restart queue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(\DB::table('jobs')->count() > 0)
        {
            if(Cron::latest()->first()->name != 'working')
            {
                Artisan::call('queue:work --stop-when-empty');
            }

            Cron::create(['name' => 'working']);
        }
        else
        {
            Cron::create(['name' => 'jobless']);
        }
    }
}
