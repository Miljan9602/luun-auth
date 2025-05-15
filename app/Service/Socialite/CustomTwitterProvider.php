<?php

namespace App\Service\Socialite;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\TwitterProvider;
use Laravel\Socialite\Two\User;

class CustomTwitterProvider extends TwitterProvider
{

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.twitter.com/2/users/me', [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer ' . $token],
            RequestOptions::QUERY => ['user.fields' => 'created_at,description,id,location,name,pinned_tweet_id,profile_image_url,protected,public_metrics,username,verified,verified_followers_count'],
        ]);

        return Arr::get(json_decode($response->getBody(), true), 'data');
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'id'),
            'name' => Arr::get($user, 'name'),
            'avatar' => Arr::get($user, 'profile_image_url'),
            'created_at' => Arr::get($user, 'created_at'),
            'location' => Arr::get($user, 'location'),
            'verified' => Arr::get($user, 'verified'),
            'description' => Arr::get($user, 'description'),
            'public_metrics' => Arr::get($user, 'public_metrics'),
            'verified_followers_count' => Arr::get($user, 'verified_followers_count'),
            'pinned_tweet_id' => Arr::get($user, 'pinned_tweet_id'),
            'protected' => Arr::get($user, 'protected'),
            'username' => Arr::get($user, 'username'),
        ]);
    }
}
