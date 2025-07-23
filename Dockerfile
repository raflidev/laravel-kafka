FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev \
    libpq-dev \
    librdkafka-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install php-rdkafka extension
RUN pecl install rdkafka \
    && docker-php-ext-enable rdkafka

WORKDIR /var/www/html
