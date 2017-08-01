# Use WordPress Coding Standards

Adapted from:
https://rezzz.com/php-codesniffer-with-wordpress-coding-standards-and-atom/

* Install Composer https://getcomposer.org/download/
* Install phpcs 2.9.1 via Composer  `php composer.phar global require "squizlabs/php_codesniffer=2.9.1"` (brew won't install a backversion, and phpcs 3.0 doesn't work with Atom correctly as of July 31, 2017) https://github.com/squizlabs/PHP_CodeSniffer
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

