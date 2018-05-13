# Lint Your Code

This repository uses consistent PHP code style throughout, and enforces it using [PHPCS](https://github.com/squizlabs/PHP_CodeSniffer). The standards in use are a combination of PSR-2 and WordPress coding styles, and are defined in the `phpcs.xml` file.

To lint your code, make sure you have `phpcs` installed, as well as the WordPress coding standards. Once you do, run `yarn lint` to lint your code.

## Install

To lint your PHP code, you'll need to install PHPCS and the WordPress coding standards.

### PHPCS

To install the latest stable version of PHPCS with Homebrew on the Mac, run:

`brew install homebrew/php/php-code-sniffer`

### WordPress Coding Standards

To install the WordPress coding standards and add them to the PHPCS config, run the following commands:

```
git clone -b master https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git wpcs

# Add WordPress standards to PHPCS config
phpcs -i # shows the libraries installed
phpcs --config-set installed_paths <path to dir that you cloned>/wpcs
```
## Run the Linter

Run the linter at the command line using the following command:

`yarn lint`

Note that the linter only checks the headless theme and Robo file. It does not lint all of WordPress.

### Lint on the fly using Atom

Atom's PHPCS linter doesn't currently work with PHPCS v3. To lint on the fly in Atom, you must install v2.9 of PHPCS, and a few Atom packages to work with it.

* [Install Composer](https://getcomposer.org/download/).
* Install phpcs 2.9.1 via Composer by running `php composer.phar global require "squizlabs/php_codesniffer=2.9.1"`. (Brew won't install a backversion, and phpcs 3.0 doesn't work with Atom correctly as of July 31, 2017.)
* Install the WordPress Coding Standards and configure PHPCS to use them, as detailed in the [section above](#wordpress-coding-standards).
* Install the following Atom packages: [linter](https://atom.io/packages/linter), [linter-phpcs](https://atom.io/packages/linter-phpcs), and [linter-ui-default](https://atom.io/packages/linter-ui-default).
* In the linter-phpcs packages' settings, set the executable path to `/Users/[username]/.composer/vendor/bin/phpcs`.
* Check "Search for Configuration File" to make sure the package uses the defined `phpcs.xml` file. Restart Atom.

From there, the linter UI will show you lint warnings and errors as you code. (Instructions adapted from [Jason Resnik](https://rezzz.com/php-codesniffer-with-wordpress-coding-standards-and-atom/).)
