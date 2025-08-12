<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'tenant_name' => ['nullable', 'string', 'max:255'],
            'tenant_subdomain' => ['nullable', 'string', 'max:255'],
        ]);

        $tenantId = null;
        if (!empty($validated['tenant_id'])) {
            $tenantId = (int) $validated['tenant_id'];
        } else {
            if (empty($validated['tenant_name']) || empty($validated['tenant_subdomain'])) {
                throw ValidationException::withMessages(['tenant' => 'Provide tenant_id to join or tenant_name + tenant_subdomain to create.']);
            }
            $tenant = Tenant::create([
                'name' => $validated['tenant_name'],
                'subdomain' => $validated['tenant_subdomain'],
                'status' => 'active',
            ]);
            $tenantId = $tenant->id;
        }

        if (User::where('tenant_id', $tenantId)->where('email', $validated['email'])->exists()) {
            throw ValidationException::withMessages(['email' => 'Email already taken for this tenant.']);
        }

        $user = User::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        // Load the user with tenant information
        $user->load('tenant');

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var User $user */
        $user = User::with('tenant')->where('email', $credentials['email'])->firstOrFail();
        $token = $user->createToken('auth')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->noContent();
    }

    public function me(Request $request)
    {
        return $request->user();
    }
}


