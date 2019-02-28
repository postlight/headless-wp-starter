#!/usr/bin/env sh

set -e

mysql_ready='nc -z db-headless 3306'

if ! $mysql_ready
then
    printf 'Waiting for MySQL.'
    while ! $mysql_ready
    do
        printf '.'
        sleep 1
    done
    echo
fi

if wp core is-installed
then
    echo "WordPress is already installed, exiting."
    exit
fi

wp core download --force

[ -f wp-config.php ] || wp config create \
    --dbhost="$WORDPRESS_DB_HOST" \
    --dbname="$WORDPRESS_DB_NAME" \
    --dbuser="$WORDPRESS_DB_USER" \
    --dbpass="$WORDPRESS_DB_PASSWORD"

wp core install \
    --url="$WORDPRESS_URL" \
    --title="$WORDPRESS_TITLE" \
    --admin_user="$WORDPRESS_ADMIN_USER" \
    --admin_password="$WORDPRESS_ADMIN_PASSWORD" \
    --admin_email="$WORDPRESS_ADMIN_EMAIL" \
    --skip-email

wp option update blogdescription "$WORDPRESS_DESCRIPTION"
wp rewrite structure "$WORDPRESS_PERMALINK_STRUCTURE"

wp theme activate postlight-headless-wp
wp theme delete twentysixteen twentyseventeen twentynineteen

wp plugin delete akismet hello
wp plugin install --activate --force \
    acf-to-wp-api \
    advanced-custom-fields \
    custom-post-type-ui \
    wordpress-importer \
    wp-rest-api-v2-menus \
    jwt-authentication-for-wp-rest-api \
    https://github.com/wp-graphql/wp-graphql/archive/master.zip \
    https://github.com/wp-graphql/wp-graphql-jwt-authentication/archive/v0.3.1.zip \
    /var/www/plugins/*.zip

wp term update category 1 --name="Sample Category"
wp menu create "Header Menu"
wp menu item add-post header-menu 1
wp menu item add-post header-menu 2
wp menu item add-term header-menu category 1
wp menu item add-custom header-menu "Read about the Starter Kit" https://postlight.com/trackchanges/introducing-postlights-wordpress-react-starter-kit
wp menu location assign header-menu header-menu
wp post update 1 --post_title="Sample Post" --post_name=sample-post
wp import /var/www/postlightheadlesswpstarter.wordpress.xml --authors=skip

echo "Great. You can now log into WordPress at: $WORDPRESS_URL/wp-admin ($WORDPRESS_ADMIN_USER/$WORDPRESS_ADMIN_PASSWORD)"
