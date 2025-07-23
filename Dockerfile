FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev \
    libpq-dev \
    librdkafka-dev \
    # supervisor \
    && docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install php-rdkafka extension
RUN pecl install rdkafka \
    && docker-php-ext-enable rdkafka

# # Set up supervisor
# RUN mkdir -p /etc/supervisor/conf.d /var/log/supervisor
# COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
# COPY docker/supervisor/conf.d/ /etc/supervisor/conf.d/

WORKDIR /var/www/html

# # Change the entrypoint to run both PHP-FPM and Supervisor
# COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
# RUN chmod +x /usr/local/bin/entrypoint.sh

# ENTRYPOINT ["entrypoint.sh"]
