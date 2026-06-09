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
    nginx \
    libpq-dev

# 🔴 Install Node.js and NPM (Required to build your Vite frontend assets)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y engineering nodejs

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Install backend dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 🔴 Install frontend dependencies and build your assets for production
RUN npm install
RUN npm run build

# Set total directory permissions for Nginx user (www-data)
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www

# Setup Nginx configuration
COPY ./nginx.conf /etc/nginx/sites-available/default

# Expose port 80
EXPOSE 80

# Start PHP-FPM in background and Nginx in foreground
CMD php-fpm -D && nginx -g "daemon off;"
