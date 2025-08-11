<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        // Any authenticated user can browse campaigns
        return true;
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $user->can('campaign.view') || $campaign->status === 'published' || $campaign->owner_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('campaign.create');
    }

    public function update(User $user, Campaign $campaign): bool
    {
        // Owner can always update; otherwise explicit permission required
        return $campaign->owner_id === $user->id || $user->can('campaign.update');
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        // Owner can delete draft campaigns; admins or privileged users can delete
        return ($campaign->owner_id === $user->id && $campaign->status !== 'published')
            || $user->can('campaign.delete');
    }

    public function publish(User $user, Campaign $campaign): bool
    {
        return $user->can('campaign.publish');
    }
}


