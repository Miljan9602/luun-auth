<?php

namespace App\Repository\Tweet;

use Illuminate\Support\Facades\DB;

class MongoTweetRepository implements TweetRepositoryInterface
{
    public function getTweetById(string $collection, string $tweetId): ?array
    {
        $result = DB::connection('mongodb')->table($collection)->where(['id_str' => $tweetId])->first();

        return $result ? (array) $result : null;
    }

    public function createOrUpdateTweet(string $collection, string $tweetId, array $tweetData = [])
    {
        return DB::connection('mongodb')->table($collection)->updateOrInsert(['id_str' => $tweetId], $tweetData);
    }

}
