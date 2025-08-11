<?php

namespace App\Services\Payment;

use App\Jobs\Payments\SendDonationToExternalPaymentJob;
use App\Models\Donation;

class NullPaymentGateway implements PaymentGateway
{
    public function processAsync(Donation $donation): void
    {
        SendDonationToExternalPaymentJob::dispatch($donation);
    }
}


