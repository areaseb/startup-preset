<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Areaseb\Core\Models\{Calendar, Cron};

class CalendarIcs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:ics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rigenerate or Create ics files';

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
        Calendar::createICS();
        $this->info('files generated successfully!');
        Cron::create(['name' => 'CalendarIcs']);
    }
}
