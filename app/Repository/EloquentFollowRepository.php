<?php

namespace App\Repository;

use App\Models\Follow;

class EloquentFollowRepository implements FollowRepositoryInterface
{
    public function follow(string $followerTwitterId, string $followedTwitterId): void
    {
        Follow::create([
            'follower_twitter_id' => $followerTwitterId,
            'followed_twitter_id' => $followedTwitterId,
        ]);
    }

    public function unfollow(string $followerTwitterId, string $followedTwitterId): void
    {
        Follow::where([
            'follower_twitter_id' => $followerTwitterId,
            'followed_twitter_id' => $followedTwitterId,
        ])->delete();
    }

    public function getFollowers(string $twitterId): array
    {
        $followers = Follow::where('followed_twitter_id', $twitterId)
            ->orderBy('created_at', 'desc')
            ->get(['follower_twitter_id', 'created_at']);

        return $followers->pluck('created_at', 'follower_twitter_id')->toArray();
    }

    public function getFollowing(string $twitterId): array
    {
        $following = Follow::where('follower_twitter_id', $twitterId)
            ->orderBy('created_at', 'desc')
            ->get(['followed_twitter_id', 'created_at']);

        return $following->pluck('created_at', 'followed_twitter_id')->toArray();
    }
}
