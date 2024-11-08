
# php-app
FROM php:8.3.13-fpm-bullseye as php-app

RUN apt-get update
RUN apt-get install -y --no-install-recommends \
    nano

RUN pecl install xdebug-3.3.2 \
 && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer