
# php-app
FROM php:8.3.13-fpm-bullseye as php-app

RUN apt-get update
RUN apt-get install -y nano
RUN apt-get install -y zlib1g-dev
RUN apt-get install -y libpng-dev
RUN apt-get install -y libjpeg-dev
RUN apt-get install -y libjpeg62-turbo-dev
RUN apt-get install -y libfreetype6-dev

RUN pecl install xdebug-3.3.2 \
 && docker-php-ext-enable xdebug

RUN docker-php-ext-install gd \
 && docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer