# Production image for Coolify (Build Pack: Dockerfile).
#
# 1. vendor: Composer packages, without dev dependencies.
# 2. assets: Vite build (needs vendor/ for Ziggy's Vue plugin).
# 3. app:    PHP 8.4 + nginx + PHP-FPM (serversideup/php), listening on 8080.
#
# On start the image runs `migrate --force`, `storage:link` and caches
# config/routes/views/events (AUTORUN_ENABLED), so the database and runtime
# environment variables must be set in Coolify.

FROM serversideup/php:8.4-fpm-nginx AS vendor
WORKDIR /var/www/html
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader

FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm ci
COPY . .
COPY --from=vendor /var/www/html/vendor ./vendor
RUN npm run build

FROM serversideup/php:8.4-fpm-nginx AS app
ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true
WORKDIR /var/www/html
COPY --chown=www-data:www-data --from=vendor /var/www/html/vendor ./vendor
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative
