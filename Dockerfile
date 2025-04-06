FROM php:8.0-fpm

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

# Debug package configuration for mbstring
RUN pkg-config --modversion oniguruma || echo "oniguruma package config not found"
RUN find /usr -name "onig*" || echo "No onig files found"
RUN ls -la /usr/include/ | grep onig || echo "No onig headers found"

# Try to install each extension with debug output
RUN set -x && \
    echo "PHP includes:" && php -i | grep include_path && \
    echo "Installing mbstring..." && \
    docker-php-ext-install mbstring || (echo "FAILED: mbstring" && false) && \
    echo "Installing mysqli..." && \
    docker-php-ext-install mysqli || (echo "FAILED: mysqli" && false) && \
    echo "Installing pdo..." && \
    docker-php-ext-install pdo || (echo "FAILED: pdo" && false) && \
    echo "Installing pdo_mysql..." && \
    docker-php-ext-install pdo_mysql || (echo "FAILED: pdo_mysql" && false) && \
    echo "Installing pdo_pgsql..." && \
    docker-php-ext-install pdo_pgsql || (echo "FAILED: pdo_pgsql" && false) && \
    echo "Installing pgsql..." && \
    docker-php-ext-install pgsql || (echo "FAILED: pgsql" && false) && \
    echo "Installing xml..." && \
    docker-php-ext-install xml || (echo "FAILED: xml" && false) && \
    echo "Installing zip..." && \
    docker-php-ext-install zip || (echo "FAILED: zip" && false)

# Verify installations
RUN php -m
