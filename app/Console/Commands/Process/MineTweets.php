<?php

namespace App\Console\Commands\Process;

use App\Exceptions\TwitterApi\ReplaceCursorException;
use App\Repository\Tweet\TweetRepositoryInterface;
use App\Repository\XUser\XUserRepositoryInterface;
use App\Service\Twitter\TwitterService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
    public function handle(TwitterService $twitterService)
    {
        $names = ['@dragonswap_dex', '$DSWAP', '$SEI', '@SeiNetwork'];
        $names = ['@dragonswap_dex', '$DSWAP'];
        $cursorDirection = 'bottom';

        $names = ['@dragonswap_dex'];

        $loopCountTypes = [
            'latest' => 150,
            'media' => 20,
            'top' => 20
        ];

        foreach ($names as $name) {
            foreach ($loopCountTypes as $type => $loopCount) {

//                $cursorData = DB::connection('mongodb')->table('cursor')->where([
//                    'type' => $type,
//                    'search_key' => $name
//                ])->first();

                $cursor = null;

//                if ($cursorData !== null) {
//                    $cursor = $cursorData->{$cursorDirection};
//                }

                for ($i = 0; $i < $loopCount; $i++) {

                    $result = $twitterService->search($name, $type, $cursor, $cursorDirection);

                    $cursorData = Arr::get($result, 'cursor');
                    $items = Arr::get($result, 'tweets');

                    if (empty($items)) {
                        break;
                    }

                    foreach ($items as $item) {
                        $tweet = Arr::get($item, 'tweet');
                        $user = Arr::get($item, 'user');

                        $this->saveTweet($tweet, $user,$name);
                    }

                    DB::connection('mongodb')->table('cursor')->updateOrInsert([
                        'type' => $type,
                        'search_key' => $name
                    ], [
                        'bottom' =>  Arr::get($cursorData, 'bottom'),
                        'top' => Arr::get($cursorData, 'top'),
                        'updated_at' => Carbon::now()->carbonize(),
                    ]);

                    $cursor = Arr::get($cursorData, $cursorDirection);
                }
            }
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
