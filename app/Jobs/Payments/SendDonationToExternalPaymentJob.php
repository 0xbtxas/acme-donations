<?php

namespace App\Jobs\Payments;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Notifications\DonationCompleted;

class SendDonationToExternalPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Donation $donation)
    {
        $this->onQueue('payments');
    }

    public function handle(): void
    {
        if ($this->donation->status !== 'pending') {
            return;
        }

        $payload = [
            'amount' => (float) $this->donation->amount,
            'currency' => $this->donation->currency,
            'donation_id' => $this->donation->id,
            'campaign' => $this->donation->campaign->title,
            'user_email' => $this->donation->user->email,
        ];

        // Placeholder external call. Replace BASE_URL via config.
        $response = Http::timeout(10)->post(config('services.payments.base_url') . '/donations', $payload);

        if ($response->successful()) {
            $this->donation->update([
                'status' => 'completed',
                'external_reference' => (string) ($response->json('reference') ?? ''),
            ]);
            $this->donation->user->notify(new DonationCompleted($this->donation->fresh(['campaign', 'user'])));
        } else {
            $this->donation->update(['status' => 'failed']);
        }
    }
}


