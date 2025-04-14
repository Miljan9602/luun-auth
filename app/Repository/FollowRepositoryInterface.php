<?php

namespace App\Repository;

interface FollowRepositoryInterface
{
    public function follow(string $followerTwitterId, string $followedTwitterId): void;

    public function unfollow(string $followerTwitterId, string $followedTwitterId): void;

    /**
     * Array key is twitter id of a follower and value is date when the follow has been made.
     * @param string $twitterId
     * @return array
     */
    public function getFollowers(string $twitterId): array;

    /**
     * Array key is twitter id of a follower and value is date when the follow has been made.
     *
     * @param string $twitterId
     * @return array
     */
    public function getFollowing(string $twitterId): array;
}
