<?php

namespace App\Console\Commands\Process;

use App\Repository\Tweet\TweetRepositoryInterface;
use App\Repository\XUser\XUserRepositoryInterface;
use App\Service\Twitter\TwitterService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class MineTweets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mine-tweets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(TwitterService $twitterService, TweetRepositoryInterface $tweetRepository, XUserRepositoryInterface $userRepository)
    {
        $names = ['@SeiNetwork', '$SEI'];

        $loopCountTypes = [
            'latest' => 6,
            'media' => 2,
            'top' => 2
        ];

        foreach ($names as $name) {
            foreach ($loopCountTypes as $type => $loopCount) {

                $cursor = null;

                for ($i=0; $i < $loopCount; $i++) {

                    $result = $twitterService->search($name, $type, $cursor);

                    $cursorData = Arr::get($result, 'cursor');
                    $items = Arr::get($result, 'tweets');

                    foreach ($items as $item) {

                        $tweet = Arr::get($item, 'tweet');
                        $user = Arr::get($item, 'user');

                        $userId = Arr::get($user, 'user_id');
                        $tweetId = Arr::get($tweet, 'id_str');

                        $user['updated_at'] = Carbon::now()->carbonize();

                        $tweet['updated_at'] = Carbon::now()->carbonize();
                        $tweet['inserted_at'] = Carbon::now()->carbonize();
                        $tweet['search_key'] = $name;
                        $postType = Arr::get($tweet, 'post_type');

                        echo "Tweet ID: $tweetId => User ID: $userId => CreatedAt => ".Arr::get($tweet, 'created_at')->toDateTimeString().PHP_EOL;


                        if (!$userRepository->getUserById($userId)) {
                            $user['inserted_at'] = Carbon::now()->carbonize();
                        }

                        if (!$tweetRepository->getTweetById($postType, $tweetId)) {
                            $tweet['inserted_at'] = Carbon::now();
                        }

                        $userRepository->createOrUpdateUser($userId, $user);
                        $tweetRepository->createOrUpdateTweet($postType, $tweetId, $tweet);
                    }

                    $cursor = Arr::get($cursorData, 'bottom');
                }
            }
        }
    }
}
