<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Service\Project\TopProjectTweetsService;
use function request;

class ProjectTopTweetsController extends Controller
{
    public function __invoke(TopProjectTweetsService $service)
    {
        $projectId = request()->route('project_id');

        return response()->json([
            'status' => 'ok',
            'top_tweets' => $service->showTweets($projectId)
        ]);
    }
}
