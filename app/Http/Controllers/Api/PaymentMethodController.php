<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request): JsonResponse
    {
        $items = PaymentMethod::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->get();

        return response()->json(['data' => $items]);
    }

    /**
     * Create a setup intent for adding a payment method
     */
    public function createSetupIntent(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->paymentService->createSetupIntent($user);

        if (!$result['success']) {
            return response()->json([
                'error' => $result['error']
            ], 400);
        }

        return response()->json([
            'client_secret' => $result['client_secret'],
            'setup_intent_id' => $result['setup_intent_id'],
        ]);
    }

    /**
     * Store a payment method after setup intent confirmation
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string'],
            'payment_method_id' => ['required', 'string'], // From payment provider
            'label' => ['nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
        ]);

        // Retrieve payment method details from provider
        $providerResult = $this->paymentService->retrievePaymentMethod($validated['payment_method_id']);

        if (!$providerResult['success']) {
            return response()->json([
                'error' => 'Failed to retrieve payment method details'
            ], 400);
        }

        $providerData = $providerResult['data'];

        // Create the payment method
        $paymentMethod = PaymentMethod::create([
            'user_id' => $request->user()->id,
            'tenant_id' => config('app.tenant_id'),
            'provider' => $validated['provider'],
            'provider_payment_method_id' => $validated['payment_method_id'],
            'label' => $validated['label'] ?: $this->generateDefaultLabel($providerData),
            'brand' => $providerData['brand'],
            'last4' => $providerData['last4'],
            'exp_month' => $providerData['exp_month'],
            'exp_year' => $providerData['exp_year'],
            'status' => 'active',
            'is_default' => $validated['is_default'] ?? false,
        ]);

        // If this is set as default, unset others
        if ($paymentMethod->is_default) {
            PaymentMethod::where('user_id', $request->user()->id)
                ->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);
        }

        return response()->json(['data' => $paymentMethod], 201);
    }

    /**
     * Generate a default label for the payment method
     */
    protected function generateDefaultLabel(array $providerData): string
    {
        $brand = $providerData['brand'] ?? 'Card';
        $last4 = $providerData['last4'] ?? '';

        if ($last4) {
            return "{$brand} •••• {$last4}";
        }

        return $brand;
    }
}


