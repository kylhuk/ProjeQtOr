FROM php:8.0-apache

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libc-client-dev \
    libkrb5-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Configure and install GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Configure and install IMAP extension
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j$(nproc) imap

# Install other required PHP extensions
RUN docker-php-ext-install \
    mbstring \
    mysqli \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    xml \
    zip

# Install OpenSSL (already included in base image)

# Configure PHP
RUN { \
    echo 'max_input_vars = 4000'; \
    echo 'request_terminate_timeout = 0'; \
    echo 'max_execution_time = 120'; \
    echo 'memory_limit = 512M'; \
    echo 'file_uploads = On'; \
    echo 'post_max_size = 100M'; \
    echo 'upload_max_filesize = 100M'; \
    echo 'error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT'; \
    } > /usr/local/etc/php/conf.d/projeqtor-recommended.ini

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy ProjeQtOr files from the projeqtor directory
COPY ./projeqtor/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
