# syntax=docker/dockerfile:1

# Production image: FrankenPHP (Caddy + PHP in one binary) serving public/.
# Build:  docker build -t laravel-blade-template .
# Run:    docker compose up --build   (see compose.yaml)

ARG PHP_VERSION=8.5
ARG NODE_VERSION=22

FROM node:${NODE_VERSION}-trixie-slim AS node

# ---------------------------------------------------------------------------
# Base: PHP runtime with the extensions the app and every supported DB need.
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php${PHP_VERSION} AS base

RUN install-php-extensions \
        bcmath \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_pgsql \
        zip \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"

WORKDIR /app

# ---------------------------------------------------------------------------
# Build: install Composer and npm dependencies and compile assets with Vite.
# ---------------------------------------------------------------------------
FROM base AS build

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

# Production images exclude dev packages. The demo build in compose.yaml sets
# this to true so the seeder can use Faker for placeholder data.
ARG COMPOSER_DEV=false

# Dependency manifests first, so these layers stay cached between code changes.
COPY composer.json composer.lock ./
RUN composer install $([ "$COMPOSER_DEV" = true ] || echo --no-dev) \
        --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY package.json package-lock.json .npmrc ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN composer dump-autoload --optimize $([ "$COMPOSER_DEV" = true ] || echo --no-dev --classmap-authoritative) \
    && npm run build \
    && rm -rf node_modules

# ---------------------------------------------------------------------------
# Runtime: only the application, running as an unprivileged user.
# ---------------------------------------------------------------------------
FROM base AS app

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    SERVER_NAME=:8080

COPY --from=build --chown=www-data:www-data /app /app
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/app-entrypoint

RUN chown -R www-data:www-data /config/caddy /data/caddy

USER www-data

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/up || exit 1

ENTRYPOINT ["app-entrypoint"]
CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
