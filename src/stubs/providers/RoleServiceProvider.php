<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\{Auth, Blade};

class RoleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::if('hasrole', function($expression){
            if(Auth::user())
            {
                if(Auth::user()->hasRole($expression))
                {
                    return true;
                }
            }
            return false;
        });
    }
}
