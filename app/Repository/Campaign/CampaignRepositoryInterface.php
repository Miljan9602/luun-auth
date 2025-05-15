<?php

namespace App\Repository\Campaign;

use App\Collection\CampaignCollection;
use App\Models\Campaign;

interface CampaignRepositoryInterface
{
    /**
     * @return CampaignCollection
     */
    public function all(): CampaignCollection;

    /**
     * @return Campaign|null
     */
    public function campaignById(string $campaignId): ?Campaign;

    /**
     * @param string $twitterProjectId
     * @return CampaignCollection
     */
    public function projectCampaigns(string $twitterProjectId): CampaignCollection;
}
