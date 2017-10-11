#!/bin/bash

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
