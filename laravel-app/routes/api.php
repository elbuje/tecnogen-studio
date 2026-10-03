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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// Public Auth Endpoints
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', function (Request $request) {
        $email = $request->input('email');
        $password = $request->input('password');
        $fullName = $request->input('full_name');
        $plan = $request->input('plan_tier', 'growth');

        if (empty($email) || empty($password)) {
            return response()->json(['detail' => 'Email y contraseña requeridos'], 400);
        }

        $existing = User::where('email', $email)->first();
        if ($existing) {
            return response()->json(['detail' => 'El correo electrónico ya está registrado'], 400);
        }

        $credits = 220;
        if ($plan === 'starter') $credits = 75;
        if ($plan === 'agency') $credits = 750;

        $userId = (string) \Illuminate\Support\Str::uuid();
        $brandId = (string) \Illuminate\Support\Str::uuid();
        $ledgerId = (string) \Illuminate\Support\Str::uuid();

        DB::table('users')->insert([
            'id' => $userId,
            'email' => $email,
            'password' => Hash::make($password),
            'full_name' => $fullName,
            'role' => 'client',
            'plan_tier' => $plan,
            'credits_balance' => $credits,
            'commercial_status' => 'active',
            'monthly_video_limit' => 30,
            'videos_generated_this_month' => 0,
            'avatar_minutes_quota' => 60,
            'avatar_minutes_used' => 0,
            'auto_mode_enabled' => 0,
            'sheet_auto_mode' => 'copilot',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $brandName = ($fullName ?: 'Mi') . ' Marca';
        DB::table('brands')->insert([
            'id' => $brandId,
            'user_id' => $userId,
            'name' => $brandName,
            'primary_color' => '#16345F',
            'accent_color' => '#7DD3FC',
            'bg_color' => '#0B1E38',
            'font_style_title' => 'serif-editorial',
            'font_style_body' => 'sans-modern',
            'layout_preset' => 'editorial-top',
            'logo_position' => 'top-left',
            'logo_width_px' => 180,
            'created_at' => now(),
        ]);

        DB::table('credit_ledger')->insert([
            'id' => $ledgerId,
            'user_id' => $userId,
            'amount' => $credits,
            'action_type' => 'initial_signup',
            'description' => 'Créditos iniciales Plan ' . ucfirst($plan),
            'created_at' => now(),
        ]);

        $tokenStr = \Illuminate\Support\Str::random(60);
        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => User::class,
            'tokenable_id' => $userId,
            'name' => 'auth_token',
            'token' => hash('sha256', $tokenStr),
            'abilities' => json_encode(['*']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tokenId = DB::getPdo()->lastInsertId();
        $plainToken = "{$tokenId}|{$tokenStr}";

        return response()->json([
            'access_token' => $plainToken,
            'refresh_token' => $plainToken,
            'token_type' => 'bearer',
            'user' => [
                'id' => $userId,
                'email' => $email,
                'full_name' => $fullName,
                'role' => 'client',
                'plan_tier' => $plan,
                'credits_balance' => $credits,
            ],
        ], 201);
    });

    Route::post('/login', function (Request $request) {
        $email = $request->input('email');
        $password = $request->input('password');

        if (empty($email) || empty($password)) {
            return response()->json(['detail' => 'Email y contraseña requeridos'], 400);
        }

        $user = DB::table('users')->where('email', $email)->first();
        if (!$user) {
            return response()->json(['detail' => 'Credenciales incorrectas'], 401);
        }

        $isValid = Hash::check($password, $user->password) || password_verify($password, $user->password);
        if (!$isValid) {
            return response()->json(['detail' => 'Credenciales incorrectas'], 401);
        }

        $tokenStr = \Illuminate\Support\Str::random(60);
        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'auth_token',
            'token' => hash('sha256', $tokenStr),
            'abilities' => json_encode(['*']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tokenId = DB::getPdo()->lastInsertId();
        $plainToken = "{$tokenId}|{$tokenStr}";

        return response()->json([
            'access_token' => $plainToken,
            'refresh_token' => $plainToken,
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
    });
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
