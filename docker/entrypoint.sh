#!/usr/bin/env sh
set -eu

mkdir -p \
    database \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

php artisan migrate --force --no-interaction
php artisan storage:link --force >/dev/null 2>&1 || true

exec "$@"
