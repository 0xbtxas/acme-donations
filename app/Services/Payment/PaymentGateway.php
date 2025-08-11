<?php

namespace App\Services\Payment;

use App\Models\Donation;

interface PaymentGateway
{
    /**
     * Dispatch external payment processing for the donation and return an id/reference if available.
     */
    public function processAsync(Donation $donation): void;
}


