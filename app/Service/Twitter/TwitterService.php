<?php

namespace App\Service\Twitter;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TwitterService
{

    /**
     * Auth key for Twitter API
     *
     * @var string
     */
    protected string $apiKey;

    /**
     * Endpoint which we need to use to access the Twitter API
     *
     * @var string
     */
    protected string $host;

    /**
     * @param string $apiKey
     * @param string $host
     */
    public function __construct(string $apiKey, string $host)
    {
        $this->apiKey = $apiKey;
        $this->host = $host;
    }


    public function search(string $ticker, string $type, string $cursor = null, int $count = 100) : array
    {
        $query = [
            'query' => $ticker,
            'type' => $type,
            'count' => $count
        ];

        if ($cursor !== null) {
            $query['cursor'] = $cursor;
        }

        $result = $this->sendRequest($query);

        return [
            'cursor' => $this->parseCursor($result),
            'tweets' => $this->filterTweets($result),
        ];
    }

    private function filterTweets(array $response) : array
    {
        $items = Arr::get(Arr::first(Arr::get($response, 'result.timeline.instructions')), 'entries');

        $tweets = [];

        foreach ($items as $originalItem) {

            $item = Arr::get($originalItem, 'content.itemContent.tweet_results.result');

            $tweet = Arr::get($item, 'legacy', Arr::get($item, 'tweet.legacy'));
            $user = Arr::get($item, 'core.user_results.result.legacy', Arr::get($item, 'tweet.core.user_results.result.legacy'));


            if (Arr::get($tweet, 'user_id_str') === null) {
                print_r(json_encode($originalItem));
                DB::connection('mongodb')->table('unknown_tweets')->insert($originalItem);
                continue;
            }

            $createdAt = Carbon::createFromFormat('D M d H:i:s O Y', Arr::get($tweet, 'created_at'));
            $tweet['created_at_timestamp'] = $createdAt->timestamp;
            $tweet['created_at'] = $createdAt;

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

            $tweet['post_type'] = $this->getPostType($tweet);
            $tweet['username_mentions'] = array_unique($usernameMentions);
            $tweet['user_ids_mentions'] = array_unique($userIdsMentions);
            $tweet['tickers'] = array_unique($tickers);

            $tweet['ticker_mentions_count'] = count($tickers);
            $tweet['user_mentions_count'] = count($userMentions);

            $user['user_id'] = Arr::get($tweet, 'user_id_str');

            $tweets[] = [
                'tweet' => $tweet,
                'user' => $user,
            ];
        }

        return $tweets;
    }

    private function parseCursor(array $response) : array
    {
        return Arr::get($response, 'cursor');
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

    private function sendRequest($query = [])
    {
        $json = (new Client())->get($this->host.'/search-v2', [
            'headers' => [
                'x-rapidapi-key' => $this->apiKey
            ],
            'verify' => false,
            'proxy' => 'http://127.0.0.1:8080',
            'query' => $query,

        ])->getBody()->getContents();

        return json_decode($json, true);
    }
}
