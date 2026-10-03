<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\AISettingController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\IntegrationController;
use App\Http\Controllers\Api\SystemLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Public Auth Endpoints
Route::prefix('v1/auth')->group(function () {
    Route::post('/test-err', function () {
        try {
            $userCount = DB::table('users')->count();
            return response()->json(['users' => $userCount]);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ], 200);
        }
    });

    Route::post('/login', function (Request $request) {
        try {
            $email = $request->input('email');
            $password = $request->input('password');

            $user = DB::table('users')->where('email', $email)->first();
            if (!$user) {
                return response()->json(['detail' => 'Usuario no encontrado: ' . $email], 401);
            }
            return response()->json(['status' => 'user_found', 'id' => $user->id]);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ], 200);
        }
    });
});

// Diagnostics
Route::match(['get', 'post'], 'v1/diagnostic', fn() => response()->json(['status' => 'ok', 'framework' => 'Laravel 11', 'db' => 'MySQL (tecnogen_studio)']));
