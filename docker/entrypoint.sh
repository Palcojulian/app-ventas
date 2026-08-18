#!/bin/bash

set -e

echo "======================================"
echo " Iniciando aplicación Laravel"
echo "======================================"

echo "Esperando a MySQL..."

until php -r "
try {
    \$pdo = new PDO(
        'mysql:host=mysql;port=3306',
        'laravel',
        'secret'
    );
    echo 'MySQL disponible';
} catch (PDOException \$e) {
    exit(1);
}
"; do
    echo "MySQL todavía no está disponible..."
    sleep 2
done

echo "Instalando dependencias"
composer install --no-dev --optimize-autoloader

echo "Limpiando cache y ejecutando migraciones"
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "Iniciando Laravel..."
php artisan serve --host=0.0.0.0 --port=8000