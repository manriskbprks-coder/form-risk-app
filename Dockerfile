FROM php:8.2-cli

# Install tool tambahan, PostgreSQL, & Node.js (buat Vite)
RUN apt-get update -y && apt-get install -y libpq-dev unzip curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo pdo_pgsql

# Ambil Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install kebutuhan Laravel (PHP)
RUN composer install --no-dev --optimize-autoloader

# Install kebutuhan Frontend & Masak desainnya (Vite)
RUN npm install
RUN npm run build

# Abaikan perintah bawaan, suruh server nyalain aplikasi
CMD php artisan migrate:fresh --seed --force && php artisan serve --host=0.0.0.0 --port=$PORT
