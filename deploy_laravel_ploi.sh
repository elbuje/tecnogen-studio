#!/bin/bash
set -e

echo "=== DEPLOY TECNOGEN STUDIO (LARAVEL 11 + REACT SPA) START ==="

# 1. Bajar cambios de Git
git fetch origin main
git reset --hard origin/main

# 2. Permisos y carpetas de almacenamiento
mkdir -p laravel-app/storage/framework/{sessions,views,cache} laravel-app/storage/logs
chmod -R 775 laravel-app/storage laravel-app/bootstrap/cache public

# 3. Permisos y carpetas de almacenamiento
echo "⚙️ Configurando directorios de almacenamiento..."
mkdir -p laravel-app/storage/app/gdrive_cache laravel-app/storage/framework/{sessions,views,cache} laravel-app/storage/logs
chmod -R 775 laravel-app/storage laravel-app/bootstrap/cache public

# 4. Backend Laravel 11
echo "⚙️ Configurando Backend Laravel 11..."
cd laravel-app
composer install --no-dev --optimize-autoloader --no-interaction
php artisan config:clear
php artisan route:clear
php artisan migrate --force

cd ..

# 5. Detener cualquier vestigio residual de Uvicorn/FastAPI
pkill -f "uvicorn" 2>/dev/null || true

# 6. Permisos finales
chmod -R 775 laravel-app/storage public

echo "🚀 TecnoGen Studio (Laravel 11 + MySQL) desplegado exitosamente!"
