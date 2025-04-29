# 1) System deps for all extensions
RUN apt-get update && apt-get install -y \
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
    && rm -rf /var/lib/apt/lists/*

# 2) Configure GD and IMAP with explicit include paths
RUN docker-php-ext-configure gd \
        --enable-gd \
        --with-jpeg=/usr/include \
        --with-freetype=/usr/include/freetype2 \
    && docker-php-ext-configure imap \
        --with-imap=/usr \
        --with-imap-ssl \
        --with-kerberos

# 3) Build & enable all extensions
RUN docker-php-ext-install -j$(nproc) \
      gd \
      imap \
      mbstring \
      mysqli \
      pdo_mysql \
      pdo_pgsql \
      pgsql \
      xml \
      zip \
    && docker-php-ext-enable openssl
