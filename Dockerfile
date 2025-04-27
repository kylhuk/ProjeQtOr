FROM php:8.0-apache

# Install system dependencies
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
    pkg-config \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    mbstring \
    mysqli \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    xml \
    zip

# Enable Apache rewrite module (needed for clean URLs, Projeqtor needs it)
RUN a2enmod rewrite

# Copy Projeqtor application
COPY ./projeqtor/ /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (done automatically by php:apache base, but just to be clear)
EXPOSE 80
