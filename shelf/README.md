# Shelf

The project that grows through the course: a small lending library with books,
members and loans. This folder is its newest state; the state after
chapter NN is in `code/NN-slug/shelf/`, for every chapter that changes it.

Since chapter 36, Shelf is a Composer project with its classes in `src/`.
Since chapter 66, it needs one package, `psr/log`. Install it, with the
autoloader, once after each pull, then run Shelf and its tests from the
repository folder:

```
composer install --working-dir=shelf
php shelf/shelf.php
vendor/bin/phpunit -c shelf
```

Its settings are in `.env` (chapter 65); put changes for your computer
into `.env.local`, which Git ignores.
