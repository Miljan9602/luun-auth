<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $result = [
            'twitter_name' => $this->twitter_name,
            'twitter_username' => $this->twitter_username,
            'twitter_id' => $this->twitter_id,
            'type' => $this->type,
            'website' => $this->website,
            'socials' => json_decode($this->socials, true),
            'ticker' => $this->ticker,
            'logo_url' => $this->logo_url,
            'description' => $this->description,
            'campaigns_count' => $this->campaigns_count,
            'active_rewards_usd' => doubleval($this->active_rewards_usd),
            'rewards_distributed_usd' => doubleval($this->rewards_distributed_usd),
        ];

        switch ($this->type) {

            case 'ecosystem':
                $result['projects_count'] = $this->projects_count;
                break;

            case 'project':
                $result['ecosystem_id'] = $this->ecosystem_id;
                break;
        }

        return $result;
    }
}
