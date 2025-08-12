<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain')->nullable();
            $table->string('status')->default('active');
            $table->string('plan')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique('subdomain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};


