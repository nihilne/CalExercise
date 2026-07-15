#!/bin/sh
set -e

mkdir -p /app/database

if [ ! -f /app/database/database.sqlite ]; then
    touch /app/database/database.sqlite
fi

if [ ! -f /app/.env ]; then
    cp /app/.env.example /app/.env
fi

php artisan key:generate --force

php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=8080