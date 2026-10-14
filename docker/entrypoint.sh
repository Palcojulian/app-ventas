#!/bin/bash

set -e

echo "======================================"
echo " Iniciando aplicación Laravel"
echo "======================================"

echo "Esperando a MySQL..."

until php -r '
try {
    $pdo = new PDO(
        sprintf(
            "mysql:host=%s;port=%s;dbname=%s",
            getenv("DB_HOST"),
            getenv("DB_PORT"),
            getenv("DB_DATABASE")
        ),
        getenv("DB_USERNAME"),
        getenv("DB_PASSWORD"),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "MySQL disponible\n";
} catch (PDOException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
'; do
    echo "MySQL todavía no está disponible..."
    sleep 2
done

echo "Instalando dependencias..."
composer install --no-interaction --optimize-autoloader

echo "Preparando Laravel..."
mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache

php artisan config:clear
php artisan migrate --force

echo "Iniciando Laravel..."
exec php artisan serve --host=0.0.0.0 --port=8000