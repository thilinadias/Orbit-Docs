#!/usr/bin/bash
# VERSION: 2.3 - FIXED PROGRESS SCRIPT
SF=$1
NOW=$2
PHP="/usr/local/bin/php"
ARTISAN="/var/www/artisan"

echo "{\"status\":\"running\",\"step\":\"Migrating database tables...\",\"progress\":40,\"started\":\"$NOW\"}" > "$SF"
$PHP $ARTISAN migrate:fresh --force --no-interaction > /tmp/migrate_output.txt 2>&1
MIGRATE_EXIT=$?
if [ $MIGRATE_EXIT -ne 0 ]; then
    OUTPUT=$(cat /tmp/migrate_output.txt | tr '"' "'" | tr '\n' ' ')
    echo "{\"status\":\"error\",\"step\":\"migrate:fresh failed\",\"message\":\"$OUTPUT\"}" > "$SF"
    exit 1
fi

echo "{\"status\":\"running\",\"step\":\"Seeding system data...\",\"progress\":80,\"started\":\"$NOW\"}" > "$SF"
$PHP $ARTISAN db:seed --force --no-interaction > /tmp/seed_output.txt 2>&1
SEED_EXIT=$?
if [ $SEED_EXIT -ne 0 ]; then
    OUTPUT=$(cat /tmp/seed_output.txt | tr '"' "'" | tr '\n' ' ')
    echo "{\"status\":\"error\",\"step\":\"db:seed failed\",\"message\":\"$OUTPUT\"}" > "$SF"
    exit 1
fi

echo "{\"status\":\"done\",\"step\":\"Setup Complete\",\"progress\":100}" > "$SF"
