FROM php:8.2-cli

# Install tool tambahan, MySQL, & Node.js (buat Vite)
RUN apt-get update -y && apt-get install -y libpq-dev unzip curl default-mysql-client \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Ambil Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install kebutuhan Laravel (PHP) — tanpa dev dependencies
RUN composer install --no-dev --optimize-autoloader

# Install kebutuhan Frontend & Masak desainnya (Vite)
RUN npm install
RUN npm run build

# Hapus file & folder yang ga perlu di production
RUN rm -rf tests/ node_modules/ resources/css/ resources/js/ \
    phpunit.xml phpunit.dusk.xml .env.dusk \
    vite.config.js postcss.config.js tailwind.config.js \
    ROADMAP.md TEST_*.md chartplan.md sc_phase.md \
    rulescline .rulescline query RISK_REPORT_POLICY.md \
    _ide_helper.php _ide_helper_models.php

# Jalankan migration (tanpa fresh — biar data aman) & serve
CMD php artisan migrate:fresh --seed --force && php artisan serve --host=0.0.0.0 --port=$PORT
