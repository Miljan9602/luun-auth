<?php

namespace App\Providers;

use App\Repository\Follow\EloquentFollowRepository;
use App\Repository\Follow\FollowRepositoryInterface;
use App\Repository\Project\EloquentProjectRepository;
use App\Repository\Project\ProjectRepositoryInterface;
use App\Repository\User\EloquentUserRepository;
use App\Repository\User\UserRepositoryInterface;
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

        $this->app->bind(FollowRepositoryInterface::class, EloquentFollowRepository::class);

        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        $this->app->bind(ProjectRepositoryInterface::class, EloquentProjectRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
