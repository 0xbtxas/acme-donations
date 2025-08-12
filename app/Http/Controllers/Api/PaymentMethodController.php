<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $items = PaymentMethod::query()->where('user_id', $request->user()->id)->orderByDesc('is_default')->get();
        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider' => ['required', 'string'],
            'payment_method_token' => ['required', 'string'],
            'label' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:50'],
            'last4' => ['nullable', 'digits:4'],
            'exp_month' => ['nullable', 'integer', 'between:1,12'],
            'exp_year' => ['nullable', 'integer', 'min:2000'],
            'is_default' => ['boolean'],
        ]);
        $item = PaymentMethod::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'tenant_id' => config('app.tenant_id'),
        ]);
        if (!empty($validated['is_default'])) {
            PaymentMethod::where('user_id', $request->user()->id)->where('id', '!=', $item->id)->update(['is_default' => false]);
        }
        return response()->json(['data' => $item], 201);
    }
}


