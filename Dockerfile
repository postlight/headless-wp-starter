FROM wordpress

RUN mv "$PHP_INI_DIR"/php.ini-development "$PHP_INI_DIR"/php.ini

# install_wordpress.sh dependencies
RUN apt-get update; \
	apt-get install -y mysql-client netcat sudo

# php intl
RUN apt-get install -y libicu-dev; \
	docker-php-ext-install intl

# php memcached
RUN apt-get install -y libmemcached-dev zlib1g-dev; \
	pecl install memcached; \
	docker-php-ext-enable memcached

# php xdebug
RUN pecl install xdebug; \
	docker-php-ext-enable xdebug

# wp-cli
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar; \
	chmod +x wp-cli.phar; \
	mv wp-cli.phar /usr/local/bin/wp

# php-cs-fixer
RUN curl -L https://cs.symfony.com/download/php-cs-fixer-v2.phar -o php-cs-fixer; \
	chmod +x php-cs-fixer; \
	mv php-cs-fixer /usr/local/bin/php-cs-fixer

# phpcs & phpcbf
RUN curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar; \
	curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar; \
	chmod a+x phpcs.phar phpcbf.phar; \
	mv phpcs.phar /usr/local/bin/phpcs; \
	mv phpcbf.phar /usr/local/bin/phpcbf

# composer
RUN curl -L https://raw.githubusercontent.com/composer/getcomposer.org/master/web/installer | php; \
	mv composer.phar /usr/local/bin/composer
