<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Maintenance mode
if (file_exists($maintenance = __DIR__.'/../laravel-app/storage/framework/maintenance.php')) {
    require $maintenance;
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

// Register the Composer autoloader
require __DIR__.'/../laravel-app/vendor/autoload.php';

// Bootstrap Laravel 11 and handle the request
(require_once __DIR__.'/../laravel-app/bootstrap/app.php')
    ->handleRequest(Request::capture());
