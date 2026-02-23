#!/bin/bash
# OrbitDocs Production Entrypoint
# Runs on every container start. Safe to restart; operations are idempotent.

log() { echo "[OrbitDocs] $*"; }

# ─────────────────────────────────────────────────────────────────────────────
# STEP 1: Sync code from image to shared volume
# rsync --delete on every start ensures code updates are always applied.
# We preserve: .env (user config), storage/ (uploads/sessions), bootstrap/cache/
# ─────────────────────────────────────────────────────────────────────────────
log "Syncing code from image to volume..."
rsync -a --delete \
    --exclude='/.env' \
    --exclude='/storage/' \
    --exclude='/bootstrap/cache/' \
    /var/www-image/ /var/www/

# Ensure required runtime directories exist after rsync
mkdir -p /var/www/bootstrap/cache
mkdir -p /var/www/storage/framework/{sessions,views,cache}
mkdir -p /var/www/storage/{logs,app/public}

log "Code sync complete."

# ─────────────────────────────────────────────────────────────────────────────
# STEP 2: Ensure .env exists and fix Docker service names
# ─────────────────────────────────────────────────────────────────────────────
if [ ! -f /var/www/.env ]; then
    log "No .env found — creating from .env.example..."
    cp /var/www/.env.example /var/www/.env
fi

dos2unix /var/www/.env 2>/dev/null || true

sed -i 's|^DB_HOST=127\.0\.0\.1|DB_HOST=db|'          /var/www/.env
sed -i 's|^DB_HOST=localhost|DB_HOST=db|'              /var/www/.env
sed -i 's|^DB_DATABASE=laravel|DB_DATABASE=orbitdocs|' /var/www/.env
sed -i 's|^DB_USERNAME=root|DB_USERNAME=orbitdocs|'    /var/www/.env
sed -i 's|^DB_PASSWORD=$|DB_PASSWORD=secret|'          /var/www/.env
sed -i 's|^REDIS_HOST=127\.0\.0\.1|REDIS_HOST=redis|'  /var/www/.env
sed -i 's|^REDIS_HOST=localhost|REDIS_HOST=redis|'     /var/www/.env

# ─────────────────────────────────────────────────────────────────────────────
# STEP 3: Generate APP_KEY if missing
# ─────────────────────────────────────────────────────────────────────────────
if ! grep -q "^APP_KEY=base64:" /var/www/.env; then
    log "Generating APP_KEY..."
    cd /var/www && php artisan key:generate --force --quiet
fi

# ─────────────────────────────────────────────────────────────────────────────
# STEP 4: File permissions
# ─────────────────────────────────────────────────────────────────────────────
log "Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775               /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chown www-data:www-data    /var/www/.env    2>/dev/null || true
chmod 660                  /var/www/.env    2>/dev/null || true

# ─────────────────────────────────────────────────────────────────────────────
# STEP 5: Storage symlink (always ensure it exists)
# ─────────────────────────────────────────────────────────────────────────────
if [ ! -L /var/www/public/storage ]; then
    log "Creating storage symlink..."
    cd /var/www && php artisan storage:link --force 2>/dev/null || true
fi

# ─────────────────────────────────────────────────────────────────────────────
# STEP 6: Database migrations — UPDATE PATH ONLY
#
# KEY DESIGN DECISION:
#   Fresh installs  → installer wizard runs "migrate:fresh --seed" (its job)
#   Existing installs → entrypoint runs incremental "migrate" here (update path)
#
# We detect "existing install" by the presence of storage/app/installed,
# which the installer writes on successful completion.
# ─────────────────────────────────────────────────────────────────────────────
run_update_migrations() {
    local db_host="${DB_HOST:-db}"
    local db_port="${DB_PORT:-3306}"
    local max_wait=120  # 2s * 120 = 4 min max

    log "Waiting for database at ${db_host}:${db_port}..."
    local count=0
    until (echo > /dev/tcp/${db_host}/${db_port}) 2>/dev/null; do
        count=$((count + 1))
        if [ "$count" -ge "$max_wait" ]; then
            log "ERROR: Database unavailable after $((max_wait * 2))s. Skipping migrations."
            return 1
        fi
        sleep 2
    done

    log "Database ready. Running incremental migrations (update path)..."
    cd /var/www
    if php artisan migrate --force 2>&1; then
        log "Migrations complete."
    else
        log "WARNING: Migration failed — run 'docker logs orbitdocs-app' for details."
    fi
}

INSTALLED_MARKER="/var/www/storage/app/installed"

if [ -f "$INSTALLED_MARKER" ]; then
    log "Existing installation detected — running incremental migrations in background..."
    run_update_migrations &
else
    log "Fresh installation — migrations will be handled by the installer wizard."
fi

# ─────────────────────────────────────────────────────────────────────────────
# START PHP-FPM
# ─────────────────────────────────────────────────────────────────────────────
log "Starting php-fpm..."
exec "$@"
