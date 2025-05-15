<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Repository\Campaign\CampaignRepositoryInterface;

class AllCampaignsController extends Controller
{
    public function __invoke(CampaignRepositoryInterface $campaignRepository)
    {
        return response()->json([
            'status' => 'ok',
            'campaigns' => CampaignResource::collection($campaignRepository->all())
        ]);
    }
}
