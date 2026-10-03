<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determinar ruta de autoload de Laravel
if (file_exists($maintenance = __DIR__.'/../laravel-app/storage/framework/maintenance.php')) {
    require $maintenance;
}

if (file_exists(__DIR__.'/../laravel-app/vendor/autoload.php')) {
    require __DIR__.'/../laravel-app/vendor/autoload.php';
    $app = require_once __DIR__.'/../laravel-app/bootstrap/app.php';
} elseif (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Si la ruta inicia con /api o /sanctum o /debug, la procesa Laravel directamente
if (strpos($uri, '/api') === 0 || strpos($uri, '/sanctum') === 0 || strpos($uri, '/debug') === 0) {
    if (isset($app)) {
        $request = Request::capture();
        $response = $app->handleRequest($request);
        $response->send();
        exit;
    }
}

// Para rutas de la SPA React: si existe archivo estático en public (que no sea index.html ni php), servirlo
$file = __DIR__ . $uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file) && !str_ends_with($file, '.html') && !str_ends_with($file, '.php')) {
    $mime = mime_content_type($file);
    if (str_ends_with($file, '.css')) $mime = 'text/css';
    if (str_ends_with($file, '.js')) $mime = 'application/javascript';
    if (str_ends_with($file, '.svg')) $mime = 'image/svg+xml';
    
    header("Content-Type: $mime");
    readfile($file);
    exit;
}

// Fallback a React SPA index.html para todas las rutas de cliente (/app/*, /login, etc.)
if (file_exists(__DIR__ . '/index.html')) {
    header("Content-Type: text/html");
    readfile(__DIR__ . '/index.html');
    exit;
}

// Fallback por defecto si no hay index.html compilado
if (isset($app)) {
    $request = Request::capture();
    $response = $app->handleRequest($request);
    $response->send();
}
