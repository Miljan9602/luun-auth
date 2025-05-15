<?php

namespace App\Repository\CampaignTask;

use App\Models\Campaign;
use App\Models\CampaignTasks;
use Illuminate\Support\Collection;

class EloquentCampaignTaskRepository implements CampaignTaskRepositoryInterface
{
    public function campaignTaskByIdAndUser(string $campaignId, string $userId): ?CampaignTasks
    {
        return CampaignTasks::where('campaign_id', $campaignId)
            ->where('user_id', $userId)
            ->first();
    }

    public function campaignTasks(string $campaignId): Collection
    {
        return CampaignTasks::where('campaign_id', $campaignId)->get();
    }

    public function userTasks(string $userId): Collection
    {
        return CampaignTasks::where('user_id', $userId)->get();
    }

    public function create($data = []): CampaignTasks
    {
        return CampaignTasks::create($data);
    }

    public function update(CampaignTasks $task, $data = []): CampaignTasks
    {
        $task->update($data);

        return $task;
    }
}
