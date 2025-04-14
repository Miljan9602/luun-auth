<?php

namespace App\Console\Commands\Script;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private function jobs(array $query)
    {
        $url = 'https://jsearch.p.rapidapi.com/search';
        $json = (new Client())->get($url, [
            'headers' => [
                'x-rapidapi-key' => env('RAPID_URL')
            ],
            'query' => $query,
            'verify' => false,
            'proxy' => 'http://127.0.0.1:8080'
        ])->getBody()->getContents();

        return json_decode($json, true);
    }


    /**
     * Execute the console command.
     */
    public function handle()
    {
//        $queries = ['laravel', 'php'];
//
//        foreach ($queries as $query) {
//            $pages = ['1', '2','3','4','5'];
//
//            foreach ($pages as $page) {
//                echo "Query: $query => $page".PHP_EOL;
//
//                $items = Cache::remember($query.'_'.$page, 600, function () use ($query, $page) {
//                    return $this->jobs([
//                        'query' => $query,
//                        'page' => $page,
//                        'num_pages' => '20',
//                        'country' => 'us',
//                        'date_posted' => 'all'
//                    ]);
//                });
//
//                $items = Arr::get($items, 'data');
//
//                foreach ($items as $item) {
//                    $id = Arr::get($item, 'job_id');
//                    DB::connection('mongodb')->table('backend')->updateOrInsert(['job_id' => $id],  $item);
//                }
//
//            }
//        }
//
//        return;

        $type = 'media'; // 'latestlist','top', 'media'

        $profileName = strtoupper('$SEI');
//        $profileName = '@SeiNetwork';
        $cursor = null;

        while (true) {

            try {
                $result = $this->search($profileName, $type, $cursor);

                $cursorData = Arr::get($result, 'cursor');

                $bottom = Arr::get($cursorData, 'bottom');
                $top = Arr::get($cursorData, 'top');

                $items = Arr::get(Arr::first(Arr::get($result, 'result.timeline.instructions')), 'entries');

                foreach ($items as $originalItem) {

                    echo Arr::get($originalItem, 'content.entryType').PHP_EOL;

                    $item = Arr::get($originalItem, 'content.itemContent.tweet_results.result');

                    $tweet = Arr::get($item, 'legacy', Arr::get($item, 'tweet.legacy'));
                    $user = Arr::get($item, 'core.user_results.result.legacy', Arr::get($item, 'tweet.core.user_results.result.legacy'));

                    $userId = Arr::get($tweet, 'user_id_str');
                    $tweetId = Arr::get($tweet, 'id_str');

                    if ($userId === null) {
                        print_r(json_encode($originalItem));
                        DB::connection('mongodb')->table('unknown_tweets')->insert($originalItem);
                        continue;
                    }

                    $createdAt = Carbon::createFromFormat('D M d H:i:s O Y', Arr::get($tweet, 'created_at'));
                    $tweet['updated_at'] = Carbon::now()->carbonize();
                    $tweet['inserted_at'] = Carbon::now()->carbonize();
                    $tweet['created_at_timestamp'] = $createdAt->timestamp;
                    $tweet['created_at'] = $createdAt;
                    $tweet['search_key'] = $profileName;

                    $entities = Arr::get($tweet, 'entities', []);
                    $userMentions = Arr::get($entities, 'user_mentions', []);
                    $symbolMentions = Arr::get($entities, 'symbols', []);

                    $usernameMentions = [];
                    $userIdsMentions = [];
                    $tickers = [];

                    foreach ($userMentions as $mention) {
                        $usernameMentions[] = $mention['screen_name'];
                        $userIdsMentions[] = $mention['id_str'];
                    }

                    foreach ($symbolMentions as $symbolMention) {
                        $tickers[] = strtoupper($symbolMention['text']);
                    }

                    $tweet['username_mentions'] = array_unique($usernameMentions);
                    $tweet['user_ids_mentions'] = array_unique($userIdsMentions);
                    $tweet['tickers'] = array_unique($tickers);
                    $tweet['post_type'] = $this->getPostType($tweet);

                    $tweet['ticker_mentions_count'] = count($tickers);
                    $tweet['user_mentions_count'] = count($userMentions);

                    echo "Tweet ID: ".$tweetId." => User ID: $userId => CreatedAt => ".$createdAt->toDateTimeString().PHP_EOL;

                    $user['updated_at'] = Carbon::now();
                    $user['user_id'] = $userId;

                    $res = DB::connection('mongodb')->table('users')->where(['user_id' => $userId]);

                    if ($res === null) {
                        $user['inserted_at'] = Carbon::now();
                    }

                    DB::connection('mongodb')->table('users')->updateOrInsert(['user_id' => $userId],  $user);

                    $res = DB::connection('mongodb')->table('tweets')->where('id_str', $tweetId)->first();

                    if ($res === null) {
                        $tweet['inserted_at'] = Carbon::now();
                    }

                    DB::connection('mongodb')->table(Arr::get($tweet, 'post_type'))->updateOrInsert(['id_str' => $tweetId],  $tweet);
                }
                $cursor = $bottom;

                echo $profileName." => ".$cursor.PHP_EOL;

                sleep(5);
            }catch (\Exception $e) {
                echo "Error: ".$e->getMessage().PHP_EOL;
                sleep(5);
            }
        }
    }

    private function getPostType(array $tweet): string
    {
        if (isset($tweet['retweeted_status'])) {
            return 'retweet';
        }

        if (!empty($tweet['is_quote_status'])) {
            return 'quote';
        }

        if (!empty($tweet['in_reply_to_status_id_str'])) {
            return 'comment';
        }

        return 'post';
    }

    private function search(string $query, $type = 'latest', $cursor = null, $count = 100)
    {
        $query = [
            'query' => $query,
            'type' => $type,
            'count' => $count
        ];

        if ($cursor !== null) {
            $query['cursor'] = $cursor;
        }

        $url = 'https://twitter241.p.rapidapi.com/search-v2';
        $json = (new Client())->get($url, [
            'headers' => [
                'x-rapidapi-key' => env('RAPID_URL')
            ],
            'query' => $query,
            'verify' => false,
            'proxy' => 'http://127.0.0.1:8080'
        ])->getBody()->getContents();

        return json_decode($json, true);
    }
}
