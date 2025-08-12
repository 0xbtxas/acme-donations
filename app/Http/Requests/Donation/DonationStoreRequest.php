<?php

namespace App\Http\Requests\Donation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DonationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('donation.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'integer', 'exists:campaigns,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'size:3'],
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('payment_methods', 'id')->where(fn($q) => $q->where('user_id', $this->user()?->id ?? 0)),
            ],
        ];
    }
}


