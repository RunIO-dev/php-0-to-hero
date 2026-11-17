# The PHP the course uses. compose.yaml builds it once per PHP version.
ARG PHP=8.6-rc-cli
FROM php:${PHP}

# mbstring for text in any language; pdo_mysql for MariaDB and MySQL (chapter 59); git and unzip for Composer.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libonig-dev \
    && docker-php-ext-install mbstring pdo_mysql \
    && rm -rf /var/lib/apt/lists/* \
    && git config --system --add safe.directory /app   # the mounted repo belongs to you, not the container

COPY --from=composer:2.11 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HOME=/tmp/composer
WORKDIR /app
