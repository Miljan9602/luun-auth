<?php

namespace App\Repository\Campaign;

use App\Collection\CampaignCollection;
use App\Models\Campaign;

class EloquentCampaignRepository implements CampaignRepositoryInterface
{
    public function all(): CampaignCollection
    {
        return new CampaignCollection(Campaign::all());
    }

    public function campaignById(string $campaignId): ?Campaign
    {
        return Campaign::where('campaign_uuid', $campaignId)->first();
    }

    public function projectCampaigns(string $twitterProjectId): CampaignCollection
    {
        return new CampaignCollection(Campaign::where('project_twitter_id', request()->route('project_id'))
            ->orderBy('start_date', 'desc')
            ->get()
        );
    }
}
