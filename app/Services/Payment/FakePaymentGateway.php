<?php

namespace App\Services\Payment;

use App\Models\Donation;
use App\Notifications\DonationCompleted;

class FakePaymentGateway implements PaymentGateway
{
    public function processAsync(Donation $donation): void
    {
        if ($donation->status !== 'pending') {
            return;
        }

        $donation->update([
            'external_reference' => 'FAKE-' . $donation->id,
            'status' => 'pending',
        ]);
    }
}


