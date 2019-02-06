FROM wordpress

RUN sed -i 's/80/8080/' /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf

RUN mv "$PHP_INI_DIR"/php.ini-development "$PHP_INI_DIR"/php.ini

# install_wordpress.sh dependencies
RUN apt-get update; \
	apt-get install -yq mysql-client netcat sudo

# php-cs-fixer
RUN curl -sL https://cs.symfony.com/download/php-cs-fixer-v2.phar -o php-cs-fixer; \
	chmod +x php-cs-fixer; \
	mv php-cs-fixer /usr/local/bin/

# phpcs & phpcbf
RUN curl -sL https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar -o phpcs; \
	curl -sL https://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar -o phpcbf; \
	chmod +x phpcs phpcbf; \
	mv phpcs phpcbf /usr/local/bin/

# phpunit
RUN curl -sL https://phar.phpunit.de/phpunit-6.5.phar -o phpunit; \
	chmod +x phpunit; \
	mv phpunit /usr/local/bin/

# composer
RUN curl -sL https://raw.githubusercontent.com/composer/getcomposer.org/master/web/installer | php; \
	mv composer.phar /usr/local/bin/composer

# wp-cli
RUN curl -sL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o wp; \
	chmod +x wp; \
	mv wp /usr/local/bin/; \
	mkdir /var/www/.wp-cli; \
	chown www-data:www-data /var/www/.wp-cli

EXPOSE 8080
