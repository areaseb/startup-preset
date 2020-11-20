<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Jacofda\Core\Models\Cron;
use \Carbon\Carbon;

class LogClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old logs';

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
        Cron::where('created_at', '<', Carbon::now()->subDays(2))->delete();
    }
}
