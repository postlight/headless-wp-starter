#!/bin/bash

if [[ "$OSTYPE" == "darwin"* ]]; then
    # If there's no password set for MySQL (first-time install), add one and cleanup
    if mysql -uroot -e "" > /dev/null 2>&1; then
        mysql -uroot <<_EOF_
          ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';
          DELETE FROM mysql.user WHERE User='';
          DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
          DROP DATABASE IF EXISTS test;
          DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
          FLUSH PRIVILEGES;
_EOF_
    fi
fi
