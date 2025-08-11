<?php

namespace App\Policies;

use App\Models\Donation;
use App\Models\User;

class DonationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('donation.viewAny');
    }

    public function view(User $user, Donation $donation): bool
    {
        return $user->can('donation.view') || $donation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('donation.create');
    }
}


