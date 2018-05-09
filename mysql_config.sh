#!/bin/bash

if [[ "$OSTYPE" == "darwin"* ]]; then
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
fi
