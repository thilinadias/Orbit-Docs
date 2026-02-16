#!/bin/bash
set -e

# Copy .env if not exists (for first run with volume)
if [ ! -f .env ]; then
    echo "Creating .env file from example..."
    cp .env.example .env
fi

# Fix DB_HOST in .env for Docker environment
if grep -q "DB_HOST=127.0.0.1" .env; then
    echo "Updating DB_HOST to 'db'..."
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=db/g' .env
fi

# Fix DB_DATABASE in .env
if grep -q "DB_DATABASE=laravel" .env; then
    echo "Updating DB_DATABASE to 'orbitdocs'..."
    sed -i 's/DB_DATABASE=laravel/DB_DATABASE=orbitdocs/g' .env
fi

# Fix DB_USERNAME in .env
if grep -q "DB_USERNAME=root" .env; then
    echo "Updating DB_USERNAME to 'orbitdocs'..."
    sed -i 's/DB_USERNAME=root/DB_USERNAME=orbitdocs/g' .env
fi

# Fix DB_PASSWORD in .env (only if empty)
if grep -q "DB_PASSWORD=$" .env; then
    echo "Updating DB_PASSWORD to 'secret'..."
    sed -i 's/DB_PASSWORD=/DB_PASSWORD=secret/g' .env
fi

# Install Composer dependencies if missing
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

# Generate key if missing or empty
if [ -f .env ] && ! grep -q "^APP_KEY=base64" .env; then
    echo "Generating Application Key..."
    php artisan key:generate
fi

# Wait for MySQL (simple sleep to avoid initial connection failure)
echo "Waiting for Database..."
sleep 10

# Run Migrations with Retry Logic
echo "Running Migrations..."
max_retries=30
count=0
until php artisan migrate --force; do
    exit_code=$?
    count=$((count + 1))
    if [ $count -ge $max_retries ]; then
        echo "Migration failed after $count attempts. Exiting."
        exit $exit_code
    fi
    echo "Migration failed (Attempt $count/$max_retries). Retrying in 5 seconds..."
    sleep 5
done

# Create Storage Link
if [ ! -L "public/storage" ]; then
    echo "Creating Storage Link..."
    php artisan storage:link
fi

# Install NPM dependencies and build if missing
if [ ! -d "node_modules" ] || [ ! -d "public/build" ]; then
    echo "Building Frontend Assets..."
    npm install
    npm run build
fi

# Cache configuration if .env is valid (skip if it's the default dummy env)
# php artisan config:cache

echo "OrbitDocs is ready."
exec "$@"
