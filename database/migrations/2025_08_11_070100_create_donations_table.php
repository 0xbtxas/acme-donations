<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->foreignId('donor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->bigInteger('amount_cents')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'confirmed', 'failed', 'refunded'])->default('pending');
            $table->string('payment_provider')->nullable();
            $table->string('payment_provider_session_id')->nullable()->unique();
            $table->string('provider_transaction_id')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'campaign_id']);
            $table->index(['tenant_id', 'donor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};