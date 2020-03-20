# Plan

## To run on local environment
1. Need self sign keys and cert to use https locally
2. Write a script to automate 
   1. create localhost.key and localhost.crt (https://letsencrypt.org/docs/certificates-for-localhost/)
   ```
   openssl req -x509 -out localhost.crt -keyout localhost.key -trustout\
  -newkey rsa:2048 -nodes -sha256 \
  -subj '/CN=localhost' -extensions EXT -config <( \
   printf "[dn]\nCN=localhost\n[req]\ndistinguished_name = dn\n[EXT]\nsubjectAltName=DNS:localhost\nkeyUsage=digitalSignature\nextendedKeyUsage=serverAuth")
   ```
   2. Add instruction to readme on how to trust self signed cert on mac (https://www.humankode.com/asp-net-core/develop-locally-with-https-self-signed-certificates-and-asp-net-core)
3. How to separate from local nginx config and production one? (by creating two different conf?)

4. needs to install `create-elm-app` and run `elm-app build` to run locally (on production, I run this build inside circle ci pipeline)
5. Probably I need to wrap landing page as docker image, and pull that docker image from production machine.