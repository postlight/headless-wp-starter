FROM ubuntu:18.04

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update
RUN apt-get install -yq curl gnupg wget sudo lsb-release debconf-utils less

RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo 'deb https://dl.yarnpkg.com/debian/ stable main' | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update
RUN apt-get install -yq yarn

RUN apt-get update

CMD yarn install && yarn start
