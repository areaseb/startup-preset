<?php

namespace Jacofda\StartupPreset;

use Illuminate\Foundation\Console\Presets\Preset as LaravelPreset;
use Illuminate\Support\Facades\File;

class Preset extends LaravelPreset
{

    public static function install()
    {
        static::moveContentsFromPublicToRoot();
        static::copyAssetsToPublic();
        static::updateMix();
        static::updateLangFolders();
        static::updateConfigFolder();
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
        unlink(base_path('config/app.php'));
        copy(__DIR__.'/stubs/config/app.php', base_path('config/app.php'));

        unlink(base_path('config/auth.php'));
        copy(__DIR__.'/stubs/config/auth.php', base_path('config/auth.php'));

        unlink(base_path('config/database.php'));
        copy(__DIR__.'/stubs/config/database.php', base_path('config/database.php'));


    }




}
