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
    vim \
    procps \
    net-tools \
    netcat-traditional

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Create directory structure
RUN mkdir -p /var/www/public \
    /var/www/storage/framework/{sessions,views,cache,testing} \
    /var/www/storage/logs \
    /var/www/bootstrap/cache \
    /var/log/php-fpm \
    /var/lib/php/session

# Copy the entire application
COPY . /var/www/

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && chmod -R 777 /var/www/storage \
    && chmod -R 777 /var/www/bootstrap/cache \
    && chmod -R 777 /var/www/storage/framework/cache

# Install composer dependencies
RUN cd /var/www && \
    rm -rf vendor && \
    composer install --no-interaction

# Copy configurations
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Create logs directory for Nginx and PHP-FPM
RUN mkdir -p /var/log/nginx /var/log/php-fpm \
    && touch /var/log/nginx/access.log \
    && touch /var/log/nginx/error.log \
    && touch /var/log/php-fpm/www-error.log \
    && chown -R www-data:www-data /var/log/nginx \
    && chown -R www-data:www-data /var/log/php-fpm

# Final permissions check
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && chmod -R 777 /var/www/storage \
    && chmod -R 777 /var/www/bootstrap/cache

# Generate autoload files
RUN cd /var/www && composer dump-autoload --optimize

# Expose port 80
EXPOSE 80

# Create startup script
RUN echo '#!/bin/sh\n\
set -e\n\
\n\
cd /var/www\n\
\n\
# Wait for database\n\
echo "Waiting for database..."\n\
while ! nc -z api-gateway-db 3306; do\n\
  sleep 1\n\
done\n\
echo "Database is up!"\n\
\n\
echo "Checking vendor directory..."\n\
if [ ! -d "/var/www/vendor" ] || [ ! -f "/var/www/vendor/autoload.php" ]; then\n\
    echo "Installing dependencies..."\n\
    rm -rf vendor\n\
    composer install --no-interaction\n\
    composer dump-autoload --optimize\n\
fi\n\
\n\
echo "Starting Laravel application..."\n\
php artisan optimize:clear\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
echo "Starting Nginx..."\n\
service nginx start\n\
\n\
echo "Starting PHP-FPM..."\n\
php-fpm --nodaemonize\n\
' > /usr/local/bin/start.sh \
&& chmod +x /usr/local/bin/start.sh

# Start services
CMD ["/usr/local/bin/start.sh"]
