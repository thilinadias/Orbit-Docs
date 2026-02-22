FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    rsync \
    libzip-dev \
    dos2unix \
    default-mysql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js (v18)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Copy Custom PHP Config
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory inside image (separate from the runtime volume)
# /var/www-image holds the fully built app; on startup, entrypoint rsyncs it to /var/www
WORKDIR /var/www-image

# Copy application source
COPY . /var/www-image

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Build frontend assets
RUN npm install && npm run build

# Clean up node_modules after build (keeps image small)
RUN rm -rf node_modules

# Seed a default .env so artisan commands work during image build
RUN cp .env.example .env

# Set permissions on build-time directories
RUN chown -R www-data:www-data /var/www-image/storage /var/www-image/bootstrap/cache \
    && chmod -R 775 /var/www-image/storage /var/www-image/bootstrap/cache

# Runtime working directory (populated by entrypoint sync)
WORKDIR /var/www

# Copy and prepare entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && dos2unix /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
