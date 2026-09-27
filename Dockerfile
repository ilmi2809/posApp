FROM php:8.3-cli-alpine

# Install system dependencies, PHP extensions, Node.js & SQLite
RUN apk add --no-cache \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev \
    nodejs \
    npm \
    sqlite \
    sqlite-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd intl opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set Working Directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install NPM & Build Vite Assets
RUN npm install && npm run build

# Ensure SQLite database file exists
RUN touch /var/www/html/database/database.sqlite

# Fix Permissions
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

ENV PORT=10000
EXPOSE 10000

# Run migrations, seeders, and start Laravel app
CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT}
