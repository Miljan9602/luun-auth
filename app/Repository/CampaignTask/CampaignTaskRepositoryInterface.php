<?php

namespace App\Repository\CampaignTask;

use App\Models\Campaign;
use App\Models\CampaignTasks;
use Illuminate\Support\Collection;

interface CampaignTaskRepositoryInterface
{
    /**
     * @param string $campaignId
     * @param string $userId
     * @return CampaignTasks|null
     */
    public function campaignTaskByIdAndUser(string $campaignId, string $userId): ?CampaignTasks;

    /**
     * @param string $campaignId
     * @return Collection
     */
    public function campaignTasks(string $campaignId) : Collection;

    /**
     * @param string $userId
     * @return Collection
     */
    public function userTasks(string $userId) : Collection;

    /**
     * @param $data
     * @return CampaignTasks
     */
    public function create($data = []): CampaignTasks;

    /**
     * @param Campaign $task
     * @param $data
     * @return CampaignTasks
     */
    public function update(CampaignTasks $task, $data = []) : CampaignTasks;
}
