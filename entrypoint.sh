#!/bin/bash
set -e

cd /var/www/html

# If vendor missing, install dependencies
if [ ! -d "vendor" ]; then
  echo "Installing Composer dependencies..."
  composer install --no-dev --optimize-autoloader
fi

# Ensure Laravel key
if ! grep -q "APP_KEY=" .env || [ -z "$(grep APP_KEY .env | cut -d '=' -f2)" ]; then
  echo "Generating app key..."
  php artisan key:generate
fi

# Run OpenGRC installation if not installed yet
if ! php artisan migrate:status > /dev/null 2>&1; then
  echo "Running OpenGRC installation..."
  if [ -f "install.sh" ]; then
    bash install.sh || true
  fi
fi

# Fix permissions each time
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

echo "Starting Apache..."
exec apache2-foreground

