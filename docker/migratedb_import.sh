#!/usr/bin/env sh

set -e

export WPMDB_EXCLUDE_RESIZED_MEDIA=1

if [ -z "$MIGRATEDB_LICENSE" ]
then
    echo "WP Migrate DB Pro: No license available. Please set MIGRATEDB_LICENSE in the .env file."
    exit
fi

if [ -z "$MIGRATEDB_FROM" ]
then
    echo "WP Migrate DB Pro: No source installation specified. Please set MIGRATEDB_FROM in the .env file."
    exit
fi

wp plugin activate \
    wp-migrate-db-pro \
    wp-migrate-db-pro-cli \
    wp-migrate-db-pro-media-files

wp migratedb setting update license "$MIGRATEDB_LICENSE"

echo "About to run data migration from $MIGRATEDB_FROM"

wp migratedb pull "$MIGRATEDB_FROM" --backup=prefix --media=compare

wp option update siteurl "$WORDPRESS_URL"
wp option update home "$WORDPRESS_URL"
