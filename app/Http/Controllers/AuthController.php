<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{

    public function me()
    {
        return response()->json([
            'status' => 'ok',
            'user' => new UserResource(auth()->user())
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function redirect()
    {
        return response()->json([
            'status' => 'ok',
            'url' => Socialite::driver('twitter')->redirect()->getTargetUrl()
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function callback()
    {
        $twitterData = Socialite::driver('twitter')->user()->getRaw();

        $user = User::updateOrCreate(
            ['twitter_id' => Arr::get($twitterData, 'id')],
            [
                'name' => Arr::get($twitterData, 'name'),
                'description' => Arr::get($twitterData, 'description'),
                'twitter_username' => Arr::get($twitterData, 'username'),
                'twitter_created_at' => Carbon::parse($twitterData['created_at']),
                'location' => Arr::get($twitterData, 'location'),
                'profile_image_url' => Arr::get($twitterData, 'profile_image_url'),
                'twitter_verified' => Arr::get($twitterData, 'verified'),
                'followers_count' => Arr::get($twitterData, 'public_metrics.followers_count'),
                'following_count' => Arr::get($twitterData, 'public_metrics.following_count'),
                'tweet_count' => Arr::get($twitterData, 'public_metrics.tweet_count'),
                'listed_count' => Arr::get($twitterData, 'public_metrics.listed_count'),
                'like_count' => Arr::get($twitterData, 'public_metrics.like_count'),
                'media_count' => Arr::get($twitterData, 'public_metrics.media_count'),
            ]
        );

        Passport::personalAccessTokensExpireIn(Carbon::now()->addDays(2));

        Auth::setUser($user);

        return response()->json([
            'status' => 'ok',
            'access_token' => Auth::user()->createToken('dswap')->accessToken,
        ]);
    }
}
