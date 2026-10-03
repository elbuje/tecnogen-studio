<?php

use Illuminate\Support\Facades\Route;

Route::get('/debug-log', function () {
    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) {
        return response('No log file found', 200);
    }
    return response(file_get_contents($logFile), 200, ['Content-Type' => 'text/plain']);
});

Route::get('/', function () {
    return file_get_contents(public_path('index.html'));
});
