# 12 · Despliegue y Docker

`app-ventas` viene preparado para ejecutarse con Docker local. En esta guía se documenta el flujo de configuración y los comandos clave.

## 🐳 Servicios definidos

`docker-compose.yml`:

```yaml
services:
  app:
    build: .
    container_name: app-ventas
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
    ports:
      - "8000:8000"
    depends_on:
      - mysql

  mysql:
    image: mysql:8.0
    container_name: mysql-app-ventas
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    ports:
      - "3307:3306"
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

- `app` se construye con el `Dockerfile` (PHP 8.4-cli con extensiones para Laravel + maatwebsite/excel).
- `mysql` expone `3307` en host para evitar conflictos con un MySQL local en `3306`.
- Los volúmenes mantienen persistencia del código y de la base de datos entre reinicios.

## 🧱 `Dockerfile`

```dockerfile
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip zip curl libzip-dev libpng-dev libjpeg62-turbo-dev \
    libfreetype6-dev libxml2-dev libonig-dev default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mysqli zip gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
```

Extensiones clave: `pdo`, `pdo_mysql`, `gd`, `zip` (esta última requerida por `maatwebsite/excel`).

## 🚀 Levantar el entorno

```bash
# 1. Variables de entorno (tomar .env.example como base)
cp .env.example .env

# 2. Levantar servicios
docker compose up -d --build

# 3. Entrar al contenedor de la app
docker compose exec app bash

# 4. Instalar dependencias y preparar Laravel
composer install
php artisan key:generate
php artisan migrate --seed

# 5. Arrancar servidor (alternativa a composer dev)
php artisan serve --host=0.0.0.0 --port=8000
```

> El `entrypoint.sh` se encarga de preparar el proyecto y mantener PHP corriendo. Consultar `docker/entrypoint.sh` para detalles exactos.

## 🧪 Flujo alternativo sin Docker

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build

# Levanta en paralelo: serve + queue + pail + vite
composer dev
```

`composer dev` ejecuta `concurrently` con cuatro procesos:
- `php artisan serve`
- `php artisan queue:listen --tries=1 --timeout=0`
- `php artisan pail --timeout=0`
- `npm run dev`

## ⚙️ Variables de entorno clave

| Variable | Uso |
|----------|-----|
| `APP_NAME` | Nombre mostrado por Laravel. |
| `APP_ENV` | `local`, `production`. |
| `APP_KEY` | Se genera con `php artisan key:generate`. |
| `APP_URL` | Se usa en `get-absolute-url` y enlaces generados. |
| `DB_CONNECTION` | `mysql` (configurado por defecto). |
| `DB_HOST` | `mysql` cuando se usa Docker; `127.0.0.1` sin Docker. |
| `DB_PORT` | `3306` interno, `3307` mapeado en host por `docker-compose`. |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Requeridos para los servicios. |
| `CACHE_STORE` | Por defecto `database` (migración `cache_table`). |
| `QUEUE_CONNECTION` | `sync` por defecto; cambiar a `database` o `redis` para producción. |
| `MAIL_MAILER` | Configurar SMTP sólo cuando haya emails transaccionales. |
| `SANCTUM_*` | Configuración de tokens personales. |

> 💡 Tras modificar `.env`, ejecuta `php artisan config:clear`.

## 🏭 Despliegue sugerido (producción)

1. `composer install --no-dev --optimize-autoloader`.
2. `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.
3. `php artisan migrate --force` con un usuario con permisos restringidos.
4. `npm ci && npm run build` para assets.
5. Servir con PHP-FPM + Nginx, no `php artisan serve`.
6. Cola en proceso separado con Supervisor:

   ```ini
   [program:app-ventas-worker]
   command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
   autostart=true
   autorestart=true
   user=www-data
   numprocs=2
   redirect_stderr=true
   stdout_logfile=/var/www/html/storage/logs/worker.log
   ```

7. Cron del servidor:

   ```cron
   * * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
   ```

## 🛡️ Seguridad en producción

- Forzar HTTPS en el balanceador/ingress.
- En `config/sanctum.php`, ajustar `stateful` para los dominios del frontend.
- Manejo de errores: en producción considerar `APP_DEBUG=false` y un render genérico de 500 que no filtre `$e->getMessage()`.

## 🧭 Comandos útiles

```bash
php artisan optimize:clear       # limpia todas las cachés
php artisan route:list           # rutas registradas
php artisan config:show auth     # muestra config interpolada
php artisan tinker --execute "echo App\\Models\\User::count();"
```

## ✅ Checklist de despliegue

- [ ] `composer install --no-dev`.
- [ ] `npm ci && npm run build`.
- [ ] `php artisan key:generate`.
- [ ] `php artisan migrate --force`.
- [ ] `php artisan storage:link` (si sirves archivos públicos).
- [ ] `php artisan config:cache` y `route:cache`.
- [ ] Supervisor + queue configurado.
- [ ] Cron configurado.
- [ ] Nginx/Apache apuntando a `public/`.
- [ ] `APP_DEBUG=false` y `APP_ENV=production`.
