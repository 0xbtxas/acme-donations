<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CampaignsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_campaigns_requires_auth(): void
    {
        $this->getJson('/api/campaigns')->assertUnauthorized();
    }

    public function test_allows_admin_to_list_campaigns(): void
    {
        $user = User::where('email', 'admin@example.com')->firstOrFail();
        Sanctum::actingAs($user);
        $this->getJson('/api/campaigns')->assertOk();
    }
}
