#!/bin/bash
set -e

echo "=== DEPLOY TECNOGEN STUDIO (LARAVEL 11 + BLADE) START ==="

# 1. Bajar cambios de Git
git fetch origin main
git reset --hard origin/main

# 2. Limpiar estáticos antiguos de SPA
rm -rf public/index.html public/static public/app public/create public/creations public/brand public/characters public/paginators public/visual-types public/assets/index-*.js public/assets/index-*.css

# 3. Backend Laravel 11
echo "⚙️ Configurando Backend Laravel 11..."
cd laravel-app
composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

cd ..

# 4. Configurar carpetas de almacenamiento y symlink de storage
mkdir -p laravel-app/storage/app/gdrive_cache laravel-app/storage/framework/{sessions,views,cache} laravel-app/storage/logs laravel-app/public/storage/creations
rm -rf public/storage
ln -sf /home/ploi/studio.tecnogen.ar/laravel-app/public/storage /home/ploi/studio.tecnogen.ar/public/storage
chmod -R 777 laravel-app/storage laravel-app/bootstrap/cache laravel-app/public/storage public

echo "🚀 TecnoGen Studio (Laravel 11 + Blade) desplegado exitosamente!"

