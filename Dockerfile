# ----------------------------
# Base image: PHP with Apache
# ----------------------------
FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libzip-dev libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql bcmath intl zip gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Node.js 18 LTS (simpler and stable)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    node -v && npm -v

# Copy Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy Laravel app source
COPY . .

# Install PHP dependencies (locked versions)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Install Node dependencies and build frontend (optional)
RUN npm install && npm run build || true

# Fix permissions for Laravel writable directories
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Apache configuration adjustments
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf && \
    echo "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" >> /etc/apache2/apache2.conf

# Expose port 80 (map externally to 8000 in docker-compose)
EXPOSE 80

# Entrypoint: Run migrations and start Apache
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
