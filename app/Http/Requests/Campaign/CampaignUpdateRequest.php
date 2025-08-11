<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Campaign;

class CampaignUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $campaign = $this->route('campaign');
        return $campaign instanceof Campaign
            ? ($this->user()?->can('update', $campaign) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'goal_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'required', 'string', 'size:3'],
            'status' => ['sometimes', 'in:draft,published,archived'],
        ];
    }
}


