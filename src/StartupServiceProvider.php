<?php

namespace Areaseb\StartupPreset;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\PresetCommand;
//use Laravel\Ui\UiCommand;

class StartupServiceProvider extends ServiceProvider
{
    public function boot()
    {
/*        UiCommand::macro('startup', function($command){
            // Preset::install();
            $command->info('All done. Make sure to require the others packages and compile your assets');
        });*/
    }
}
