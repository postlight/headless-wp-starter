# Headless WordPress Starter Kit

The development setup utilizes [WP-CLI](http://wp-cli.org/). Before we can start development we need to install a few programs.

_Note: This environment has only been tested under OS X._

## Install Required Software

Using Homebrew, please install WP-CLI, Robo and MySQL:

```bash
brew install homebrew/php/wp-cli
brew install homebrew/php/robo
brew install mysql
```

## Start MySQL

Once you have the necessary software installed, start a MySQL server:

```
mysql.server start
```

## Set Up WordPress

```
robo wordpress:setup
```

## Start WordPress

```
wp server
```

The site will be running at [http://localhost:3000](http://localhost:3000).

## Develop

At this point you can start using and changing the Postlight Headless WordPress Starter theme.

* Site is running at [http://localhost:3000](http://localhost:3000)
* WordPress admin is at [http://localhost:3000/wp-admin/](http://localhost:3000/wp-admin/)  admin / decapitate
* Primary theme code is located in `wordpress/wp-content/themes/postlight-headless`

To turn on BrowserSync, which automatically reloads pages as you save changes to PHP files and static assets, do the following:

* Be sure to run ``npm install`` at least once
* Start WP server with ``wp server``
* Run ``gulp``

At this point the gulp task in your terminal will run continuously, watching for file changes and auto-reloading your browser when they do.

### Use WordPress Coding Standards

https://rezzz.com/php-codesniffer-with-wordpress-coding-standards-and-atom/

* Install Composer https://getcomposer.org/download/
* Install phpcs 2.9.1 via Composer  `php composer.phar global require "squizlabs/php_codesniffer=2.9.1"` (brew won't install a backversion, and phpcs 3.0 doesn't work with Atom correctly) https://github.com/squizlabs/PHP_CodeSniffer
* Install WordPress Coding Standards

```
git clone -b master https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git wpcs

#Add WordPress standards to PHPCS config
phpcs -i #shows the libraries installed
phpcs --config-set installed_paths <path to dir that you cloned>/wpcs
```

* Install Atom's package linter-phpcs
* Set executable path to /Users/ginatrapani/.composer/vendor/bin/phpcs
* Set Code Standard to WordPress-VIP

Happy coding!
