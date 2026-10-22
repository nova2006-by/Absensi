#!/bin/sh
set -eu

port="${PORT:-10000}"
app_dir="/var/www/html"

if [ "${VERCEL_CONTAINER:-0}" = "1" ]; then
    app_dir="/tmp/laravel-app"
    mkdir -p "$app_dir"
    cp -a /var/www/html/. "$app_dir/"
    sed -i "s#/var/www/html#$app_dir#g" /etc/apache2/sites-available/000-default.conf
fi

sed -i "s/^Listen 80$/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s#<VirtualHost \\*:[0-9][0-9]*>#<VirtualHost *:${port}>#" \
    /etc/apache2/sites-available/000-default.conf

mkdir -p \
    "$app_dir/bootstrap/cache" \
    "$app_dir/storage/app/private" \
    "$app_dir/storage/framework/cache/data" \
    "$app_dir/storage/framework/sessions" \
    "$app_dir/storage/framework/views" \
    "$app_dir/storage/logs" \
    "$app_dir/public/uploads"

chown -R www-data:www-data \
    "$app_dir/bootstrap/cache" \
    "$app_dir/storage" \
    "$app_dir/public/uploads"

if ! php -r '
$key = getenv("APP_KEY") ?: "";
$decoded = str_starts_with($key, "base64:")
    ? base64_decode(substr($key, 7), true)
    : $key;
if (is_string($decoded) && strlen($decoded) === 32) {
    exit(0);
}
exit(1);
'; then
    echo "APP_KEY tidak ada atau tidak valid. Isi APP_KEY dengan hasil php artisan key:generate --show di environment Vercel." >&2
    exit 1
fi

su -s /bin/sh www-data -c "
    set -eu
    cd '$app_dir'
    php artisan config:clear
    php artisan package:discover --ansi
    php artisan migrate --force
    php artisan config:cache
    php artisan view:cache
"

exec apache2-foreground
