<?php

namespace App\Console\Commands\Script;

use App\Repository\Tweet\TweetRepositoryInterface;
use App\Repository\XUser\XUserRepositoryInterface;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\EnumeratesValues;

class Test extends Command
{

    protected TweetRepositoryInterface $tweetRepository;

    protected XUserRepositoryInterface $xUserRepository;



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

    protected function jobs()
    {
        // 'Customer support',

        $jobs = ['tracking'];
        $loop = 5;

        foreach ($jobs as $job) {

            for ($i=1; $i<=$loop; $i++) {

                $json = (new Client())->get('https://jsearch.p.rapidapi.com/search', [
                    'query' => [
                        'query' => $job,
                        'num_pages' => 20,
                        'date_posted' => 'all',
                        'page' => $i
                    ],
                    'headers' => [
                        'x-rapidapi-key' => 'c001e66c84msh9059c9f0f4db38bp17f8d4jsnb3c0915dcb71'
                    ]
                ])->getBody()->getContents();

                $decoded = json_decode($json, true);

                $items = Arr::get($decoded, 'data');

                foreach ($items as $item) {
                    $jobId = Arr::get($item, 'job_id');
                    DB::connection('mongodb')->table('jobs_'.$job)->updateOrInsert(['job_id' => $jobId], $item);
                }

                echo "Page $i for job $job processed.\n";
            }
        }

        dd("Asd");
    }

    /**
     * Execute the console command.
     */
    public function handle(TweetRepositoryInterface $tweetRepository, XUserRepositoryInterface $userRepository)
    {

//        $this->jobs();
//        return;

        $collection = 'post';
        $profileName = '@dragonswap_dex';
        $profileTicker = '$DSWAP';

        while (true) {

            $tweets = DB::connection('mongodb')->table($collection)
                ->whereIn('search_key', [$profileName, $profileTicker])
                ->whereNull('ai_score.open_ai_new')
                ->where('ticker_mentions_count', '<', 3)
                ->get()
                ->filter(function ($item) {
                    $createdAt = Carbon::create($item->created_at);
                    $updatedAt = Carbon::create($item->updated_at);

                    // Min 4 hours needs to pass.
                    return $createdAt->diffInHours($updatedAt) >= 4;
                });

            $total = sizeof($tweets);
            $current = 0;

            foreach ($tweets as $tweet) {
                $current++;

                $tweetId = $tweet->id_str;

                $tweet = $tweetRepository->getTweetById($collection, $tweetId);
                $user = $userRepository->getUserById(Arr::get($tweet, 'user_id_str'));

                $result = intval(Arr::get($this->getTweetScore($tweet, $user), 'open_ai_score'));

                DB::connection('mongodb')->table($collection)
                    ->where('id_str', $tweetId)
                    ->update([
                        'ai_score.open_ai_new' => $result
                    ]);

                echo "Tweet ID: $tweetId => User ID: {$tweet['user_id_str']} => CreatedAt => " . Arr::get($tweet, 'created_at')->toDateTimeString() . " => Scored: $result. => $current/$total" .PHP_EOL;
            }

            if (empty($tweets)) break;
        }
    }


    private function getTweetScore(array $tweet, array $user)
    {
        $systemMessage = 'You are an expert evaluating social media content quality using a multi-dimensional approach. Analyze the following tweet across three primary dimensions:

1. CONTENT QUALITY (40 points max):
   - Accuracy (15 points): Factual correctness and depth
   - Originality (15 points): Uniqueness of perspective and phrasing
   - Relevance (10 points): Connection to the specified project/topic

2. ENGAGEMENT QUALITY (30 points max):
   - Influencer Engagement (15 points): Has it attracted high-quality engagement?
   - Engagement Ratio (15 points): Is engagement proportional to author\'s reach?

3. VALUE CONTRIBUTION (30 points max):
   - Information Value (15 points): Does it provide useful insights?
   - Community Benefit (15 points): Does it educate or resource the community?

CRITICAL: Apply anti-gaming penalties for:
- Repetitive keyword use (-30 points)
- Engagement baiting (-40 points)
- Generic statements without insight (-25 points)
- Low-effort responses (-20 points)

Score must be an integer between 1-100 representing the sum of all dimension scores minus any penalties.';

//        $outputMessage = 'Evaluate the tweet based on the scoring criteria and provide a score from 1 to 100. Give me how much each weight got points.!';
        $outputMessage = 'Evaluate the tweet based on the scoring criteria and provide a score from 1 to 100. Output just needs to be final score. Nothing else. So integer, not containing any other text.';

        $prepared = $this->prepareTweetForScoring($tweet, $user);

        $textPrompt = Arr::get($prepared, 'textPrompt');
        $imagePrompt = Arr::get($prepared, 'imagePrompt');

        return $this->ai($systemMessage,$outputMessage, $textPrompt, $imagePrompt);
    }

