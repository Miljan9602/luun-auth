<?php

namespace App\Action\Campaign;

use App\Models\Campaign;
use App\Models\User;
use App\Repository\CampaignTask\CampaignTaskRepositoryInterface;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class VerifyTaskAction
{
    protected CampaignTaskRepositoryInterface $campaignTaskRepository;

    /**
     * @param CampaignTaskRepositoryInterface $campaignTaskRepository
     */
    public function __construct(CampaignTaskRepositoryInterface $campaignTaskRepository)
    {
        $this->campaignTaskRepository = $campaignTaskRepository;
    }

    public function handle(Campaign $campaign, User $user) : bool
    {

        if ($user->evm_wallet === null) return false;

        $task = $this->campaignTaskRepository->campaignTaskByIdAndUser($campaign->campaign_uuid, $user->getId());

        if ($task !== null && $task->is_completed === true) {
            return true;
        } else {
            $task = $this->campaignTaskRepository->create([
                'campaign_id' => $campaign->campaign_uuid,
                'user_address' => $user->evm_wallet,
                'user_id' => $user->getId(),
            ]);
        }

        try {

            $json = (new Client())->get($campaign->resolve_url, [
                'query' => [
                    'address' => $user->evm_wallet,
                ],
                'headers' => [
                    'Authorization' => 'Bearer '.$this->decodeAuthToken($campaign)
                ]
            ])->getBody()->getContents();

            $decoded = json_decode($json, true);

            $result = Arr::get($decoded, 'data');

            if ($result === true) {
                $this->campaignTaskRepository->update($task, [
                    'is_completed' => true,
                    'completed_at' => Carbon::now(),
                ]);
            }

            return $result;

        }catch (\Exception $e) {
            return false;
        }
    }

    private function decodeAuthToken(Campaign $campaign) : ?string
    {
        if ($campaign->authorization_token) {
            return Crypt::decryptString($campaign->authorization_token);
        }

        return null;
    }
}
