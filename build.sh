echo "Running composer install..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "Clearing caches..."
php artisan optimize:clear

echo "Running migrations..."
php artisan migrate --force

echo "Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Build complete!"
