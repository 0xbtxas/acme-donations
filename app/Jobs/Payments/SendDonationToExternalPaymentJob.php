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

        // Reload donation and related models without tenant scope to ensure availability in jobs
        $donation = Donation::withoutGlobalScopes()->with([
            'campaign' => function ($q) {
                $q->withoutGlobalScopes();
            },
            'user',
        ])->find($this->donation->id);

        if (!$donation || !$donation->campaign || !$donation->user) {
            $this->donation->update(['status' => 'failed']);
            return;
        }

        $payload = [
            'amount' => (float) $donation->amount,
            'currency' => $donation->currency,
            'donation_id' => $donation->id,
            'campaign' => $donation->campaign->title,
            'user_email' => $donation->user->email,
        ];

        // Placeholder external call. Replace BASE_URL via config.
        $response = Http::timeout(10)
            ->retry(2, 500)
            ->post(rtrim(config('services.payments.base_url'), '/') . '/donations', $payload);

        if ($response->successful()) {
            $donation->update([
                'external_reference' => (string) ($response->json('reference') ?? ''),
            ]);
        } else {
            $donation->update(['status' => 'failed']);
        }
    }
}


