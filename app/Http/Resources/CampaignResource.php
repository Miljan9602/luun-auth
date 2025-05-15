<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->campaign_uuid,
            'campaign_name' => $this->campaign_name,
            'description' => $this->description,
            'start_date' => $this->start_date->timestamp,
            'end_date' => $this->end_date->timestamp,
            'reward_usd' => floatval($this->reward_usd)
        ];
    }
}
