FROM php:8.2-apache

# 1. Install tool tambahan, ekstensi PHP, dan Node.js (buat Vite)
RUN apt-get update -y && apt-get install -y \
    libpq-dev unzip curl default-mysql-client libpng-dev libzip-dev \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql opcache gd zip

# 2. Aktifkan modul rewrite Apache (wajib buat routing Laravel)
RUN a2enmod rewrite

# 3. Ubah Document Root Apache ke folder /public milik Laravel dan atur Port dinamis untuk Render
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 4. Ambil Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 5. Copy OPCache config (opsional, kalau ada)
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY . .

# 6. Install kebutuhan Laravel (PHP) tanpa dev dependencies
RUN composer install --no-dev --optimize-autoloader

# 7. Install & Build Frontend (Vite/Tailwind)
RUN npm install
RUN npm run build

# 8. Hapus file sisa build biar Docker Image jadi ringan
RUN rm -rf tests/ node_modules/ resources/css/ resources/js/ \
    phpunit.xml phpunit.dusk.xml vite.config.js postcss.config.js tailwind.config.js \
    *.md _ide_helper*.php

# 9. Atur permission (Hak Akses) folder krusial Laravel buat Apache (www-data)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 10. Bikin file entrypoint (script jalan sebelum Apache nyala)
# Script ini buat otomatis ngerun config cache & migrate saat server nyala
RUN echo '#!/bin/bash\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
php artisan migrate --force\n\
php artisan storage:link || true\n\
apache2-foreground' > /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

# Port bawaan Apache
EXPOSE 80

# Jalankan entrypoint script
CMD ["/usr/local/bin/entrypoint.sh"]