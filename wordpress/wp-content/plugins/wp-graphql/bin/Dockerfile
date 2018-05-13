ARG PHP_VERSION

FROM wordpress:cli-php${PHP_VERSION}

USER root

RUN apk --no-cache --update add bash subversion sudo

RUN docker-php-ext-install pdo_mysql

RUN echo 'date.timezone = "UTC"' > /usr/local/etc/php/conf.d/timezone.ini

ENV WP_CLI_ARGS="--allow-root"

WORKDIR /project
