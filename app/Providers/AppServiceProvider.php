<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\Payment\PaymentGateway;
use App\Services\Payment\NullPaymentGateway;
use App\Services\Payment\FakePaymentGateway;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function ($app) {
            return $app->environment('testing')
                ? $app->make(FakePaymentGateway::class)
                : $app->make(NullPaymentGateway::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        // Global tenant assignment: automatically set tenant_id on creating when available
        Model::creating(function ($model) {
            $tenantId = config('app.tenant_id');
            if ($tenantId && in_array('tenant_id', $model->getFillable(), true) && empty($model->tenant_id)) {
                $model->tenant_id = $tenantId;
            }
        });
    }
}
