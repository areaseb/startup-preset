<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Fe\Actions\Receive;
use App\FeiC\Actions\Receive as FeiCReceive;
use App\FeiC\FeiC;
use Areaseb\Core\Models\Setting;

class InvoiceReceive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:receive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call SDI and get new costs';

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
            (new Receive)->init();
        } else if ($settings->connettore == 'Fatture in Cloud') {
            (new FeiCReceive)->receive();
        } else {
            $this->error('Modulo FE non impostato');
        }

        $this->info('ricezione completata');
    }
}
