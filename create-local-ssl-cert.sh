#!/bin/bash

# This is the script create dummy certificate for localhost

domains=(www.japaninsider.co)
data_path="./certbot"

if [ -d "$data_path" ]; then
  read -p "Existing data found for $domains. Continue and replace existing certificate? (y/N) " decision
  if [ "$decision" != "Y" ] && [ "$decision" != "y" ]; then
    exit
  fi
fi

echo "### Creating dummy certificate for localhost ..."
path="$data_path/conf/live/$domains"
mkdir -p "$path"
openssl req -x509 -out "$path/fullchain.pem" -keyout "$path/privkey.pem" \
  -newkey rsa:2048 -nodes -sha256 \
  -subj '/CN=localhost' -extensions EXT -config <( \
   printf "[dn]\nCN=localhost\n[req]\ndistinguished_name = dn\n[EXT]\nsubjectAltName=DNS:localhost\nkeyUsage=digitalSignature\nextendedKeyUsage=serverAuth")

echo "Now you can find your ssl certificates under $path"
