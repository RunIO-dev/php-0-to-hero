# Shelf

The project that grows through the course: a small lending library with books,
members and loans. This folder is its newest state; the state after
chapter NN is in `code/NN-slug/shelf/`, for every chapter that changes it.

Since chapter 36, Shelf is a Composer project with its classes in `src/`.
Write its autoloader once, then run it from the repository folder:

```
composer dump-autoload --working-dir=shelf
php shelf/shelf.php
```
