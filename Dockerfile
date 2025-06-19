FROM php:8.2-fpm

# 1. Installa librerie di sistema necessarie
RUN apt-get update && apt-get install -y \
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
    default-mysql-client \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# 2. Installa Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Imposta directory di lavoro
WORKDIR /var/www

# 4. Copia il codice sorgente nel container
COPY . .

# 5. Installa tutte le dipendenze, incluse quelle dev (es. Faker)
RUN composer install --optimize-autoloader

# 6. Permessi corretti
RUN chown -R www-data:www-data /var/www

# 7. Migrate + Seed + Serve
CMD php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan serve --host=0.0.0.0 --port=8000
