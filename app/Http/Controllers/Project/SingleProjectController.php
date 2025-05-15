<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\ProjectResource;
use App\Repository\Campaign\CampaignRepositoryInterface;
use App\Repository\Project\ProjectRepositoryInterface;
use App\Service\Project\TopProjectTweetsService;
use function request;

class SingleProjectController extends Controller
{
    public function __invoke(TopProjectTweetsService $service, CampaignRepositoryInterface $campaignRepository, ProjectRepositoryInterface $projectRepository)
    {
        $projectId = request()->route('project_id');

        return response()->json([
            'status' => 'ok',
            'project' => new ProjectResource(request()->project),
            'campaigns' => CampaignResource::collection($campaignRepository->projectCampaigns($projectId)),
            'top_tweets' => $service->showTweets($projectId)
        ]);
    }
}
