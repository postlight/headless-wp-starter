![Logo](https://d2ffutrenqvap3.cloudfront.net/items/1i0F192I0j3F042t3S3R/logo-250.png)

# WPGraphQL 

<a href="https://www.wpgraphql.com" target="_blank">Website</a> • <a href="https://wpgraphql.com/docs/getting-started/about/" target="_blank">Docs</a> • <a href="https://wp-graphql.github.io/wp-graphql-api-docs/" target="_blank">ApiGen Code Docs</a> • <a href="https://wpgql-slack.herokuapp.com/" target="_blank">Slack</a>

GraphQL API for WordPress.

[![Build Status](https://travis-ci.org/wp-graphql/wp-graphql.svg?branch=master)](https://travis-ci.org/wp-graphql/wp-graphql)
[![Coverage Status](https://coveralls.io/repos/github/wp-graphql/wp-graphql/badge.svg?branch=master)](https://coveralls.io/github/wp-graphql/wp-graphql?branch=master)

------

## Quick Install
Download and install like any WordPress plugin.
[Details on Install and Activation](https://wpgraphql.com/docs/getting-started/install-and-activate/)

## Documentation

Documentation is being moved [here](https://wpgraphql.com/docs/getting-started/about), but some can still be found [on the Wiki](https://github.com/wp-graphql/wp-graphql/wiki) on this repository.

- Requires PHP 5.5+
- Requires WordPress 4.7+

## Overview
This plugin brings the power of GraphQL to WordPress.

<a href="https://graphql.org" target="_blank">GraphQL</a> is a query language spec that was open sourced by Facebook® in 
2015, and has been used in production by Facebook® since 2012.

GraphQL has some similarities to REST in that it exposes an HTTP endpoint where requests can be sent and a JSON response 
is returned. However, where REST has a different endpoint per resource, GraphQL has just a single endpoint and the
data returned isn't implicit, but rather explicit and matches the shape of the request. 

A REST API is implicit, meaning that the data coming back from an endpoint is implied. An endpoint such as `/posts/` 
implies that the data I will retrieve is data related to Post objects, but beyond that it's hard to know exactly what 
will be returned. It might be more data than I need or might not be the data I need at all. 

GraphQL is explicit, meaning that you ask for the data you want and you get the data back in the same shape that it was 
asked for.

Additionally, where REST requires multiple HTTP requests for related data, GraphQL allows related data to be queried and 
retrieved in a single request, and again, in the same shape of the request without any worry of over or under-fetching 
data.

GraphQL also provides rich introspection, allowing for queries to be run to find out details about the Schema, which is
how powerful dev tools, such as _GraphiQl_ have been able to be created.

## GraphiQL API Explorer
_GraphiQL_ is a fantastic GraphQL API Explorer / IDE. There are various versions of _GraphiQL_
that you can find, including a <a href="https://chrome.google.com/webstore/detail/chromeiql/fkkiamalmpiidkljmicmjfbieiclmeij?hl=en">Chrome Extension</a> but
my recommendation is the _GraphiQL_ desktop app below:

- <a href="https://github.com/skevy/graphiql-app">Download the GraphiQL Desktop App</a>
    - Once the app is downloaded and installed, open the App.
    - Set the `GraphQL Endpoint` to `http://yoursite.com/graphql`. In order for the /graphql endpoint to work, you must have [pretty permalinks](https://codex.wordpress.org/Using_Permalinks/) enabled.
    - You should now be able to browse the GraphQL Schema via the "Docs" explorer
    at the top right. 
    - On the left side, you can execute GraphQL Queries
    
    <img src="https://github.com/wp-graphql/wp-graphql/blob/master/img/graphql-docs.gif?raw=true" alt="GraphiQL API Explorer">

## POSSIBLE BREAKING CHANGES
Please note that as the plugin continues to take shape, there might be breaking changes at any point. Once the plugin reaches a stable 1.0.0 release, breaking changes should be minimized and communicated appropriately if they are required.

## Unit Testing and Code Coverage 

Before anything is merged into the WPGraphQL code base it must pass all tests and have 100% code coverage. 
Travis-CI and Coveralls will check this when you create a pull request to the WPGraphQL repo. 
However, before that happens, you should ensure all of these requirements are met locally. 
The following will help you set up both testing and code coverage in your local environment.

### Prerequisites
To run unit tests and code coverage during development you'll need the following:

* [Composer](https://getcomposer.org/doc/00-intro.md)
    * [php-coveralls](https://github.com/php-coveralls/php-coveralls)
        * `composer global require php-coveralls/php-coveralls`
* [Xdebug](https://xdebug.org/docs/install)

### Test Database
In order for tests to run, you need MySQL setup locally. The test suite will need 2 databases for testing. 
One named `wpgraphql_serve` and the other you can name yourself. 
You can keep these databases around if you like and the test suite will use the existing databases, or you can delete them when you're done testing and the test suite will 
re-install them as needed the next time you run the script to install the tests.

*NOTE*: You'll want the test database to be a true test database, not a database with valuable, existing information. 
The tests will create new data and clear out data, and you don't want to cause issues with a database you're actually using for projects.

### Installing the Test Suite
To install the test suite/test databases, from the root of the plugin directory, in the command line run: 

`bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]`

For example: 

`bin/install-wp-tests.sh wpgraphql_test root password 127.0.0.1 latest`

DEBUGGING: If you have run this command before in another branch you may already have a local copy of WordPress downloaded in your `/private/tmp` directory. 
If this is the case, please remove it and then run the install script again. Without removing this you may receive an error when running phpunit.

#### Local Environment Configuration for Codeception Tests

You may have different local environment configuration than what Travis CI has to run the tests, such as database username/password.

In the `/tests` directory you will find `*.suite.dist.yml` config files for each of the codeception test suites. 

You can copy those files and remove the `.dist` from the filename, and that file will be loaded locally _before_ the `.dist` file.

For example, if you wanted to update the `dbName` or `dbPassword` for your local tests, you could copy `wpunit.suite.dist.yml` to `wpunit.suite.yml` and update the `dbName` or `dbPassword` value to reflect your local database and password.

This file is .gitignored, so it will remain in your local environment but will not be added to the repo when you submit pull requests.

### Running the Tests
The tests are built on top of the Codeception testing framework. 

To run the tests, after you've installed the test suite, as described above, you need to also install the `wp-browser`. 

*@todo*: Make this easier than running all these steps, but for now this is what we've got to do.
Perhaps someone who's more of a Composer expert could lend some advise?:


- `rm -rf composer.lock vendor` to remove all composer dependencies and the composer lock file
- `composer require lucatume/wp-browser --dev` to install the Codeception WordPress deps
- `vendor/bin/codecept run` to run all the codeception tests
    - You can specify which tests to run like: 
        - `vendor/bin/codecept run wpunit`
        - `vendor/bin/codecept run functional`
        - `vendor/bin/codecept run unit`
        - `vendor/bin/codecept run acceptance`


### Testing in Docker

A `docker-compose` file in the root of this repo provides all of the testing prerequisites, allowing you to run
tests in isolation without installing anything locally (besides Docker).

Install dependencies using Composer. Note the `--ignore-platform-reqs` which skips the checks for PHP extensions
inside the barebones Docker container.

```
docker run --rm -v $(pwd):/app composer install --ignore-platform-reqs
```

Now you're ready to start the Docker containers: a PHP container to hold the code under test, a PHP/Apache
container to serve WordPress, and a MariaDB container.

```
docker-compose build
docker-compose up -d
```

Your stack is starting! If this is your first time running that command, it will take a few minutes to download the
referenced Docker images. Note that it takes about 15-30 seconds after this process completes for MariaDB to be
ready to accept connections, so you'll need to wait that long before running the next command.

Now we install the WordPress testing suite inside the container, link the local codebase inside it, and activate it
as a plugin. Note that we are skipping database creation (handled by our `docker-compose` file). The WordPress
version you pass here should match what is specified in `docker-compose.yml`.

```
docker-compose run --rm tests ./bin/install-wp-tests.sh ignored root testing mysql_test 4.9.4 true
```

You now have a stable testing environment. The WPGraphQL codebase is mapped inside your containers and any changes
you make will be reflected almost immediately. We use Codeception Environments (`--env`) to point tests to our
database container instead of `127.0.0.1`. Run your tests and repeat as necessary, e.g.:

```
docker-compose run --rm tests ./vendor/bin/codecept run acceptance --env docker
docker-compose run --rm tests ./vendor/bin/codecept run functional --env docker
docker-compose run --rm tests ./vendor/bin/codecept run wpunit --env docker
```

Code coverage for `wpunit` tests can be generated using `phpdbg`:

```
docker-compose run --rm tests phpdbg -qrr ./vendor/bin/codecept run wpunit --env docker --coverage --coverage-xml
```

If you need to test against a different WordPress version, you will need to destroy your environemnt, update
`docker-compose.yml` and `bin/Dockerfile` to point to the desired version, then recreate your environment.



### Generating Code Coverage
You can generate code coverage for tests by passing `--coverage`, `--coverage-xml` or `--coverage-html` with the tests. 

- `--coverage` will print coverage info to the screen
- `--coverage-xml` will save an XML file that can be used by services like Coveralls or CodeCov
- `--coverage-html` will save the coverage report in an HTML file that you can browse. 

The coverage details will be output to `/tests/_output`

### Running Individual Files 
As you'll note, running all of the tests in the entire test suite can be time consuming. If you would like to run only one test file instead of all of them, simply pass the test file you're trying to test, like so:

`vendor/bin/codecept run wpunit AvatarObjectQueriesTest`

To capture coverage for a single file, you can run the test like so:

`vendor/bin/codecept run wpunit AvatarObjectQueriesTest --coverage`

And you can output the coverage locally to HTML like so: 

`vendor/bin/codecept run wpunit AvatarObjectQueriesTest --coverage --coverage-html`

## Shout Outs
This plugin brings the power of GraphQL (http://graphql.org/) to WordPress.

This plugin is based on the hard work of Jason Bahl, Ryan Kanner, Hughie Devore and Peter Pak of Digital First Media (https://github.com/dfmedia),
and Edwin Cromley of BE-Webdesign (https://github.com/BE-Webdesign).

The plugin is built on top of the graphql-php library by Webonyx (https://github.com/webonyx/graphql-php) and makes use 
of the graphql-relay-php library by Ivome (https://github.com/ivome/graphql-relay-php/)

Special thanks to Digital First Media (http://digitalfirstmedia.com) for allocating development resources to push the 
project forward.

Some of the concepts and code are based on the WordPress Rest API. Much love to the folks (https://github.com/orgs/WP-API/people) 
that put their blood, sweat and tears into the WP-API project, as it's been huge in moving WordPress forward as a 
platform and helped inspire and direct the development of WPGraphQL.

Much love to Facebook® for open sourcing the GraphQL spec (https://facebook.github.io/graphql/), the amazing GraphiQL 
dev tools (https://github.com/graphql/graphiql), and maintaining the JavaScript GraphQL reference 
implementation (https://github.com/graphql/graphql-js)

Much love to Apollo (Meteor Development Group) for their work on driving GraphQL forward and providing a lot of insight 
into how to design GraphQL schemas, etc. Check them out: http://www.apollodata.com/
