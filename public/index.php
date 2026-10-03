<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Maintenance mode
if (file_exists($maintenance = __DIR__.'/../laravel-app/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Bootstrap Laravel
if (file_exists(__DIR__.'/../laravel-app/vendor/autoload.php')) {
    require __DIR__.'/../laravel-app/vendor/autoload.php';
    $app = require_once __DIR__.'/../laravel-app/bootstrap/app.php';
} elseif (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
}

// Servir archivos estáticos reales (imágenes, css, js) si existen físicamente
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file) && !str_ends_with($file, '.php')) {
    $mime = mime_content_type($file);
    if (str_ends_with($file, '.css')) $mime = 'text/css';
    if (str_ends_with($file, '.js')) $mime = 'application/javascript';
    if (str_ends_with($file, '.svg')) $mime = 'image/svg+xml';
    if (str_ends_with($file, '.webp')) $mime = 'image/webp';
    if (str_ends_with($file, '.png')) $mime = 'image/png';
    if (str_ends_with($file, '.jpg') || str_ends_with($file, '.jpeg')) $mime = 'image/jpeg';
    
    header("Content-Type: $mime");
    readfile($file);
    exit;
}

// Todas las peticiones son procesadas por Laravel 11 (Blade + Rutas Web y API)
if (isset($app)) {
    $request = Request::capture();
    $response = $app->handleRequest($request);
    $response->send();
    exit;
}
