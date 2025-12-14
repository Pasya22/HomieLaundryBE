echo "Running composer install..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "Clearing caches..."
php artisan optimize:clear

echo "Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running migrations..."
php artisan migrate --force

echo "Build complete!"
