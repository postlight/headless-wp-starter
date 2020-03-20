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

```
elm-app start
```

Open `http://localhost:3000/` and you can develop with hot-reload.

### Production Build

```
elm-app build
```

Bundle and optimize the app and put it inside `build` folder.

### Deploy

Whenever pull requests are merged into `master` branch, it'll trigger deployment pipeline to release onto production.
