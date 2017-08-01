# Headless WordPress Starter Kit

Postlight's Headless WordPress Starter Kit is an automated toolset that will spin up two things:

1. a WordPress backend that serves its data via the [WP REST API](https://developer.wordpress.org/rest-api/)
2. a server-side rendered React frontend using Next.js

## Backend

The following setup will get WordPress running locally on your machine, along with the WordPress plugins you'll need to create and serve custom data via the WP REST API.

### Set Up WordPress

_Note: This environment has only been tested under OS X._


1. Using Homebrew, please install [WP-CLI](http://wp-cli.org/), [Robo](http://robo.li/) and MySQL:
```
brew install homebrew/php/wp-cli
brew install homebrew/php/robo
brew install mysql
```

2. Once you have the necessary software installed, start a MySQL server:
```
mysql.server start
```

3. Install and set up WordPress:
```
robo wordpress:setup
```

4. Start the WordPress server.
```
wp server
```

The site will be running at [http://localhost:8080](http://localhost:8080).

### Develop WordPress

At this point you can start setting up custom fields, and if necessary, creating [custom REST API endpoints](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/) in the Postlight Headless WordPress Starter theme. When you do, be sure to [use WordPress coding standards](https://github.com/postlight/headless-wp-starter/blob/master/wordpress/wp-content/themes/postlight-headless-wp/README.md).


* The backend is running at [http://localhost:8080](http://localhost:8080)
* WordPress admin is at [http://localhost:8080/wp-admin/](http://localhost:8080/wp-admin/)  nedstark / winteriscoming
* Primary theme code is located in `wordpress/wp-content/themes/postlight-headless-wp`


## Frontend

TK

Happy coding!
