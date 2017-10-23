#!/bin/bash

if [[ "$OSTYPE" == "linux-gnu" ]]; then
    # Install mysql-server and preset root password
    sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
    sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
    sudo apt-get -y install mysql-server
    # Install PHP and PHP MySQL Plugin
    sudo apt-get -y install php php-mysql
    # Download and install wp-cli
    wget https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    sudo chmod +x wp-cli.phar
    sudo mv wp-cli.phar /usr/local/bin/wp
    # Download and install robo
    wget http://robo.li/robo.phar
    sudo chmod +x robo.phar
    sudo mv robo.phar /usr/bin/robo
    # Start MySQL Server
    sudo service mysql start
elif [[ "$OSTYPE" == "darwin"* ]]; then
    # Download and install wp-cli
    brew install homebrew/php/wp-cli
    # Download and install robo
    brew install homebrew/php/robo
    # Install mysql-server
    brew install mysql
    # Start mysql-server
    mysql.server start
    # Set the password to root to be compatible with the world
    mysqladmin -u root password root
else
    echo "Sorry, this installation script only works on Mac OS X and Ubuntu Linux. Looks like your operating system is $OSTYPE."
fi
