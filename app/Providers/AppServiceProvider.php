<?php

namespace App\Providers;

use App\Repository\Campaign\CampaignRepositoryInterface;
use App\Repository\Campaign\EloquentCampaignRepository;
use App\Repository\CampaignTask\CampaignTaskRepositoryInterface;
use App\Repository\CampaignTask\EloquentCampaignTaskRepository;
use App\Repository\Follow\EloquentFollowRepository;
use App\Repository\Follow\FollowRepositoryInterface;
use App\Repository\Project\EloquentProjectRepository;
use App\Repository\Project\ProjectRepositoryInterface;
use App\Repository\Tweet\MongoTweetRepository;
use App\Repository\Tweet\TweetRepositoryInterface;
use App\Repository\User\EloquentUserRepository;
use App\Repository\User\UserRepositoryInterface;
use App\Repository\XUser\MongoXUserRepository;
use App\Repository\XUser\XUserRepositoryInterface;
use App\Service\Socialite\CustomTwitterProvider;
use App\Service\Twitter\TwitterService;
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

        $this->app->singleton(FollowRepositoryInterface::class, EloquentFollowRepository::class);

        $this->app->singleton(UserRepositoryInterface::class, EloquentUserRepository::class);

        $this->app->singleton(ProjectRepositoryInterface::class, EloquentProjectRepository::class);

        $this->app->singleton(TweetRepositoryInterface::class, MongoTweetRepository::class);

        $this->app->bind(XUserRepositoryInterface::class, MongoXUserRepository::class);

        $this->app->bind(CampaignRepositoryInterface::class, EloquentCampaignRepository::class);

        $this->app->bind(CampaignTaskRepositoryInterface::class, EloquentCampaignTaskRepository::class);

        $this->app->bind(TwitterService::class, function () {
            $apiKey = config('services.rapid_api.api_key');
            $host = config('services.rapid_api.host');

            return new TwitterService($apiKey, $host);
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
