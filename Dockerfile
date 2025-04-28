# syntax=docker/dockerfile:1
FROM php:8-apache

# Install system dependencies for GD, IMAP, OpenSSL, ZIP, XML, etc.
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libc-client-dev \
    libkrb5-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    pkg-config \
    curl \
  && rm -rf /var/lib/apt/lists/*

# Configure GD and IMAP before installation
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl

# Install all required PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    gd \
    imap \
    mbstring \
    mysqli \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    xml \
    zip \
 && docker-php-ext-enable openssl

# Drop in your custom PHP settings
COPY ./config/custom.ini /usr/local/etc/php/conf.d/99-custom.ini

# Enable Apache rewrite module (for clean URLs)
RUN a2enmod rewrite

# Copy application code
COPY ./projeqtor/ /var/www/html/

# Ensure correct ownership
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
