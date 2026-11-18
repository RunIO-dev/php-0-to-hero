# Shelf

The project that grows through the course: a small lending library with books,
members and loans. This folder is its newest state; the state after
chapter NN is in `code/NN-slug/shelf/`, for every chapter that changes it.

Since chapter 72, Shelf is a Symfony 7.4 app. Install its packages once after
each pull, make its database, and fill it, from the repository folder:

```
composer install --working-dir=shelf
php shelf/bin/console doctrine:migrations:migrate --no-interaction
php shelf/bin/console shelf:import
php shelf/bin/console shelf:catalogue shelf/catalogue.json
php shelf/bin/console shelf:password Amira
```

`shelf:catalogue` (chapter 81) adds about fifty classic books, so search and
pages have something to show; it adds only titles that aren't there yet.

Then serve it, and open http://localhost:8000:

```
docker compose up web
```

Run its checks, as CI does (chapter 79): the tests (chapter 77; they use their
own database, in `var/`), PHPStan and the code style:

```
composer --working-dir=shelf check
```

Try the production containers on your computer (chapter 78), then open
https://localhost; the browser warns once about Caddy's certificate for
`localhost`:

```
cd shelf
SHELF_APP_SECRET=$(openssl rand -hex 32) docker compose -f compose.prod.yaml up -d --build
docker compose -f compose.prod.yaml exec app php bin/console shelf:import
```

Deploying to a server, and CI, are in chapter 79 (`.github/workflows/ci.yml`,
`deploy/deploy.sh`).

`php shelf/bin/console list shelf` shows its commands. Its settings are in
`.env` (chapter 65); put changes for your computer into `.env.local`, which
Git ignores.
