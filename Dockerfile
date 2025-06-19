FROM php:8.2-fpm

# 1. Installa le dipendenze
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    nano \
    mysql-client \
    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# 2. Installa Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Imposta directory
WORKDIR /var/www

# 4. Copia i file del progetto
COPY . .

# 5. Installa le dipendenze Laravel
RUN composer install --optimize-autoloader --no-dev

# 6. Imposta i permessi
RUN chown -R www-data:www-data /var/www

# 7. Migrazione + seed + avvio
CMD php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan serve --host=0.0.0.0 --port=8000
