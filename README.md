# Official Website of Japan Insider

## Tech Stack

- Landing page: [Elm](https://elm-lang.org/)
- Article page: [Next.js](https://github.com/zeit/next.js/)
- Docker
- Wordpress Headless docker image by [headless-wp-starter](https://github.com/postlight/headless-wp-starter)
- nginx
- mariaDB

## Landing Page

Under `landing` folder, the whole application is built with [create-elm-app](https://github.com/halfzebra/create-elm-app).

### Development

- Whole website

  1. In order to enable **https** locally, we need to generate self-signed certificates by running this script locally (**ref**: https://letsencrypt.org/docs/certificates-for-localhost/):
     ```bash
     ❯ ./create-local-ssl-cert.sh
     ```
  2. Drag those certificates to your keychain and toggle `Trust All` option (**ref**: https://www.humankode.com/asp-net-core/develop-locally-with-https-self-signed-certificates-and-asp-net-core).
  3. Then, launch the whole application by running:
     ```bash
     ❯ docker-compose up -d
     ```
     Open `https://localhost/` and you can see landing page.
     Open `http://localhost:3000/` and you can see frontend page.

- Landing Page only

  ```bash
  ❯ elm-app start
  ```
  Open `http://localhost` and you can develop with hot-reload.

### Production Build

```bash
❯ elm-app build
```

Bundle and optimize the app and put it inside `build` folder.

### Deploy

Whenever pull requests are merged into `master` branch, it'll trigger deployment pipeline to release onto production.
