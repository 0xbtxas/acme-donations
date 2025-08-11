<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Campaign */
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
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'goal_amount' => (float) $this->goal_amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'owner' => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
            ],
            'donations_count' => $this->whenCounted('donations'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}


