<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Areaseb\Core\Models\Cron;
use Areaseb\Renewals\Models\Renewal as RenewalModel;

class Renewals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renewals:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create New Renewals';

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
        if(\Illuminate\Support\Facades\Schema::hasTable('renewals'))
        {
            RenewalModel::updateOnInvoice();
            $this->info('renewals updated');
            Cron::create(['name' => 'renewals']);
        }
    }
}
