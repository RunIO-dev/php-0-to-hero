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
php shelf/bin/console shelf:password Amira
```

Then serve it, and open http://localhost:8000:

```
docker compose up web
```

Run its tests (chapter 77; they use their own database, in `var/`):

```
vendor/bin/phpunit -c shelf
```

`php shelf/bin/console list shelf` shows its commands. Its settings are in
`.env` (chapter 65); put changes for your computer into `.env.local`, which
Git ignores.
