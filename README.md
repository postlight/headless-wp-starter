![WordPress + React Starter Kit](frontend/static/images/wordpress-plus-react-header.png)

[![Build status](https://travis-ci.org/postlight/headless-wp-starter.svg)](https://travis-ci.org/postlight/headless-wp-starter)

[Postlight](https://postlight.com)'s Headless WordPress + React Starter Kit is an automated toolset that will spin up three things:

1.  A WordPress backend that serves its data via the [WP REST API](https://developer.wordpress.org/rest-api/) and [GraphQL](http://graphql.org/).
2.  A sample React frontend powered by the [WP GraphQL API](https://www.wpgraphql.com/) which supports posts, pages, categories, menus, search, and user sign-in.
3.  Another sample server-side rendered React frontend using [Next.js](https://github.com/zeit/next.js/) powered by the WP REST API.

You can read all about it in [this handy introduction](https://postlight.com/trackchanges/introducing-postlights-wordpress-react-starter-kit).

**What's inside:**

- An automated installer script which bootstraps a core WordPress installation that provides an established, stable REST API.
- A plugin which exposes a newer, in-progress [GraphQL API for WordPress](https://wpgraphql.com/).
- The WordPress plugins you need to set up custom post types and custom fields ([Advanced Custom Fields](https://www.advancedcustomfields.com/) and [Custom Post Type UI](https://wordpress.org/plugins/custom-post-type-ui/)).
- Plugins which expose those custom fields and WordPress menus in the [WP REST API](https://developer.wordpress.org/rest-api/) ([ACF to WP API](https://wordpress.org/plugins/acf-to-wp-api/) and [WP-REST-API V2 Menus](https://wordpress.org/plugins/wp-rest-api-v2-menus/)).
- JWT authentication plugins: [JWT WP REST](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/) and [JWT WP GraphQL](https://github.com/wp-graphql/wp-graphql-jwt-authentication).
- All the starter WordPress theme code and settings headless requires, including pretty permalinks, CORS `Allow-Origin` headers, and useful logging functions for easy debugging.
- A mechanism for easily importing data from an existing WordPress installation anywhere on the web using [WP Migrate DB Pro](https://deliciousbrains.com/wp-migrate-db-pro/) and its accompanying plugins (license required).
- A sample, starter frontend React app powered by [GraphQL](http://graphql.org/).
- Another sample, starter frontend React app, server-side rendered via [Next.js](https://learnnextjs.com/), powered by the WP REST API.
- [Docker](https://www.docker.com/) containers and scripts to manage them, for easily running the frontend React apps and backend locally or deploying it to any hosting provider with Docker support.

Let's get started.

## Install

Before you install WordPress, make sure you have [Docker](https://www.docker.com) installed. On Linux, you might need to install [docker-compose](https://docs.docker.com/compose/install/#install-compose) separately.

Docker Compose builds and starts four containers by default - `db-headless`, `wp-headless`, `frontend` & `frontend-graphql`:

    docker-compose up -d

**Wait a few minutes** for Docker to build the services for the first time. After the initial build, startup should only take a few seconds.

You can follow the Docker output to see build progress and logs:

    docker-compose logs -f

Alternatively, you can use some useful Docker tools like Kitematic and/or VSCode Docker plugin to follow logs, start / stop / remove containers and images.

_Optional:_ you can run the frontend locally while WordPress still runs on Docker:

    docker-compose up -d wp-headless
    cd frontend && yarn && yarn start

Once the containers are running, you can access the frontend and backend.

## Frontend

This starter kit provides two frontend containers:

- `frontend` container powered by the WP REST API is server-side rendered using Next.js, and exposed on port `3000`: [http://localhost:3000](http://localhost:3000)
- `frontend-graphql` container powered by GraphQL, exposed on port `3001`: [http://localhost:3001](http://localhost:3001)

Follow `yarn start` output by running docker-compose `logs` command followed by the container name. For example:

    docker-compose logs -f frontend

If you need to restart that process, restart the container:

    docker-compose restart frontend

**PS:** Browsing the Next.js frontend in development mode is relatively slow due to the fact that pages are being built on demand. In a production environment, there would be a significant improvement in page load.

## Backend

The `wp-headless` container exposes Apache on host port `8080`:

- Dashboard: [http://localhost:8080/wp-admin](http://localhost:8080/wp-admin) (default credentials `nedstark`/`winteriscoming`)
- REST API: [http://localhost:8080/wp-json](http://localhost:8080/wp-json)
- GraphQL API: [http://localhost:8080/graphql](http://localhost:8080/graphql) (Note: the WP GraphQL plugin needs to be enabled first)

This container includes some development tools:

    docker exec wp-headless composer --help
    docker exec wp-headless phpcbf --help
    docker exec wp-headless phpcs --help
    docker exec wp-headless phpunit --help
    docker exec wp-headless wp --info

Apache/PHP logs are available via `docker-compose logs -f wp-headless`.

## Database

The `db-headless` container exposes MySQL on host port `3307`:

    mysql -uwp_headless -pwp_headless -h127.0.0.1 -P3307 wp_headless

You can also run a mysql shell on the container:

    docker exec db-headless mysql -hdb-headless -uwp_headless -pwp_headless wp_headless

## Reinstall/Import

Reinstall WordPress from scratch:

    docker exec wp-headless sh -c 'wp db reset && install_wordpress'

Import data from a mysqldump with `mysql`:

    docker exec db-headless mysql -hdb-headless -uwp_headless -pwp_headless wp_headless < example.sql
    docker exec wp-headless wp search-replace https://example.com http://localhost:8080

## Migrate DB Pro:

First set `MIGRATEDB_LICENSE` & `MIGRATEDB_FROM` in `.env` and recreate containers to enact the changes.

    docker-compose up -d

Then run the import script:

    docker exec wp-headless migratedb_import

If you need more advanced functionality check out the available WP-CLI commands:

    docker exec wp-headless wp help migratedb

## Extend the REST and GraphQL APIs

At this point you can start setting up custom fields in the WordPress admin, and if necessary, creating [custom REST API endpoints](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/) in the Postlight Headless WordPress Starter theme.

The primary theme code is located in `wordpress/wp-content/themes/postlight-headless-wp`.

You can also [modify and extend the GraphQL API](https://wpgraphql.com/docs/getting-started/about), An example of creating a Custom Type and registering a Resolver is located in: `wordpress/wp-content/themes/postlight-headless-wp/inc/graphql`.

## REST & GraphQL JWT Authentication

JWT Authentication requires some modifications in the `.htaccess` file.

For the REST JWT implementation, add the following:

```
RewriteEngine on
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
```

For the GraphQL JWT implementation, add the following:

```
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

Use something like the [WordPress Salt generator](https://api.wordpress.org/secret-key/1.1/salt/) to generate a secret for JWT, then define it in `wp-config.php`

For the REST API:

    define('JWT_AUTH_SECRET_KEY', 'your-secret-here');

For the GraphQL API:

    define( 'GRAPHQL_JWT_AUTH_SECRET_KEY', 'your-secret-here');

Make sure to read [JWT REST](https://github.com/Tmeister/wp-api-jwt-auth) and [JWT GraphQL](https://github.com/wp-graphql/wp-graphql-jwt-authentication) documentation for more info.

## Linting

Don't forget to lint your code as you go:

    docker exec -w /var/www/html/wp-content/themes/postlight-headless-wp wp-headless phpcs

You may also attempt to auto fix lint errors:

    docker exec -w /var/www/html/wp-content/themes/postlight-headless-wp wp-headless phpcbf

## Hosting

Most WordPress hosts don't also host Node applications, so when it's time to go live, you will need to find a hosting service for the frontend.

That's why we've packaged the frontend app in a Docker container, which can be deployed to a hosting provider with Docker support like Amazon Web Services or Google Cloud Platform. For a fast, easier alternative, check out [Now](https://zeit.co/now).

## Troubleshooting Common Errors

**Breaking Change Alert - Docker**

If you had the project already setup and then updated to a commit newer than `99b4d7b`, you will need to go through the installation process again because the project was migrated to Docker.
You will need to also migrate MySQL data to the new MySQL db container.

**Docker Caching**

In some cases, you need to delete the `wp-headless` image (not only the container) and rebuild it.

**CORS errors**

If you have deployed your WordPress install and are having CORS issues be sure to update `/wordpress/wp-content/themes/postlight-headless-wp/inc/frontend-origin.php` with your frontend origin URL.

See anything else you'd like to add here? Please send a pull request!

---

🔬 A Labs project from your friends at [Postlight](https://postlight.com). Happy coding!
