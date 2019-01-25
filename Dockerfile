FROM wordpress

RUN mv "$PHP_INI_DIR"/php.ini-development "$PHP_INI_DIR"/php.ini

RUN apt-get update; \
	apt-get install -y netcat

# intl
RUN apt-get install -y libicu-dev; \
	docker-php-ext-install intl

# memcached
RUN apt-get install -y libmemcached-dev zlib1g-dev; \
	pecl install memcached; \
	docker-php-ext-enable memcached

# xdebug
RUN pecl install xdebug; \
	docker-php-ext-enable xdebug

# wp-cli
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar; \
	chmod +x wp-cli.phar; \
	mv wp-cli.phar /usr/local/bin/wp

# yarn
#RUN apt-get install -yq gnupg; \
#	curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -; \
#	echo 'deb https://dl.yarnpkg.com/debian/ stable main' | tee /etc/apt/sources.list.d/yarn.list; \
#	apt-get update; \
#	apt-get install -yq yarn
