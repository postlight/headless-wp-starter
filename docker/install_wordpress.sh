#!/usr/bin/env sh

set -e

mysql_ready='nc -z db-headless 3306'
wp='sudo -Eu www-data wp'

apt-get install -y netcat sudo

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

$wp core download --force
$wp core install \
    --url="$WORDPRESS_URL" \
    --title="$WORDPRESS_TITLE" \
    --admin_user="$WORDPRESS_ADMIN_USER" \
    --admin_password="$WORDPRESS_ADMIN_PASSWORD" \
    --admin_email="$WORDPRESS_ADMIN_EMAIL" \
    --skip-email

$wp option update blogdescription "$WORDPRESS_DESCRIPTION"
$wp rewrite structure "$WORDPRESS_PERMALINK_STRUCTURE"

$wp plugin delete akismet hello
$wp plugin install --activate --force \
    advanced-custom-fields \
    acf-to-wp-api \
    custom-post-type-ui \
    wp-rest-api-v2-menus \
    wordpress-importer \
    https://github.com/wp-graphql/wp-graphql/archive/master.zip \
    /tmp/wp-migrate-db-pro*.zip

$wp theme activate postlight-headless-wp
$wp theme delete twentysixteen twentyseventeen twentynineteen

$wp term update category 1 --name="Sample Category"
$wp menu create "Header Menu"
$wp menu item add-post header-menu 1
$wp menu item add-post header-menu 2
$wp menu item add-term header-menu category 1
$wp menu item add-custom header-menu "Read about the Starter Kit on Medium" https://trackchanges.postlight.com/introducing-postlights-wordpress-react-starter-kit-a61e2633c48c
$wp menu location assign header-menu header-menu
$wp post update 1 --post_title="Sample Post" --post_name=sample-post
$wp import /tmp/postlightheadlesswpstarter.wordpress.xml --authors=skip

echo "Great. You can now log into WordPress at: $WORDPRESS_URL/wp-admin ($WORDPRESS_ADMIN_USER/$WORDPRESS_ADMIN_PASSWORD)"
