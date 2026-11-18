# Shelf in production (chapter 78), in stages. Build and start it with compose.prod.yaml.
#   base:    the official PHP-FPM image, with mbstring for text in any language, and intl for its
#            plurals and dates (chapter 87)
#   build:   the packages, without the tools for development, and Shelf's code
#   test:    build, plus the tools, and every check (chapter 79: CI runs docker build --target test)
#   release: build, without the tests
#   app:     PHP-FPM with production settings, the release and its warmed cache
#   web:     Caddy, the web server: HTTPS, the public files, and every other request to app
ARG PHP=8.6-rc-fpm

FROM php:${PHP} AS base
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev libicu-dev \
    && docker-php-ext-install mbstring intl \
    && rm -rf /var/lib/apt/lists/*

FROM base AS build
# git and unzip for Composer, only while building: the image that runs Shelf doesn't need them.
RUN apt-get update && apt-get install -y --no-install-recommends git unzip && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2.11 /usr/bin/composer /usr/bin/composer
WORKDIR /app
# The packages first, in a layer of their own: a change to Shelf's code doesn't download them again.
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress
COPY . .
# An autoloader that knows every class in advance, and never looks for a file.
RUN composer dump-autoload --no-dev --classmap-authoritative
# The stylesheet under its versioned name, in public/assets/ (chapter 80): Caddy serves it as a file.
RUN APP_ENV=prod php bin/console asset-map:compile

FROM build AS test
RUN composer install --no-scripts --prefer-dist --no-progress && composer check

FROM build AS release
RUN rm -rf tests phpunit.xml.dist phpstan.neon.dist .php-cs-fixer.dist.php .env.test

FROM base AS app
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-shelf.ini"
ENV APP_ENV=prod APP_DEBUG=0
WORKDIR /app
COPY --from=release /app /app
# The cache, made once now, not by the first visitor. Then Shelf runs as www-data, never as root, and
# may write only to var/ (Symfony's cache) and data/ (the database and the covers, kept in a volume).
RUN php bin/console cache:warmup \
    && mkdir -p data \
    && chown -R www-data:www-data var data
USER www-data
ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php-fpm"]

FROM caddy:2.11 AS web
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY --from=release /app/public /app/public
