<?php

namespace Jacofda\StartupPreset;

use Illuminate\Foundation\Console\Presets\Preset as LaravelPreset;
use Illuminate\Support\Facades\File;

class Preset extends LaravelPreset
{

    public static function install()
    {
        static::cleanSassDirectory();
        static::updatePackages();
        static::updateMix();
        static::updateScripts();
        static::updateStyles();
    }

    public static function cleanSassDirectory()
    {
        File::cleanDirectory(resource_path('assets/sass'));
    }


    public static function updatePackageArray()
    {
        return [
            'bootstrap' => '^4.5.0',
            'popper.js' => '^1.16',
            'jquery' => '^3.5.0',
            'lodash' => '^4.17'
        ];
    }

    public static function updateMix()
    {
        copy(__DIR__.'/stubs/webpack.mix.js', base_path('webpack.mix.js'));
    }

    public static function updateScripts()
    {
        copy(__DIR__.'/stubs/app.js', resource_path('js/app.js'));
        copy(__DIR__.'/stubs/bootstrap.js', resource_path('js/bootstrap.js'));
    }

    public static function updateStyles()
    {
        File::put(resource_path('sass/app.sass'), '');
    }


}
