<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $query = Tenant::query()->select(['id', 'name', 'subdomain', 'status'])->orderBy('name');
        if ($q !== '') {
            $query->where('name', 'like', "%$q%")
                ->orWhere('subdomain', 'like', "%$q%");
        }
        return response()->json(['data' => $query->limit(25)->get()]);
    }
}


