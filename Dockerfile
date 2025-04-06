FROM php:8.0-fpm

# Install Nginx and dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libc-client-dev \
    libkrb5-dev \
    libonig-dev \
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

# Set working directory
WORKDIR /var/www/html

# Copy ProjeQtOr files
COPY ./projeqtor/ /var/www/html/

# Configure Nginx
RUN echo 'server {\n\
    listen 80;\n\
    server_name localhost;\n\
    root /var/www/html;\n\
    index index.php index.html;\n\
\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
\n\
    location ~ \.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
    }\n\
}' > /etc/nginx/sites-available/default

# Create entrypoint script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Start PHP-FPM\n\
php-fpm -D\n\
\n\
# Start Nginx\n\
nginx -g "daemon off;"\n\
' > /usr/local/bin/docker-entrypoint.sh

# Make entrypoint executable
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Verify PHP extensions are installed
RUN php -m

# Expose port
EXPOSE 80

# Use the entrypoint script
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