    function ai(string $systemMessage, string $outputMessage, string $tweetData, array $imagePrompt = null)
    {
        return [
            'open_ai_score' => $this->openAi($systemMessage, $outputMessage, $tweetData, $imagePrompt),
//            'grok_ai' => $this->grok($systemMessage, $outputMessage, $tweetData, $imagePrompt),
//            'deepseek_ai' => $this->deepseek($systemMessage, $outputMessage, $tweetData, $imagePrompt),
        ];
    }

    private function grok(string $systemMessage, string $outputMessage, string $tweetData, array $imagePrompt = null)
    {
        $apiKey = env('GROK_KEY');
// Compose full prompt text for the user message
        $userMessage = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $tweetData,
                ]
            ]
        ];

        // Add image URL in a structured format if available
        if ($imagePrompt && isset($imagePrompt[1]['image_url']['url'])) {
            $imageUrl = $imagePrompt[1]['image_url']['url'];
            $userMessage['content'][] = [
                'type' => 'image_url',
                'image_url' => $imageUrl,
            ];
        }

        // Construct the messages array with system, user, and assistant roles
        $messages = [
            [
                'role' => 'system',
                'content' => $systemMessage,
            ],
            $userMessage
        ];

        // Prepare data for the chat completions endpoint
        $requestData = [
            'model' => 'grok-2-vision-1212',
            'messages' => $messages,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ])->post('https://api.x.ai/v1/chat/completions', $requestData);

            dd($response->status());

            if ($response->successful()) {

                return Arr::get(Arr::first(Arr::get(Arr::first(Arr::get($response->json(), 'output')), 'content')), 'text');
            } else {
                return 'Error: ' . $response->body();
            }
        } catch (\Exception $e) {
            return 'Request failed: ' . $e->getMessage();
        }
    }

    private function deepseek(string $systemMessage, string $outputMessage, string $tweetData, array $imagePrompt = null)
    {
        // Compose full prompt text, including image URL if available (avoid embedding huge base64)
        $fullText = $tweetData;

        if ($imagePrompt && isset($imagePrompt[1]['image_url']['url'])) {
            $imageUrl = $imagePrompt[1]['image_url']['url'];
            // Append a note about the image (or any description you'd like)
            $fullText .= "\n\nNote: This tweet contains an associated image at: $imageUrl";
        }


        $messages = [
            [
                'role' => 'system',
                'content' => $systemMessage,
            ],
            [
                'role' => 'user',
                'content' => $fullText,
            ],
            [
                'role' => 'assistant',
                'content' => $outputMessage,
            ]
        ];

        $apiKey = env('DEEPSEEK_AI_KEY');

        // Prepare data for the chat completions endpoint
        $requestData = [
            'model' => 'deepseek-chat',
            'messages' => $messages,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ])->post('https://api.deepseek.com/chat/completions', $requestData);


            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            } else {
                return 'Error: ' . $response->body();
            }
        } catch (\Exception $e) {
            return 'Request failed: ' . $e->getMessage();
        }
    }

    private function openAi(string $systemMessage, string $outputMessage, string $tweetData, array $imagePrompt = null)
    {
        // Compose full prompt text, including image URL if available (avoid embedding huge base64)
        $fullText = $tweetData;
        $imageUrl = null;

        if ($imagePrompt && isset($imagePrompt[1]['image_url']['url'])) {
            $imageUrl = $imagePrompt[1]['image_url']['url'];
            // Append a note about the image (or any description you'd like)
            $fullText .= "\n\nNote: This tweet contains an associated image at: $imageUrl";
        }

        $tweetContent[] = [
            'type' => 'input_text',
            'text' => $fullText,
        ];

        if ($imageUrl) {
            $tweetContent[] = [
                'type' => 'input_image',
                'image_url' => $imageUrl
            ];
        }

        $messages = [
            [
                'role' => 'system',
                'content' => [[
                    'type' => 'input_text',
                    'text' => $systemMessage
                ]],
            ],
            [
                'role' => 'user',
                'content' => $tweetContent
            ],
            [
                'role' => 'assistant',
                'content' => [[
                    'type' => 'output_text',
                    'text' => $outputMessage
                ]],
            ]
        ];

        $apiKey = env('OPEN_AI_KEY');

        // Prepare data for the chat completions endpoint
        $requestData = [
            'model' => 'gpt-4.1',
            'input' => $messages,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/responses', $requestData);

            if ($response->successful()) {

                return Arr::get(Arr::first(Arr::get(Arr::first(Arr::get($response->json(), 'output')), 'content')), 'text');
            } else {
                return 'Error: ' . $response->body();
            }
        } catch (\Exception $e) {
            return 'Request failed: ' . $e->getMessage();
        }
    }

    function prepareTweetForScoring(array $tweet, array $user): array
    {
        // 1. Extract tweet basics
        $text         = $tweet['full_text'] ?? '';
        $createdAtRaw = $tweet['created_at'] ?? null;
        $tickerMentions = implode(', ', $tweet['tickers'] ?? []);
        $url = $tweet['entities']['urls'][0]['expanded_url'] ?? null;

        // 2. Format created_at safely
        $createdAt = $createdAtRaw instanceof \Carbon\Carbon
            ? $createdAtRaw->toDateTimeString()
            : (is_array($createdAtRaw) && isset($createdAtRaw['$date']) ? $createdAtRaw['$date'] : $createdAtRaw);

        $media = $tweet['extended_entities']['media'] ?? [];

        // 3. Extract image (if any)
        $imageUrl = null;

        if (!empty($media) && Arr::get(Arr::first($media), 'type') === 'photo') {
            $imageUrl = Arr::get(Arr::first($media), 'media_url_https');
        }

        // 4. Tweet metrics
        $tweetMetrics = [
            'Likes'       => $tweet['favorite_count'] ?? 0,
            'Replies'     => $tweet['reply_count'] ?? 0,
            'Retweets'    => $tweet['retweet_count'] ?? 0,
            'Quotes'      => $tweet['quote_count'] ?? 0,
            'Bookmarks'   => $tweet['bookmark_count'] ?? 0,
            'Language'    => $tweet['lang'] ?? 'N/A',
            'Ticker Tags' => $tickerMentions,
            'Link'        => $url ?? 'N/A',
            'Date Posted' => $createdAt ?? 'Unknown',
        ];

        // 5. User metrics
        $userMetrics = [
            'Username'      => '@' . ($user['screen_name'] ?? ''),
            'Display Name'  => $user['name'] ?? '',
            'Bio'           => $user['description'] ?? '',
            'Location'      => $user['location'] ?? 'N/A',
            'Verified'      => $user['verified'] ? 'Yes' : 'No',
            'Profile Created' => $user['created_at'] ?? '',
            'Followers'     => $user['followers_count'] ?? 0,
            'Following'     => $user['friends_count'] ?? 0,
            'Total Tweets'  => $user['statuses_count'] ?? 0,
            'Likes Given'   => $user['favourites_count'] ?? 0,
            'Listed Count'  => $user['listed_count'] ?? 0,
        ];

        $tweetBlock = collect($tweetMetrics)
            ->map(fn($v, $k) => "$k: $v")
            ->implode("\n");

        $userBlock = collect($userMetrics)
            ->map(fn($v, $k) => "$k: $v")
            ->implode("\n");

        // 6. Download image and encode
        $imageBase64 = null;
        if ($imageUrl) {
            try {
                $imageResponse = Http::timeout(10)->get($imageUrl);
                if ($imageResponse->ok()) {
                    $imageBase64 = 'data:image/jpeg;base64,' . base64_encode($imageResponse->body());
                }
            } catch (\Exception $e) {
                // Skip image if error occurs
            }
        }

        // 7. Build final prompts
        $textPrompt = <<<EOT

Tweet Content:
$text

Tweet Metrics:
$tweetBlock

User Profile:
$userBlock

EOT;

        $imagePrompt = [
            ['type' => 'text', 'text' => $textPrompt]
        ];

        if ($imageBase64) {
            $imagePrompt[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $imageUrl
                ]
            ];
        }

        return [
            'textPrompt'  => $textPrompt,
            'imagePrompt' => $imagePrompt,
            'imageFound'  => $imagePrompt !== null
        ];
    }
}
