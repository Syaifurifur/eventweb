#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
  storage/app/public \
  storage/cache \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

if [ ! -L public/storage ] && [ -d storage/app/public ]; then
  su -s /bin/sh www-data -c "php artisan storage:link" || true
fi

# Optimization caches for production
su -s /bin/sh www-data -c "php artisan config:cache" || true
su -s /bin/sh www-data -c "php artisan route:cache" || true
su -s /bin/sh www-data -c "php artisan view:cache" || true

if [ "$#" -gt 0 ]; then
  exec "$@"
fi

exec supervisord -n -c /etc/supervisor/supervisord.conf
