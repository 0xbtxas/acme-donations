<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DonationsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_auth_to_donate(): void
    {
        $this->postJson('/api/donations', [])->assertUnauthorized();
    }

    public function test_employee_can_donate_to_a_campaign(): void
    {
        $employee = User::where('email', 'employee@example.com')->firstOrFail();
        Sanctum::actingAs($employee);
        $campaign = Campaign::factory()->for($employee, 'owner')->create();
        $this->postJson('/api/donations', [
            'campaign_id' => $campaign->id,
            'amount' => 10,
            'currency' => 'USD',
        ])->assertCreated()->assertJsonStructure(['data' => ['id', 'status']]);
    }
}
