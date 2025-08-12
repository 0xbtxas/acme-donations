<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $slug = $request->header('X-Tenant') ?: $request->route('tenant') ?: null;

        if (!$slug) {
            // basic host-based mapping: {slug}.example.com
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $slug = $parts[0];
            }
        }

        if ($slug) {
            $tenant = Tenant::where('slug', $slug)->first();
            if ($tenant) {
                app()->instance(Tenant::class, $tenant);
                // scope models
                Tenant::unguarded(function () use ($tenant) {
                    config(['app.tenant_id' => $tenant->id]);
                });
            }
        }

        return $next($request);
    }
}


