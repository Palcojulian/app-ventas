#!/bin/bash

set -e

echo "======================================"
echo " Iniciando aplicación Laravel"
echo "======================================"

echo "Instalando dependencias de Composer..."
composer install

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

echo ""
echo "Ejecutando migraciones y seeders..."
php artisan migrate --force
php artisan db:seed --force

echo ""
echo "Iniciando Laravel..."
php artisan serve --host=0.0.0.0 --port=8000