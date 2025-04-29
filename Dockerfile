FROM php:8.0-apache

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
      libpng-dev \
      libjpeg62-turbo-dev \
      libfreetype6-dev \
      libzip-dev \
      libxml2-dev \
      libpq-dev \
      libc-client-dev \
      libkrb5-dev \
      libssl-dev \
      libonig-dev \
      pkg-config \
      curl; \
    rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    docker-php-ext-configure gd \
      --enable-gd \
      --with-jpeg=/usr/include \
      --with-freetype=/usr/include/freetype2; \
    docker-php-ext-configure imap \
      --with-imap=/usr \
      --with-imap-ssl \
      --with-kerberos

RUN set -eux; \
    docker-php-ext-install -j"$(nproc)" \
      gd \
      imap \
      mbstring \
      mysqli \
      pdo_mysql \
      pdo_pgsql \
      pgsql \
      xml \
      zip; \
    docker-php-ext-enable openssl

COPY config/custom.ini /usr/local/etc/php/conf.d/99-custom.ini

RUN a2enmod rewrite

COPY projeqtor/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
