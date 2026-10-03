<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\AISettingController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\IntegrationController;
use App\Http\Controllers\Api\SystemLogController;
use Illuminate\Support\Facades\Route;

// Public Auth Endpoints
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public Content Viewer (para compartir links sin auth si es necesario)
Route::get('v1/contents/public/{id}', [ContentController::class, 'publicShow']);

// Diagnostics
Route::post('v1/diagnostic', fn() => response()->json(['status' => 'ok']));
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
