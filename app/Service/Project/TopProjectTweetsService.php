<?php

namespace App\Service\Project;

use App\Repository\Tweet\TweetRepositoryInterface;
use Carbon\Carbon;

class TopProjectTweetsService
{

    protected TweetRepositoryInterface $repository;

    /**
     * @param TweetRepositoryInterface $repository
     */
    public function __construct(TweetRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function showTweets(string $twitterProjectId): array
    {
        return $this->repository->getTweetsFromQuery('post', [
            ['user_id_str' => $twitterProjectId],
            ['created_at', '>=', Carbon::now()->subDays(25)],
            ['retweet_count', '>', 5],
            ['reply_count', '>', 5],
            ['bookmark_count', '>', 0],
            ['favorite_count' => 10]
        ]);
    }
}
