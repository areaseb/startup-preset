<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Fe\Actions\Sync;
use App\FeiC\Actions\Sync as FeiCSync;
use Areaseb\Core\Models\Setting;

class InvoiceSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call SDI and sync invoices out';

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
        $settings = Setting::fe();

        if ($settings->connettore == 'Aruba') {
            (new Sync)->init();
        } else if ($settings->connettore == 'Fatture in Cloud') {
            (new FeiCSync)->sync();
        } else {
            $this->error('Modulo FE non impostato');
        }

        $this->info('sincronizzazione completata');
    }
}
