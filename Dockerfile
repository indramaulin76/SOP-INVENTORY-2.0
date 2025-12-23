# ===========================================================
# Sae Bakery Inventory - Production Dockerfile
# ===========================================================

# Gunakan PHP 8.4 sesuai versi laptop kamu (CachyOS)
FROM php:8.4-fpm

# 1. Install dependency system
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libicu-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 2. Install Node.js 20.x LTS (Wajib buat Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# 3. Install Ekstensi PHP yang dibutuhkan Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# 4. Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# 5. Copy file composer dulu (biar cache jalan)
COPY composer.json composer.lock ./

# Install PHP dependencies
# Tambah --ignore-platform-reqs buat jaga-jaga kalau ada beda minor version
RUN composer install --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# 6. Copy file package.json
COPY package.json package-lock.json* ./

# 7. Install Node dependencies (FIX: Install SEMUA termasuk devDependencies/Vite)
RUN npm install

# 8. Copy seluruh source code project
COPY . .

# 9. Generate Autoload & Build Frontend (Vite)
RUN composer dump-autoload --optimize
RUN npm run build

# 10. Atur Permission Folder Storage
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Expose port
EXPOSE 9000

CMD ["php-fpm"]