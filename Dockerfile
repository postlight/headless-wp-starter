FROM ubuntu:18.04

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update
RUN apt-get install -yq curl php gnupg wget sudo lsb-release debconf-utils

RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app

COPY install.sh /usr/src/app/install.sh

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo 'deb https://dl.yarnpkg.com/debian/ stable main' | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update
RUN apt-get install -yq yarn

RUN ./install.sh

RUN echo 'mysql-community-server mysql-community-server/root-pass password root' | debconf-set-selections
RUN echo 'mysql-community-server mysql-community-server/re-root-pass password root' | debconf-set-selections
RUN echo 'mysql-community-server mysql-server/default-auth-override select Use Legacy Authentication Method (Retain MySQL 5.x Compatibility)' | debconf-set-selections
RUN wget -c https://dev.mysql.com/get/mysql-apt-config_0.8.10-1_all.deb
RUN dpkg -i mysql-apt-config_0.8.10-1_all.deb
RUN apt-get update
RUN apt-get -qy install mysql-server

EXPOSE 8080
CMD [ "./start_docker.sh" ]
