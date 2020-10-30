<?php

namespace Jacofda\StartupPreset;

use Illuminate\Foundation\Console\Presets\Preset as LaravelPreset;
use Illuminate\Support\Facades\{File, Storage};

class Preset extends LaravelPreset
{

    public static function install()
    {
        static::copyAssetsToPublic();
        static::updateDatabaseFolder();
        static::updateProvidersFolder();
        static::generateMediaSupport();
        static::insertClasses();
        static::insertRoutes();
        static::updateConfigFolder();
    }


    public static function copyAssetsToPublic()
    {
        copy(__DIR__.'/stubs/public/favicon.ico', public_path('favicon.ico'));
        copy(__DIR__.'/stubs/public/robots.txt', public_path('robots.txt'));
        File::copyDirectory(__DIR__.'/stubs/assets/calendar', public_path('calendar'));
        File::copyDirectory(__DIR__.'/stubs/assets/css', public_path('css'));
        File::copyDirectory(__DIR__.'/stubs/assets/editor', public_path('editor'));
        File::copyDirectory(__DIR__.'/stubs/assets/img', public_path('img'));
        File::copyDirectory(__DIR__.'/stubs/assets/js', public_path('js'));
        File::copyDirectory(__DIR__.'/stubs/assets/plugins', public_path('plugins'));
    }


    public static function updateConfigFolder()
    {
        unlink(config_path('app.php'));
        copy(__DIR__.'/stubs/config/app.php', config_path('app.php'));

        unlink(base_path('config/auth.php'));
        copy(__DIR__.'/stubs/config/auth.php', config_path('auth.php'));

        unlink(base_path('config/database.php'));
        copy(__DIR__.'/stubs/config/database.php', config_path('database.php'));

        if( file_exists(base_path('config/logging.php')) )
        {
            unlink(base_path('config/logging.php'));
        }
        copy(__DIR__.'/stubs/config/logging.php', config_path('logging.php'));

        copy(__DIR__.'/stubs/config/snappy.php', config_path('snappy.php'));
        copy(__DIR__.'/stubs/config/permission.php', config_path('permission.php'));
    }

    public static function updateDatabaseFolder()
    {
        unlink(database_path('migrations/2014_10_12_000000_create_users_table.php'));
        copy(__DIR__.'/stubs/database/migrations/2014_10_12_000000_create_users_table.php', database_path('migrations/2014_10_12_000000_create_users_table.php'));
    }

    public static function updateProvidersFolder()
    {
        unlink(app_path('Providers/AppServiceProvider.php'));
        copy(__DIR__.'/stubs/providers/AppServiceProvider.php', app_path('Providers/AppServiceProvider.php'));
        unlink(app_path('Providers/RouteServiceProvider.php'));
        copy(__DIR__.'/stubs/providers/RouteServiceProvider.php', app_path('Providers/RouteServiceProvider.php'));
        copy(__DIR__.'/stubs/providers/ViewServiceProvider.php', app_path('Providers/ViewServiceProvider.php'));
    }

    public static function generateMediaSupport()
    {
        $directories = [
        	'public/editor/',
            'public/editor/original',
            'public/editor/display',
            'public/editor/full',
            'public/settings',
            'public/calendars',
            'public/fe',
            'public/fe/inviate/2019',
            'public/fe/inviate/2020',
            'public/fe/inviate/2021',
            'public/fe/ricevute/2019',
            'public/fe/ricevute/2020',
            'public/fe/ricevute/2021',
            'public/fe/pdf',
            'public/fe/pdf/inviate',
            'public/fe/pdf/ricevute'
        ];
        foreach($directories as $directory)
        {
            if( !file_exists(storage_path('app/'.$directory) ))
            {
                Storage::disk('local')->makeDirectory($directory);
            }

        }

        $mediatypes = app_path('Mediatypes');
        if( !file_exists($mediatypes) )
        {
            File::makeDirectory($mediatypes, 0755, true);
        }

        copy(__DIR__.'/stubs/mediatypes/MediaGeneral.php', app_path('Mediatypes/MediaGeneral.php'));
        copy(__DIR__.'/stubs/mediatypes/MediaEditor.php', app_path('Mediatypes/MediaEditor.php'));
    }

    public static function insertClasses()
    {
        unlink(app_path('User.php'));
        copy(__DIR__.'/stubs/classes/User.php', app_path('User.php'));
    }

    public static function insertRoutes()
    {
        unlink(base_path('routes/web.php'));
        copy(__DIR__.'/stubs/routes/web.php', base_path('routes/web.php'));
    }



}
