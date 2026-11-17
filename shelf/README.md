# Shelf

The project that grows through the course: a small lending library with books,
members and loans. This folder is its newest state; the state after
chapter NN is in `code/NN-slug/shelf/`, for every chapter that changes it.

Since chapter 36, Shelf is a Composer project with its classes in `src/`.
Write its autoloader once (`--dev` includes the tests' classes, chapter 41),
then run it and its tests from the repository folder:

```
composer dump-autoload --dev --working-dir=shelf
php shelf/shelf.php
vendor/bin/phpunit -c shelf
```
