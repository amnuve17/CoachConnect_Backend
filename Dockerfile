FROM php:8.2-fpm

# 1. Installa le dipendenze di sistema
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
    libpq-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# 2. Installa Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Imposta la cartella di lavoro
WORKDIR /var/www

# 4. Copia i file del progetto Laravel
COPY . .

# 5. Installa le dipendenze PHP
RUN composer install --optimize-autoloader --no-dev

# 6. Imposta i permessi
RUN chown -R www-data:www-data /var/www

# 7. Esegui le migrazioni e i seeder all'avvio
CMD php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan serve --host=0.0.0.0 --port=8000
