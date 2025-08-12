<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Notifications\DonationCompleted;

class WebhookController extends Controller
{
    public function payments(Request $request): Response
    {
        $secret = (string) config('services.payments.webhook_secret', '');
        if ($secret !== '') {
            $signature = (string) $request->header('X-Signature', '');
            $computed = hash_hmac('sha256', $request->getContent(), $secret);
            if (!hash_equals($computed, $signature)) {
                return response(status: 401);
            }
        }

        $data = $request->validate([
            'donation_id' => ['required', 'integer'],
            'status' => ['required', 'in:confirmed,failed,refunded'],
            'reference' => ['nullable', 'string'],
        ]);

        $donation = Donation::withoutGlobalScope('tenant')->find($data['donation_id']);
        if (!$donation) {
            Log::warning('Webhook donation not found', $data);
            return response(status: 204);
        }

        $donation->update([
            'status' => $data['status'],
            'external_reference' => $data['reference'] ?? $donation->external_reference,
        ]);

        if ($data['status'] === 'confirmed') {
            $donation->user->notify(new DonationCompleted($donation->fresh(['campaign', 'user'])));
        }

        return response(status: 204);
    }
}


