FROM php:8.4-apache

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        autoconf \
        gcc \
        g++ \
        make \
        pkg-config \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
        libxml2-dev \
        libpq-dev \
        libc-client2007e-dev \
        libkrb5-dev \
        libssl-dev \
        libonig-dev \
    && rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        gd \
        mbstring \
        mysqli \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        xml \
        zip

RUN set -eux; \
    yes '' | pecl install imap; \
    docker-php-ext-enable imap

COPY config/custom.ini /usr/local/etc/php/conf.d/99-custom.ini

RUN a2enmod rewrite

COPY projeqtor/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
