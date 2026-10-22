#!/bin/sh
set -eu

port="${PORT:-10000}"

sed -i "s/^Listen 80$/Listen ${port}/" /etc/apache2/ports.conf

if [ "$port" != "10000" ]; then
    sed -i "s/:10000>/:${port}>/" /etc/apache2/sites-available/000-default.conf
fi

mkdir -p \
    bootstrap/cache \
    storage/app/private \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    public/uploads

chown -R www-data:www-data bootstrap/cache storage public/uploads

su -s /bin/sh www-data -c '
    set -eu
    cd /var/www/html
    php artisan config:clear
    php artisan package:discover --ansi
    php artisan migrate --force
    php artisan config:cache
    php artisan view:cache
'

exec apache2-foreground
