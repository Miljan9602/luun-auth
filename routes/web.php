<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'api/v1'], function ($router) {

    Route::group(['prefix' => 'auth'], function ($router) {

        Route::group(['prefix' => 'twitter'], function ($router) {

            Route::any('/', [\App\Http\Controllers\AuthController::class, 'redirect'])
                ->name('auth.twitter');

            Route::any('/callback', [\App\Http\Controllers\AuthController::class, 'callback'])
                ->name('auth.twitter.callback');
        });

    });
});
