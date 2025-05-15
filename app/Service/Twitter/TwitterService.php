<?php

namespace App\Service\Twitter;

use App\Exceptions\TwitterApi\ReplaceCursorException;
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

    public function getTweetsByIds(array $ids)
    {
        $result = $this->sendRequest([
            'tweetIds' => implode(',', $ids),
        ], 'tweet-by-ids');

        return $this->filterTweets(Arr::get($result, 'result.tweetResult'), 'result');
    }


    /**
     * @param string $ticker
     * @param string $type
     * @param string|null $cursor
     * @param string $cursorDirection
     * @param int $count
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function search(string $ticker, string $type, string $cursor = null, string $cursorDirection, int $count = 100): array
    {
        $query = [
            'query' => $ticker,
            'type' => $type,
            'count' => $count
        ];

        if ($cursor !== null) {
            $query['cursor'] = $cursor;
        }

        $result = $this->sendRequest($query, 'search-v2');

        $items = Arr::get(Arr::first(Arr::get($result, 'result.timeline.instructions')), 'entries') ?? [];

        return [
            'cursor' => $this->parseCursor($result),
            'tweets' => $this->filterTweets($items, 'content.itemContent.tweet_results.result'),
        ];
    }


    /**
     * @param $query
     * @param $url
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendRequest($query = [], $url)
    {
        $json = (new Client())->get($this->host .'/'. $url, [
            'headers' => [
                'x-rapidapi-key' => $this->apiKey
            ],
            'verify' => false,
            'proxy' => 'http://127.0.0.1:8080',
            'query' => $query,

        ])->getBody()->getContents();

        return json_decode($json, true);
    }

    private function parseCursor(array $response): array
    {
        return Arr::get($response, 'cursor');
    }

    public function formatTweet(array $tweet) : array
    {
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


        return $tweet;
    }

    public function formatUser(array $user, array $tweet) : array
    {
        $user['user_id'] = Arr::get($tweet, 'user_id_str');

        return $user;
    }

    private function unpackUserAndTweer(array $item) : array
    {
        $tweet = Arr::get($item, 'legacy', Arr::get($item, 'tweet.legacy'));
        $user = Arr::get($item, 'core.user_results.result.legacy', Arr::get($item, 'tweet.core.user_results.result.legacy'));

        return [
            'tweet' => $tweet,
            'user' => $user
        ];
    }

    private function filterTweets(array $items, string $tweetRootPath): array
    {
        $tweets = [];

        foreach ($items as $originalItem) {

            $item = Arr::get($originalItem, $tweetRootPath);

            if ($item === null) {
                continue;
            }

            $unpack = $this->unpackUserAndTweer($item);

            $tweet = Arr::get($unpack, 'tweet');
            $user = Arr::get($unpack, 'user');


            if (Arr::get($tweet, 'user_id_str') === null) {
                print_r(json_encode($originalItem));
                DB::connection('mongodb')->table('unknown_tweets')->insert($originalItem);
                continue;
            }

            $tweet = $this->formatTweet($tweet);
            $user = $this->formatUser($user, $tweet);

            $tweets[] = [
                'tweet' => $tweet,
                'user' => $user,
            ];
        }

        return $tweets;
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
}
