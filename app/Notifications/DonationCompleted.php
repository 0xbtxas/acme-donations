<?php

namespace App\Notifications;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonationCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Donation $donation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Donation Confirmation')
            ->line('Thank you for your donation!')
            ->line('Amount: ' . $this->donation->amount . ' ' . $this->donation->currency)
            ->line('Campaign: ' . $this->donation->campaign->title)
            ->line('Reference: ' . $this->donation->external_reference);
    }
}


