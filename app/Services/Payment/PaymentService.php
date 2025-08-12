<?php

namespace App\Services\Payment;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('services.stripe');
    }

    /**
     * Create a setup intent for adding a payment method
     */
    public function createSetupIntent(User $user): array
    {
        try {
            // Make real Stripe API call to create setup intent
            $response = Http::withOptions([
                'verify' => config('app.env') === 'local' ? false : true, // Skip SSL verification in local dev
            ])->withHeaders([
                        'Authorization' => 'Bearer ' . $this->config['secret'],
                        'Stripe-Version' => '2023-10-16',
                    ])->post('https://api.stripe.com/v1/setup_intents', [
                        'customer' => $this->getOrCreateCustomer($user),
                        'payment_method_types' => ['card'],
                        'usage' => 'off_session',
                    ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'client_secret' => $response->json('client_secret'),
                    'setup_intent_id' => $response->json('id'),
                ];
            }

            Log::error('Failed to create setup intent', [
                'response' => $response->json(),
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create setup intent',
            ];
        } catch (\Exception $e) {
            Log::error('Exception creating setup intent', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'error' => 'Service temporarily unavailable',
            ];
        }
    }

    /**
     * Retrieve payment method details from Stripe
     */
    public function retrievePaymentMethod(string $paymentMethodId): array
    {
        try {
            $response = Http::withOptions([
                'verify' => config('app.env') === 'local' ? false : true, // Skip SSL verification in local dev
            ])->withHeaders([
                        'Authorization' => 'Bearer ' . $this->config['secret'],
                        'Stripe-Version' => '2023-10-16',
                    ])->get("https://api.stripe.com/v1/payment_methods/{$paymentMethodId}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => [
                        'brand' => $data['card']['brand'] ?? null,
                        'last4' => $data['card']['last4'] ?? null,
                        'exp_month' => $data['card']['exp_month'] ?? null,
                        'exp_year' => $data['card']['exp_year'] ?? null,
                        'type' => $data['type'] ?? null,
                    ],
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to retrieve payment method',
            ];
        } catch (\Exception $e) {
            Log::error('Exception retrieving payment method', [
                'error' => $e->getMessage(),
                'payment_method_id' => $paymentMethodId,
            ]);

            return [
                'success' => false,
                'error' => 'Service temporarily unavailable',
            ];
        }
    }

    /**
     * Get or create a customer for the user in Stripe
     */
    protected function getOrCreateCustomer(User $user): string
    {
        // In production, you would store the customer ID in the user model
        // For now, we'll create a new customer each time
        try {
            $response = Http::withOptions([
                'verify' => config('app.env') === 'local' ? false : true, // Skip SSL verification in local dev
            ])->withHeaders([
                        'Authorization' => 'Bearer ' . $this->config['secret'],
                        'Stripe-Version' => '2023-10-16',
                    ])->post('https://api.stripe.com/v1/customers', [
                        'email' => $user->email,
                        'name' => $user->name,
                        'metadata' => [
                            'user_id' => $user->id,
                            'tenant_id' => $user->tenant_id,
                        ],
                    ]);

            if ($response->successful()) {
                return $response->json('id');
            }
        } catch (\Exception $e) {
            Log::error('Failed to create customer', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }

        // Return a fallback customer ID for development
        return 'cus_fallback_' . $user->id;
    }
}
