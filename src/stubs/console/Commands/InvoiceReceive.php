<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Fe\Actions\Receive;

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
        (new Receive)->init();
        $this->info('ricezione completata');
    }
}
