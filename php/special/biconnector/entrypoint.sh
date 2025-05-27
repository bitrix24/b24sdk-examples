#!/bin/bash
set -e

# Composer install if needed
if [ ! -d /var/www/vendor ] || [ -z "$(ls -A /var/www/vendor)" ]; then
    echo "Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Initialize demo.db if it does not exist or is empty
if [ ! -f /var/www/data/demo.db ] || [ ! -s /var/www/data/demo.db ]; then
    echo "Initializing SQLite database..."
    sqlite3 /var/www/data/demo.db < /var/www/sql/schema.sql
    echo "Database created."
fi

# Start apache
exec apache2-foreground