<?php

namespace Jacofda\StartupPreset;

use Illuminate\Foundation\Console\Presets\Preset as LaravelPreset;
use Illuminate\Support\Facades\{File, Storage};

class Preset extends LaravelPreset
{

    public static function install()
    {
        static::moveContentsFromPublicToRoot();
        static::copyAssetsToPublic();
        static::updateMix();
        static::updateLangFolders();
        static::updateConfigFolder();
        static::updateDatabaseFolder();
        static::updateProvidersFolder();
        static::generateMediaSupport();
        static::updateBaseController();
        static::insertClasses();
        static::insertviews();
        static::insertRoutes();
        // static::updateStyles();
    }

    public static function moveContentsFromPublicToRoot()
    {
        File::cleanDirectory(public_path('assets/sass'));
        copy(__DIR__.'/stubs/public/.htaccess', base_path('.htaccess'));
        copy(__DIR__.'/stubs/public/index.php', base_path('index.php'));
        copy(__DIR__.'/stubs/public/robots.txt', base_path('robots.txt'));
        copy(__DIR__.'/stubs/public/web.config', base_path('web.config'));
    }


    public static function copyAssetsToPublic()
    {
        File::copyDirectory(__DIR__.'/stubs/assets/fonts', public_path('fonts'));
        File::copyDirectory(__DIR__.'/stubs/assets/webfonts', public_path('webfonts'));
        File::copyDirectory(__DIR__.'/stubs/assets/img', public_path('img'));
        File::copyDirectory(__DIR__.'/stubs/assets/plugins', public_path('plugins'));
    }


    public static function updateMix()
    {
        copy(__DIR__.'/stubs/webpack.mix.js', base_path('webpack.mix.js'));
    }

    public static function updateLangFolders()
    {
        File::cleanDirectory(resource_path('lang'));
        File::copyDirectory(__DIR__.'/stubs/lang/en', resource_path('lang/en'));
        File::copyDirectory(__DIR__.'/stubs/lang/it', resource_path('lang/it'));
    }

    public static function updateConfigFolder()
    {
        unlink(config_path('app.php'));
        copy(__DIR__.'/stubs/config/app.php', config_path('app.php'));

        unlink(base_path('config/auth.php'));
        copy(__DIR__.'/stubs/config/auth.php', config_path('auth.php'));

        unlink(base_path('config/database.php'));
        copy(__DIR__.'/stubs/config/database.php', config_path('database.php'));
    }

    public static function updateDatabaseFolder()
    {
        unlink(database_path('seeds/DatabaseSeeder.php'));
        File::copyDirectory(__DIR__.'/stubs/database/seeds', database_path('seeds'));
        File::cleanDirectory(database_path('migrations'));
        File::copyDirectory(__DIR__.'/stubs/database/dumps', database_path('dumps'));
        File::copyDirectory(__DIR__.'/stubs/database/migrations', database_path('migrations'));
    }

    public static function updateProvidersFolder()
    {
        unlink(app_path('Providers/AppServiceProvider.php'));
        unlink(app_path('Providers/RouteServiceProvider.php'));
        copy(__DIR__.'/stubs/providers/AppServiceProvider.php', app_path('Providers/AppServiceProvider.php'));
        copy(__DIR__.'/stubs/providers/RouteServiceProvider.php', app_path('Providers/RouteServiceProvider.php'));
        copy(__DIR__.'/stubs/providers/RoleServiceProvider.php', app_path('Providers/RoleServiceProvider.php'));
        copy(__DIR__.'/stubs/providers/ViewServiceProvider.php', app_path('Providers/ViewServiceProvider.php'));
    }

    public static function generateMediaSupport()
    {
        $directories = [
        	'public/editor/',
            'public/editor/original',
            'public/editor/display',
            'public/editor/full',
            'public/original'
        ];
        foreach($directories as $directory)
        {
            Storage::disk('local')->makeDirectory($directory);
        }

        $mediatypes = app_path('Mediatypes');
        File::makeDirectory($mediatypes, 0755, true);
        copy(__DIR__.'/stubs/mediatypes/MediaGeneral.php', app_path('Mediatypes/MediaGeneral.php'));
        copy(__DIR__.'/stubs/mediatypes/MediaEditor.php', app_path('Mediatypes/MediaEditor.php'));
    }

    public static function updateBaseController()
    {
        File::cleanDirectory(app_path('Http/Controllers'));
        $files = [
            'Controller.php',
            'GeneralController.php',
            'LoginController.php',
            'NotificationController.php',
            'RoleController.php',
            'SettingController.php',
            'UserController.php'
        ];
        foreach($files as $file)
        {
            copy(__DIR__.'/stubs/controllers/'.$file, app_path('Http/Controllers/'.$file));
        }
    }

    public static function insertClasses()
    {
        File::copyDirectory(__DIR__.'/stubs/classes', app_path('Classes'));
    }

    public static function insertviews()
    {
        File::cleanDirectory(resource_path('views'));
        File::copyDirectory(__DIR__.'/stubs/views', resource_path('views'));
    }

    public static function insertRoutes()
    {
        unlink(base_path('routes/web.php'));
        copy(__DIR__.'/stubs/routes/web.php', base_path('routes/web.php'));
    }



}
