<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'twitter_id' => $this->getId(),
            'name' => $this->name,
            'created_at' => $this->created_at,
            'description' => $this->description,
            'twitter_username' => $this->twitter_username,
            'profile_image_url' => $this->profile_image_url,
        ];
    }
}
