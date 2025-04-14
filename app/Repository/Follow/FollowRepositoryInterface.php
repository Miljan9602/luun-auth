<?php

namespace App\Repository\Follow;

interface FollowRepositoryInterface
{
    /**
     * @param string $followerId
     * @param string $followedId
     * @return void
     */
    public function follow(string $followerId, string $followedId): void;

    /**
     * @param string $followerId
     * @param string $followedId
     * @return void
     */
    public function unfollow(string $followerId, string $followedId): void;

    /**
     * @param string $followerId
     * @param string $followedId
     * @return bool
     */
    public function hasFollow(string $followerId, string $followedId): bool;

    /**
     * Array key is user id of a follower and value is date when the follow has been made.
     * @param string $userId
     * @return array
     */
    public function getFollowers(string $userId): array;

    /**
     * Array key is user id of a follower and value is date when the follow has been made.
     *
     * @param string $userId
     * @return array
     */
    public function getFollowing(string $userId): array;
}
