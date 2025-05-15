<?php

namespace App\Console\Commands\Script;

use App\Repository\Tweet\TweetRepositoryInterface;
use App\Repository\XUser\XUserRepositoryInterface;
use App\Service\Twitter\TwitterService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SaveTweetByIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:save-tweet-by-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var TweetRepositoryInterface
     */
    protected TweetRepositoryInterface $tweetRepository;

    /**
     * @var XUserRepositoryInterface
     */
    protected XUserRepositoryInterface $xUserRepository;


    /**
     * @param TweetRepositoryInterface $tweetRepository
     * @param XUserRepositoryInterface $xUserRepository
     */
    public function __construct(TweetRepositoryInterface $tweetRepository, XUserRepositoryInterface $xUserRepository)
    {
        parent::__construct();
        $this->tweetRepository = $tweetRepository;
        $this->xUserRepository = $xUserRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $twitterService = app()->make(TwitterService::class);

        $items = $twitterService->getTweetsByIds([
            '1912004301309755463'
        ]);

        foreach ($items as $item) {

            $tweet = Arr::get($item, 'tweet');
            $user = Arr::get($item, 'user');

            $this->saveTweet($tweet, $user, 'custom_ds');
        }
    }

    private function saveTweet(array $tweet, array $user, $searchKey)
    {
        $userId = Arr::get($user, 'user_id');
        $tweetId = Arr::get($tweet, 'id_str');

        $user['updated_at'] = Carbon::now()->carbonize();

        $tweet['updated_at'] = Carbon::now()->carbonize();
        $tweet['inserted_at'] = Carbon::now()->carbonize();
        $tweet['search_key'] = $searchKey;
        $postType = Arr::get($tweet, 'post_type');

        echo "Tweet ID: $tweetId => User ID: $userId => CreatedAt => " . Arr::get($tweet, 'created_at')->toDateTimeString() . PHP_EOL;


        if (!$this->xUserRepository->getUserById($userId)) {
            $user['inserted_at'] = Carbon::now()->carbonize();
        }

        if (!$this->tweetRepository->getTweetById($postType, $tweetId)) {
            $tweet['inserted_at'] = Carbon::now();
        }

        $this->xUserRepository->createOrUpdateUser($userId, $user);
        $this->tweetRepository->createOrUpdateTweet($postType, $tweetId, $tweet);
    }
}
