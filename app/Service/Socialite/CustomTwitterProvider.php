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
            RequestOptions::HEADERS => ['Authorization' => 'Bearer '.$token],
            RequestOptions::QUERY => ['user.fields' => 'created_at,description,id,location,name,pinned_tweet_id,profile_image_url,protected,public_metrics,username,verified,verified_followers_count'],
        ]);

        return Arr::get(json_decode($response->getBody(), true), 'data');
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'] ?? null,
            'nickname' => $user['username'] ?? null,
            'name' => $user['name'] ?? null,
            'avatar' => $user['profile_image_url'] ?? null,
            'created_at' => $user['created_at'] ?? null,
            'location' => $user['location'] ?? null,
            'verified' => $user['verified'] ?? null,
            'description' => $user['description'] ?? null,
            'public_metrics' => $user['public_metrics'] ?? null,
            'verified_followers_count' => $user['verified_followers_count'] ?? null,
            'pinned_tweet_id' => $user['pinned_tweet_id'] ?? null,
            'protected' => $user['protected'] ?? null,
            'username' => $user['username'] ?? null,
        ]);
    }
}
