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

wp config set JWT_AUTH_SECRET_KEY 'your-secret-here'
wp config set GRAPHQL_JWT_AUTH_SECRET_KEY 'your-secret-here'

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
wp theme delete twentytwenty twentytwentyone twentytwentytwo

wp plugin delete akismet hello
wp plugin install --activate --force \
    acf-to-wp-api \
    advanced-custom-fields \
    custom-post-type-ui \
    wordpress-importer \
    wp-rest-api-v2-menus \
    jwt-authentication-for-wp-rest-api \
    wp-graphql \
    https://github.com/wp-graphql/wp-graphql-jwt-authentication/archive/refs/tags/v0.4.1.zip \
    https://github.com/wp-graphql/wp-graphql-acf/archive/master.zip \
    /var/www/plugins/*.zip

wp term update category 1 --name="Sample Category"
wp post delete 1 2

wp import /var/www/postlightheadlesswpstarter.wordpress.xml --authors=skip --skip=attachment

wp media import /var/www/images/Graphql2.png --featured_image \
  --post_id=$(wp post list --field=ID --name=what-do-you-need-to-know-about-graphql)
wp media import /var/www/images/19-word-press-without-shame-0.png --featured_image \
  --post_id=$(wp post list --field=ID --name=wordpress-without-shame)
wp media import /var/www/images/cropped-hal-gatewood-tZc3vjPCk-Q-unsplash.jpg --featured_image \
  --post_id=$(wp post list --field=ID --name=why-bother-with-a-headless-cms)
wp media import /var/www/images/careers-photo-opt.jpg --featured_image \
  --post_id=$(wp post list --field=ID --post_type=page --name=postlight-careers)

echo "Great. You can now log into WordPress at: $WORDPRESS_URL/wp-admin ($WORDPRESS_ADMIN_USER/$WORDPRESS_ADMIN_PASSWORD)"
