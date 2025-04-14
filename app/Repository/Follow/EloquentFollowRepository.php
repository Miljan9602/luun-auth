<?php

namespace App\Repository\Follow;

use App\Models\Follow;

class EloquentFollowRepository implements FollowRepositoryInterface
{
    public function follow(string $followerId, string $followedId): void
    {
        Follow::create([
            'follower_id' => $followerId,
            'followed_id' => $followedId,
        ]);
    }

    public function unfollow(string $followerId, string $followedId): void
    {
        Follow::where([
            'follower_id' => $followerId,
            'followed_id' => $followedId,
        ])->delete();
    }

    public function hasFollow(string $followerId, string $followedId): bool
    {
        return Follow::where([
            'follower_id' => $followerId,
            'followed_id' => $followedId,
        ])->exists();
    }


    public function getFollowers(string $userId): array
    {
        $followers = Follow::where('followed_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get(['followed_id', 'created_at']);

        return $followers->pluck('created_at', 'followed_id')->toArray();
    }

    public function getFollowing(string $userId): array
    {
        $followers = Follow::where('follower_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get(['follower_id', 'created_at']);

        return $followers->pluck('created_at', 'follower_id')->toArray();
    }
}
