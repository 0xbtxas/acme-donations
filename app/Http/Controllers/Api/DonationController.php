<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Donation\DonationStoreRequest;
use App\Http\Resources\DonationResource;
use App\Models\Donation;
use App\Services\Payment\PaymentGateway;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function __construct(private readonly PaymentGateway $payments)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Donation::class);
        $query = Donation::query()->with(['campaign', 'user']);
        return DonationResource::collection($query->orderByDesc('id')->paginate(15));
    }

    public function store(DonationStoreRequest $request)
    {
        $donation = Donation::create([
            'user_id' => $request->user()->id,
            'campaign_id' => (int) $request->validated('campaign_id'),
            'amount' => (float) $request->validated('amount'),
            'currency' => $request->validated('currency'),
            'payment_method_id' => $request->validated('payment_method_id') ? (int) $request->validated('payment_method_id') : null,
        ]);

        $this->payments->processAsync($donation);

        // In synchronous queues or fake gateways, processing may have completed; refresh to reflect final status
        $donation->refresh();

        return (new DonationResource($donation->load(['campaign', 'user'])))->response()->setStatusCode(201);
    }

    public function show(Donation $donation)
    {
        $this->authorize('view', $donation);
        return new DonationResource($donation->load(['campaign', 'user']));
    }
}


