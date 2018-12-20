![WordPress + React Starter Kit](frontend/static/images/wordpress-plus-react-header.png)

Postlight's Headless WordPress + React Starter Kit is an automated toolset that will spin up two things:

1.  A WordPress backend that serves its data via the [WP REST API](https://developer.wordpress.org/rest-api/) and [GraphQL](http://graphql.org/) (**new!**).
2.  A server-side rendered React frontend using [Next.js](https://github.com/zeit/next.js/).

You can read all about it in [this handy introduction](https://trackchanges.postlight.com/introducing-postlights-wordpress-react-starter-kit-a61e2633c48c).

**What's inside:**

*   An automated installer script which bootstraps a core WordPress installation that provides an established, stable REST API.
*   A plugin which exposes a newer, in-progress [GraphQL API for WordPress](https://wpgraphql.com/).
*   The WordPress plugins you need to set up custom post types and custom fields ([Advanced Custom Fields Pro](https://www.advancedcustomfields.com/) and [Custom Post Type UI](https://wordpress.org/plugins/custom-post-type-ui/)).
*   Plugins which expose those custom fields and WordPress menus in the [WP REST API](https://developer.wordpress.org/rest-api/) ([ACF to WP API](https://wordpress.org/plugins/acf-to-wp-api/) and [WP-REST-API V2 Menus](https://wordpress.org/plugins/wp-rest-api-v2-menus/)).
*   All the starter WordPress theme code and settings headless requires, including pretty permalinks, CORS `Allow-Origin` headers, and useful logging functions for easy debugging.
*   A mechanism for easily importing data from an existing WordPress installation anywhere on the web using [WP Migrate DB Pro](https://deliciousbrains.com/wp-migrate-db-pro/) and its accompanying plugins (license required).
*   A starter frontend React app powered by [Next.js](https://learnnextjs.com/).
*   A [Docker](https://www.docker.com/) container and scripts to manage it, for easily running the frontend React app and backend locally or deploying it to any hosting provider with Docker support.

Let's get started.

## WordPress Backend

Before you install WordPress, make sure you have [Docker](https://www.docker.com) installed. On Linux, you might need to install [docker-compose](https://docs.docker.com/compose/install/#install-compose) separately.

### Install

The following commands will get WordPress running on your machine using Docker, along with the WordPress plugins you'll need to create and serve custom data via the WP REST API.

```zsh
> docker-compose up -d
```
- Attach to the `wp-headless` container.
```zsh
> docker exec -it wp-headless /bin/bash
```
- Setup headless wordpress by running yarn
```zsh
> yarn install
```
When the installation process completes successfully:

*   The WordPress REST API is available at [http://localhost:8080](http://localhost:8080)
*   The WordPress GraphQL API is available at [http://localhost:8080/graphql](http://localhost:8080/graphql)
*   The WordPress admin is at [http://localhost:8080/wp-admin/](http://localhost:8080/wp-admin/) default login credentials `nedstark` / `winteriscoming`

### Import Data (Optional)

To import data and media from a live WordPress installation, you can use the Migrate DB Pro plugin, which is already installed. To do so, in the `robo.yml` file, set the plugin license and source install. Run `robo wordpress:setup`, then run `robo wordpress:import` to pull in the data.

### Extend the REST and GraphQL APIs

At this point you can start setting up custom fields in the WordPress admin, and if necessary, creating [custom REST API endpoints](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/) in the Postlight Headless WordPress Starter theme. You can also [modify and extend the GraphQL API](https://wpgraphql.com/docs/getting-started/about).

The primary theme code is located in `wordpress/wp-content/themes/postlight-headless-wp`. Remember to [lint your code](README-linting.md) as you go.

## React Frontend

To spin up the frontend client app, run the following commands:

```zsh
> docker exec -it wp-headless /bin/bash
> cd frontend && yarn install && yarn start
```

The Next.js app will be running on [http://localhost:3000](http://localhost:3000).

### Deploying

If you're hosting WordPress on a traditional WordPress hosting provider like BlueHost, DreamHost, or SiteGround, when it's time to go live, you will likely need to find a hosting service for the frontend. That's why we've packaged the frontend app in a Docker container, which can be deployed to a hosting provider with Docker support like Amazon Web Services or Google Cloud Platform or for a fast, easier alternative check out [Now](https://zeit.co/now).

To run the Docker container locally:

1.  In `frontend/config.js`, replace `localhost:8080` with your publicly-accessible WordPress installation's domain name or IP address.
2.  Start the container by running this command:

```zsh
> yarn run deploy
```

Or, you can deploy both the WordPress application and frontend to a cloud platform and leverage that cloud's SQL and media storage services. There's a large number of possible options you could use, including:

- App Engine PaaS on Google Cloud Platform ([setup guide](https://computerlab.io/2018/12/20/deploying-headless-wordpress-on-gcp/)
- Elastic Beanstalk PaaS on AWS
- Managed Kubernetes services like GKE / AKS
- PaaSes / VMs provided by any cloud

## Troubleshooting Common Errors
**Breaking Change Alert - Docker**

If you had the project already setup and then updated to a commit newer than `99b4d7b`, you will need to go through the [installation](https://github.com/postlight/headless-wp-starter/tree/feat-docker#install) process again because the project was migrated to Docker.
You will need to also migrate MySQL data to the new MySQL db container.

**CORS errors**

If you have deployed your WordPress install and are having CORS issues be sure to update `/wordpress/wp-content/themes/postlight-headless-wp/inc/frontend-origin.php` with your frontend origin URL.

See anything else you'd like to add here? Please send a pull request!

---

Made with ❤️ by [Postlight](https://postlight.com). Happy coding!
