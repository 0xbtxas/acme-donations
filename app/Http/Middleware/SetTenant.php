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
        $headerId = $request->header('X-Tenant-ID');
        $headerSub = $request->header('X-Tenant');
        $tenant = null;

        if ($headerId && is_numeric($headerId)) {
            $tenant = Tenant::find((int) $headerId);
        }

        if (!$tenant) {
            $subdomain = $headerSub ?: null;
            if (!$subdomain) {
                $parts = explode('.', $host);
                if (count($parts) > 2) {
                    $subdomain = $parts[0];
                }
            }
            if ($subdomain) {
                $tenant = Tenant::where('subdomain', $subdomain)->first();
            }
        }

        if ($tenant) {
            app()->instance(Tenant::class, $tenant);
            config(['app.tenant_id' => $tenant->id]);
        }

        return $next($request);
    }
}


