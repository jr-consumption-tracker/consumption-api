FROM php:8.4-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Xdebug and Redis
RUN pecl install xdebug \
    && pecl install redis \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-enable redis

# Copy Xdebug configuration
COPY xdebug.ini "${PHP_INI_DIR}/conf.d"

# Set working directory
WORKDIR /var/www/html/api

# Expose port 9000
EXPOSE 9000

CMD ["php-fpm"]