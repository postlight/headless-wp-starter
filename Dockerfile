FROM ubuntu:18.04

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get install -yq curl php gnupg wget sudo lsb-release debconf-utils less

RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app

COPY install.sh /usr/src/app/install.sh
COPY docker/install_php_extensions.sh /usr/src/app/install_php_extensions.sh

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo 'deb https://dl.yarnpkg.com/debian/ stable main' | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update && apt-get install -yq yarn

RUN ./install.sh
RUN ./install_php_extensions.sh

RUN wget -c https://dev.mysql.com/get/mysql-apt-config_0.8.10-1_all.deb
RUN dpkg -i mysql-apt-config_0.8.10-1_all.deb
RUN apt-get update
RUN sudo a2enmod rewrite
RUN chown -R www-data:www-data /var/www/html/

COPY mysql_config.sh RoboFile.php wp-cli.yml robo.yml /var/www/html/
COPY docker/000-default.conf /etc/apache2/sites-enabled/
COPY docker/ports.conf /etc/apache2/
WORKDIR /var/www/html

RUN apt-get update

CMD apachectl -D FOREGROUND
