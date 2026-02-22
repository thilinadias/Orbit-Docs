#!/bin/bash
# OrbitDocs Production Entrypoint
# Runs on every container start. Safe to restart; idempotent operations only.

log() { echo "[OrbitDocs] $*"; }

# ─────────────────────────────────────────────────────────────────────────────
# STEP 1: Sync code from image to shared volume (runs on EVERY startup)
#
# This is the critical step that makes updates work. Without rsync on every
# start, a rebuilt image's new code would never reach the shared volume.
#
# We preserve:
#   .env             — user's environment configuration
#   storage/         — uploads, logs, sessions (overlaid by named volumes anyway)
#   bootstrap/cache/ — cache files (will be regenerated)
# ─────────────────────────────────────────────────────────────────────────────
log "Syncing code from image to volume..."
rsync -a --delete \
    --exclude='/.env' \
    --exclude='/storage/' \
    --exclude='/bootstrap/cache/' \
    /var/www-image/ /var/www/

log "Code sync complete."

# ─────────────────────────────────────────────────────────────────────────────
# STEP 2: Ensure .env exists and has correct Docker values
# ─────────────────────────────────────────────────────────────────────────────
if [ ! -f /var/www/.env ]; then
    log "No .env found — creating from .env.example..."
    cp /var/www/.env.example /var/www/.env
fi

# Ensure Unix line endings (safe on Linux, fixes Windows-edited files)
dos2unix /var/www/.env 2>/dev/null || true

# Patch any remaining localhost/default values to Docker service names.
# These sed commands are safe to run multiple times (already-correct values won't match).
sed -i 's|^DB_HOST=127\.0\.0\.1|DB_HOST=db|'         /var/www/.env
sed -i 's|^DB_HOST=localhost|DB_HOST=db|'             /var/www/.env
sed -i 's|^DB_DATABASE=laravel|DB_DATABASE=orbitdocs|' /var/www/.env
sed -i 's|^DB_USERNAME=root|DB_USERNAME=orbitdocs|'   /var/www/.env
sed -i 's|^DB_PASSWORD=$|DB_PASSWORD=secret|'         /var/www/.env
sed -i 's|^REDIS_HOST=127\.0\.0\.1|REDIS_HOST=redis|' /var/www/.env
sed -i 's|^REDIS_HOST=localhost|REDIS_HOST=redis|'    /var/www/.env

# ─────────────────────────────────────────────────────────────────────────────
# STEP 3: Generate APP_KEY if missing
# ─────────────────────────────────────────────────────────────────────────────
if ! grep -q "^APP_KEY=base64:" /var/www/.env; then
    log "Generating APP_KEY..."
    cd /var/www && php artisan key:generate --force --quiet
fi

# ─────────────────────────────────────────────────────────────────────────────
# STEP 4: Set correct file permissions
# ─────────────────────────────────────────────────────────────────────────────
log "Setting permissions..."
chown -R www-data:www-data \
    /var/www/storage \
    /var/www/bootstrap/cache || true
chmod -R 775 \
    /var/www/storage \
    /var/www/bootstrap/cache || true
chown www-data:www-data /var/www/.env && chmod 660 /var/www/.env || true

# ─────────────────────────────────────────────────────────────────────────────
# STEP 5: Run migrations in background (non-blocking)
#
# php-fpm starts immediately (no 2-3 min 502 window).
# Migrations run in background after DB is confirmed ready.
# Uses TCP check (not artisan) so it works even before app is configured.
# ─────────────────────────────────────────────────────────────────────────────
run_migrations() {
    local db_host="${DB_HOST:-db}"
    local db_port="${DB_PORT:-3306}"
    local max_wait=120  # 120 x 2s = 4 minutes max

    log "Waiting for database at ${db_host}:${db_port}..."
    local count=0
    until (echo > /dev/tcp/${db_host}/${db_port}) 2>/dev/null; do
        count=$((count + 1))
        if [ "$count" -ge "$max_wait" ]; then
            log "ERROR: Database did not become available after $((max_wait * 2))s. Migrations skipped."
            return 1
        fi
        sleep 2
    done

    log "Database is ready. Running migrations..."
    cd /var/www

    if php artisan migrate --force 2>&1; then
        log "Migrations complete."
    else
        log "WARNING: Migrations failed — check 'docker logs orbitdocs-app' for details."
    fi

    # Create storage symlink if missing
    if [ ! -L /var/www/public/storage ]; then
        log "Creating storage symlink..."
        php artisan storage:link --force 2>/dev/null || true
    fi
}

log "Starting background database setup..."
run_migrations &

log "Starting php-fpm..."
exec "$@"
