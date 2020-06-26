<?php

namespace Jacofda\StartupPreset;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\PresetCommand;

class StartupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        PresetCommand::macro('startup', function($command){
            Preset::install();

            $command->info('All done. Make sure to require the others packages and compile your assets');
        });
    }
}
