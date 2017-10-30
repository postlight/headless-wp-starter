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
    # If there's no password set for MySQL (first-time install), add one and cleanup
    if mysql -uroot; then
        mysql -uroot <<_EOF_
          UPDATE mysql.user SET authentication_string=PASSWORD('root') WHERE User='root';
          DELETE FROM mysql.user WHERE User='';
          DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
          DROP DATABASE IF EXISTS test;
          DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
          FLUSH PRIVILEGES;
_EOF_
    fi
else
    echo "Sorry, this installation script only works on Mac OS X and Ubuntu Linux. Looks like your operating system is $OSTYPE."
fi
