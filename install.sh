#!/bin/bash

if [[ "$OSTYPE" == "linux-gnu" ]]; then
    # Install PHP and PHP MySQL Plugin
    sudo apt-get -y install php php-mysql mysql-client
    # Download and install wp-cli
    wget https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    sudo chmod +x wp-cli.phar
    sudo mv wp-cli.phar /usr/local/bin/wp
    # Download and install robo
    wget http://robo.li/robo.phar
    sudo mv robo.phar /usr/bin/robo && sudo chmod +x /usr/bin/robo
elif [[ "$OSTYPE" == "darwin"* ]]; then
    # Download and install wp-cli
    brew install wp-cli
    # Download and install wget
    brew install wget
    # Download and install robo
    wget http://robo.li/robo.phar
    sudo mv robo.phar /usr/local/bin/robo && sudo chmod +x /usr/local/bin/robo
else
    echo "Sorry, this installation script only works on Mac OS X and Ubuntu Linux. Looks like your operating system is $OSTYPE."
fi
