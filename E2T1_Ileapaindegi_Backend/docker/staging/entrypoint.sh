#!/bin/bash
set -e

# Esperar a que la base de datos esté lista (healthcheck ya lo garantiza, pero por seguridad)
echo "Iniciando la aplicación Laravel..."

# Cachear configuración y rutas
php artisan config:cache
php artisan route:cache

# Ejecutar migraciones
php artisan migrate --force

# Crear enlace simbólico de storage (necesario para fotos del portfolio)
php artisan storage:link || true

# Ajustar permisos de storage
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Arrancar php-fpm en segundo plano
php-fpm -D

echo "PHP-FPM arrancado. Iniciando Nginx..."

# Arrancar Nginx en primer plano
exec nginx -g "daemon off;"
