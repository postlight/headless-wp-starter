# Official Website of Japan Insider

## Tech Stack

- Landing page: [elm](https://elm-lang.org/)
- Article page: [next.js](https://github.com/zeit/next.js/)
- Docker
- Wordpress Headless docker image by [headless-wp-starter](https://github.com/postlight/headless-wp-starter)
- nginx
- mariaDB

## Development

### Article Page

Under `frontend` folder, the whole application is built with [next.js](https://github.com/zeit/next.js/).

#### Prerequisite

- Frontend service (`frontend` in `docker-compose.yml`)
- Wordpress Headless service (`wp-headless` in `docker-compose.yml`)
- Database service (`db-headless` in `docker-compose.yml`)

#### Launch all services

```
 docker-compose -f docker-compose-local.yml up
```

Landing Page: Open http://localhost
Article Page: Open http://localhost:3000

#### Feed database with production data (for showing articles)

1. Log in to the machine

```
ssh -i $PRIVATE_KEY ubuntu@$MACHINE_IP
```

2. Dump the database

```
docker exec db-headless /usr/bin/mysqldump -u $USERNAME --password=$PASSWORD wp_headless > $BACKUP_SQL_NAME.sql
```

3. Copy the backup file to the host machine

```
scp -i $PRIVATE_KEY -r ubuntu@$MACHINE_IP:/home/ubuntu/$BACKUP_SQL_NAME.sql ~/Desktop/$BACKUP_SQL_NAME.sql
```

After this, you can see all articles as production website has.

### Landing Page

Under `landing` folder, the whole application is built with [create-elm-app](https://github.com/halfzebra/create-elm-app).

```

elm-app start

```

Open `http://localhost:3000/` and you can develop with hot-reload.

### Production Build

```

elm-app build

```

Bundle and optimize the app and put it inside `build` folder.

## Deploy

Whenever pull requests are merged into `master` branch, it'll trigger deployment pipeline to release onto production.

```

```
