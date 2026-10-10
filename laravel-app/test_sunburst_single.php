<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = new App\Services\PaginatorImageService();
echo "=== Generando Paginador #01 con Sunburst ===\n";
$res = $service->generateSingle(1);
echo json_encode($res, JSON_PRETTY_PRINT) . "\n";
