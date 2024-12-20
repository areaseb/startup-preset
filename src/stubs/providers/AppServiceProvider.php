<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Mail\Mailer;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\UrlGenerator as LaravelUrlGenerator;
use Areaseb\Core\Services\UrlGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('custom.mailer', function ($app, $parameters) {
            $smtp_host = array_get($parameters, 'MAIL_HOST');
            $smtp_port = array_get($parameters, 'MAIL_PORT');
            $smtp_username = array_get($parameters, 'MAIL_USERNAME');
            $smtp_password = array_get($parameters, 'MAIL_PASSWORD');
            $smtp_encryption = array_get($parameters, 'MAIL_ENCRYPTION');

            $from_email = array_get($parameters, 'MAIL_FROM_ADDRESS');
            $from_name = array_get($parameters, 'MAIL_FROM_NAME');

            $from_email = $parameters['MAIL_FROM_ADDRESS'];
            $from_name = $parameters['MAIL_FROM_NAME'];

            $transport = new \Swift_SmtpTransport($smtp_host, $smtp_port);
            $transport->setUsername($smtp_username);
            $transport->setPassword($smtp_password);
            $transport->setEncryption($smtp_encryption);

            $swift_mailer = new \Swift_Mailer($transport);

            $mailer = new Mailer($app->get('view'), $swift_mailer, $app->get('events'));
            $mailer->alwaysFrom($from_email, $from_name);
            $mailer->alwaysReplyTo($from_email, $from_name);

            return $mailer;
        });
        
        
        $this->app->singleton('url', function ($app) {
		    return new \Areaseb\Core\Services\UrlGenerator(
		        $app['router']->getRoutes(),
		        $app['request']
		    );
		});
        
        $this->app->singleton(LaravelUrlGenerator::class, UrlGenerator::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    	$this->app->singleton(\Illuminate\Routing\UrlGenerator::class, function ($app) {
	        return new \Areaseb\Core\Services\UrlGenerator(
	            $app['router']->getRoutes(), // Ottieni la RouteCollection
	            $app['request']              // Ottieni la richiesta corrente
	        );
	    });    
    
        setlocale(LC_TIME,'it');
        date_default_timezone_set('Europe/Rome');
        Schema::defaultStringLength(191);

		Paginator::useBootstrapFive();
		Paginator::useBootstrapFour();
    }
}
