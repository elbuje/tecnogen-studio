<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CreditLedger;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const PLAN_INITIAL_CREDITS = [
        'starter' => 75,
        'growth' => 220,
        'agency' => 750,
    ];

    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'full_name' => 'nullable|string|max:100',
            'plan_tier' => 'nullable|string',
        ]);

        $plan = $validated['plan_tier'] ?? 'growth';
        $credits = self::PLAN_INITIAL_CREDITS[$plan] ?? 220;

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'full_name' => $validated['full_name'] ?? null,
            'role' => 'client',
            'plan_tier' => $plan,
            'credits_balance' => $credits,
            'commercial_status' => 'active',
            'monthly_video_limit' => 30,
            'videos_generated_this_month' => 0,
            'avatar_minutes_quota' => 60,
            'avatar_minutes_used' => 0,
            'auto_mode_enabled' => false,
            'sheet_auto_mode' => 'copilot',
        ]);

        $brandName = ($validated['full_name'] ?? 'Mi') . ' Marca';
        Brand::create([
            'user_id' => $user->id,
            'name' => $brandName,
            'created_at' => now(),
        ]);

        CreditLedger::create([
            'user_id' => $user->id,
            'amount' => $credits,
            'action_type' => 'initial_signup',
            'description' => 'Créditos iniciales Plan ' . ucfirst($plan),
            'created_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $token,
            'token_type' => 'bearer',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'role' => $user->role,
                'plan_tier' => $user->plan_tier,
                'credits_balance' => $user->credits_balance,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['detail' => 'Credenciales incorrectas'], 401);
        }

        $isValid = Hash::check($validated['password'], $user->password) || password_verify($validated['password'], $user->password);
        if (!$isValid) {
            return response()->json(['detail' => 'Credenciales incorrectas'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $token,
            'token_type' => 'bearer',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'role' => $user->role,
                'plan_tier' => $user->plan_tier,
                'credits_balance' => $user->credits_balance,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $brand = Brand::where('user_id', $user->id)->first();

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'role' => $user->role,
            'plan_tier' => $user->plan_tier,
            'credits_balance' => $user->credits_balance,
            'commercial_status' => $user->commercial_status,
            'plan_name' => $user->plan_name,
            'brand_name' => $brand ? $brand->name : null,
        ]);
    }
}
