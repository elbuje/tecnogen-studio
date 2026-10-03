<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\AISettingController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\IntegrationController;
use App\Http\Controllers\Api\SystemLogController;
use App\Models\Brand;
use App\Models\CreditLedger;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// Public Auth Endpoints
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', function (Request $request) {
        try {
            $validated = $request->validate([
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'full_name' => 'nullable|string|max:100',
                'plan_tier' => 'nullable|string',
            ]);

            $plan = $validated['plan_tier'] ?? 'growth';
            $credits = 220;
            if ($plan === 'starter') $credits = 75;
            if ($plan === 'agency') $credits = 750;

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
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    });

    Route::post('/login', [AuthController::class, 'login']);
});

// Public Content Viewer
Route::get('v1/contents/public/{id}', [ContentController::class, 'publicShow']);

// Diagnostics
Route::match(['get', 'post'], 'v1/diagnostic', fn() => response()->json(['status' => 'ok', 'framework' => 'Laravel 11', 'db' => 'MySQL (tecnogen_studio)']));
Route::post('v1/diagnostic-text', fn() => response()->json(['saved' => true]));

// Protected API Routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Brands
    Route::get('/brands', [BrandController::class, 'index']);
    Route::post('/brands', [BrandController::class, 'store']);
    Route::get('/brands/{id}', [BrandController::class, 'show']);
    Route::put('/brands/{id}', [BrandController::class, 'update']);
    Route::post('/brands/{id}/assets', [BrandController::class, 'addAsset']);
    Route::delete('/brands/{id}/assets/{assetId}', [BrandController::class, 'deleteAsset']);

    // Contents & Carousels
    Route::get('/contents', [ContentController::class, 'index']);
    Route::post('/contents', [ContentController::class, 'store']);
    Route::get('/contents/{id}', [ContentController::class, 'show']);
    Route::patch('/contents/{id}/status', [ContentController::class, 'updateStatus']);
    Route::put('/contents/{contentId}/slides/{slideId}', [ContentController::class, 'updateSlide']);

    // AI Settings
    Route::get('/settings/ai', [AISettingController::class, 'index']);
    Route::post('/settings/ai', [AISettingController::class, 'store']);
    Route::post('/settings/ai/fetch-models', [AISettingController::class, 'fetchModels']);

    // Billing & Credits
    Route::get('/billing/balance', [BillingController::class, 'getBalance']);

    // Integrations
    Route::get('/integrations', [IntegrationController::class, 'getStatus']);
    Route::post('/integrations/drive', [IntegrationController::class, 'connectDrive']);
    Route::post('/integrations/sheets', [IntegrationController::class, 'connectSheets']);
    Route::post('/integrations/metricool', [IntegrationController::class, 'connectMetricool']);

    // System Logs
    Route::get('/system/logs', [SystemLogController::class, 'getLogs']);
});
