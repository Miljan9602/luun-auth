<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Repository\Tweet\TweetRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProjectTrendingTweetsController extends Controller
{
    public function __invoke(TweetRepositoryInterface $repository)
    {
        $projectId = \request()->route('project_id');

        return response()->json([
            'status' => 'ok',
            'tweets' => $repository->getTweetsFromQuery('post', [
                ['user_id_str' => $projectId],
                ['created_at', '>=', Carbon::now()->subDays(25)],
                ['retweet_count', '>', 5],
                ['reply_count', '>', 5],
                ['bookmark_count', '>', 0],
                ['favorite_count' => 10]
            ])
        ]);
    }
}
