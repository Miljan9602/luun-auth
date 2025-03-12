<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{

    public function redirect()
    {
        dd("asd");
        return response()->json([
            'status' => 'ok',
            'url' => Socialite::driver('twitter')->redirect()->getTargetUrl()
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function callback()
    {
        $twitterData = Socialite::driver('twitter')->user()->getRaw();

        dd($twitterData);

        $user = User::updateOrCreate(
            ['twitter_id' => $twitterData['id']],
            [
                'name' => $twitterData['name'],
                'description' => $twitterData['description'],
                'twitter_username' => $twitterData['username'],
                'twitter_created_at' => Carbon::parse($twitterData['created_at']),
                'location' => $twitterData['location'],
                'profile_image_url' => $twitterData['profile_image_url'],
                'twitter_verified' => $twitterData['verified'],
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
