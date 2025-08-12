<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;

class Donation extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'campaign_id',
        'donor_id',
        'payment_method_id',
        'amount',
        'amount_cents',
        'currency',
        'status',
        'external_reference',
        'payment_provider',
        'payment_provider_session_id',
        'provider_transaction_id',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_cents' => 'integer',
        'refunded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}


