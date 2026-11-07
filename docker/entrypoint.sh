#!/bin/sh
set -e

# Configuration comes from the environment at run time, so caches are built
# here rather than in the image.
php artisan optimize

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${SEED_DEMO_DATA:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec docker-php-entrypoint "$@"
