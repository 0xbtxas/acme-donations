<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payout_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('provider');
            $table->string('provider_account_id');
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'provider', 'provider_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_accounts');
    }
};


