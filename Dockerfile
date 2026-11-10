# Tahap 1: build aset Vite
FROM node:22 AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Tahap 2: PHP + Apache
FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev unzip git \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_pgsql pgsql zip gd bcmath \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

# Document root ke folder public Laravel + hilangkan peringatan ServerName
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
COPY --from=assets /app/public/build public/build

# Pasang dependensi (termasuk Faker untuk seeder), siapkan storage, atur izin
RUN composer install --optimize-autoloader --no-interaction \
 && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 80

# Bersihkan cache lama, migrasi, seed, lalu jalankan Apache
CMD php artisan optimize:clear \
 && php artisan migrate --force \
 && php artisan db:seed --force \
 && apache2-foreground