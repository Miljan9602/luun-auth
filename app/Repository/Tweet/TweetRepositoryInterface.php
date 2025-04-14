<?php

namespace App\Repository\Tweet;

interface TweetRepositoryInterface
{
    /**
     * @param string $collection
     * @param string $tweetId
     * @return array|null
     */
    public function getTweetById(string $collection, string $tweetId) : ?array;

    /**
     * @param string $collection
     * @param string $tweetId
     * @param array $tweetData
     * @return mixed
     */
    public function createOrUpdateTweet(string $collection, string $tweetId, array $tweetData = []);

    /**
     * @param string $collection
     * @param array $query
     * @param int $limit
     * @return mixed
     */
    public function getTweetsFromQuery(string $collection, array $query, int $limit = 25);
}
