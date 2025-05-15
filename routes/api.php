<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function ($router) {

    Route::group(['prefix' => 'auth'], function ($router) {

        Route::group(['prefix' => 'twitter'], function ($router) {

            Route::any('/', [\App\Http\Controllers\AuthController::class, 'redirect'])
                ->name('auth.twitter');

            Route::any('/callback', [\App\Http\Controllers\AuthController::class, 'callback'])
                ->name('auth.twitter.callback');
        });
    });

    Route::group(['prefix' => 'campaigns'], function ($router) {

        Route::get('/', \App\Http\Controllers\Campaign\AllCampaignsController::class)
            ->name('campaigns.all');

        Route::group(['prefix' => '{campaign_id}', 'middleware' => [\App\Http\Middleware\InjectCampaignMiddleware::class]], function ($router) {

            Route::post('/verify', \App\Http\Controllers\Campaign\VerifyTaskController::class)
                ->middleware('auth:api')
                ->name('campaigns.verify');

        });

    });

    Route::group(['prefix' => 'projects'], function ($router) {

        Route::get('/', \App\Http\Controllers\Project\ShowAllProjectsController::class)
            ->name('projects.all');

        Route::group(['prefix' => '{project_id}', 'middleware' => [\App\Http\Middleware\InjectProjectMiddleware::class]], function ($router) {

            Route::get('/', \App\Http\Controllers\Project\SingleProjectController::class)
                ->name('projects.show');

            Route::get('/trending', \App\Http\Controllers\Project\ProjectTopTweetsController::class)
                ->name('projects.trending');

            Route::get('/campaigns', \App\Http\Controllers\Campaign\ShowProjectCampaignsController::class)
                ->name('projects.campaigns');

        });
    });

    Route::group(['prefix' => 'users'], function ($router) {

        Route::get('me', [\App\Http\Controllers\UserController::class, 'me'])
            ->middleware('auth:api')
            ->name('auth.user.me');

        Route::delete('wallet', [\App\Http\Controllers\UserController::class, 'updateWallet'])
            ->middleware('auth:api')
            ->name('auth.user.wallet');

        Route::get('followers', \App\Http\Controllers\Relationship\FollowersController::class)
            ->middleware('auth:api')
            ->name('auth.user.followers');

        Route::get('following', \App\Http\Controllers\Relationship\FollowingController::class)
            ->middleware('auth:api')
            ->name('auth.user.following');


        Route::group(['prefix' => '/{user_id}'], function ($router) {

            Route::post('follow', \App\Http\Controllers\Relationship\FollowController::class)
                ->middleware('auth:api')
                ->name('auth.user.follow');

            Route::delete('follow', \App\Http\Controllers\Relationship\UnfollowController::class)
                ->middleware('auth:api')
                ->name('auth.user.unfollow');

        });
    });
});
