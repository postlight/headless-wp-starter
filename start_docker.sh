#!/bin/bash

service mysql start

if [ ! -d "./data/wp_headless" ]; then
  robo wordpress:setup --docker

  if [ $? -ne 0 ]; then
    exit 1;
  fi
fi

cd wordpress
wp server --host=0.0.0.0 --allow-root
