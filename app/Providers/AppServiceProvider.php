<?php

namespace App\Providers;

use App\Service\Socialite\CustomTwitterProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory as Socialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend(Socialite::class, function ($service, $app) {
            $service->extend('twitter', function ($app) use ($service) {
                $config = $app['config']['services.twitter'];

                return new CustomTwitterProvider(
                    $app['request'],
                    $config['client_id'],
                    $config['client_secret'],
                    $config['redirect']
                );
            });

            return $service;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
