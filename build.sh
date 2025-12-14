
echo "Running composer install..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "Clearing caches..."
php artisan optimize:clear

echo "Build complete!"
