# 78. Production setup

## Goals

- You will be able to build a production image for a Symfony app: no tools for development, a warmed cache, PHP's production settings.
- You will be able to serve it over HTTPS, with a web server that gets and renews its certificate on its own.
- You will be able to give it its settings and secrets from the server's environment, never from Git.
- You will be able to keep its data in volumes, and its logs where the server collects them.

## Before you start

- Chapter 45: Docker and `compose.yaml`.
- Chapter 64: security headers and HTTPS.
- Chapters 65 and 66: settings from the environment, and logs.
- Chapters 72 to 77: *Shelf* as a Symfony app.

## The idea

On your computer, *Shelf* runs with PHP's built-in server, in debug mode, with every tool installed, and shows an error with its whole stack trace. That is right for development, and wrong on a server in four ways: the built-in server answers one request at a time and has no HTTPS; debug mode is slow and shows strangers what the code looks like inside; the tools are code that nobody needs on the server, and each one is something to keep up to date; and the settings, the secret above all, must not come from files in Git.

A production setup changes each of them:

- **A web server and PHP-FPM.** PHP-FPM ("FastCGI Process Manager") keeps PHP processes ready, and runs many requests at once. In front of it, a web server takes every connection: it serves the files in `public/` itself, sends every other request to PHP-FPM, and speaks HTTPS. *Shelf* uses **Caddy**: it gets a certificate from Let's Encrypt for the site's domain on its own, renews it before it runs out, and sends every `http://` request to `https://`.
- **An image built once.** The Dockerfile installs the packages without the ones for development (`composer install --no-dev`), makes an autoloader that knows every class in advance, and builds Symfony's cache. The server runs this image, and changes nothing in it: a new version of *Shelf* is a new image.
- **The production environment.** `APP_ENV=prod` and `APP_DEBUG=0`: Symfony uses the cache it built, logs errors instead of showing them, and shows visitors *Shelf*'s own error pages. PHP gets its own production settings, `php.ini-production`, plus a few of ours.
- **Settings from the environment.** The server sets real environment variables, and they win over `.env` (chapter 65). The secret comes from the server, never from a file in Git.
- **Data outside the image.** The database and the covers go into a Docker **volume**, which stays when the container is made again from a new image. The logs go to the container's error output, where `docker compose logs`, or the server's own log collector, finds them.

**OPcache** keeps PHP's compiled code in memory, so PHP doesn't read and compile each file on every request. In production the files never change, so OPcache never needs to check them: a new version is a new container, with an empty OPcache.

## In code

The examples are a Composer project of their own (`code/78-production/composer.json`):

<!-- include: code/78-production/composer.json -->

### Settings from the environment

The `.env` of the example, as a developer's computer has it, `code/78-production/.env`:

<!-- include: code/78-production/.env -->

<!-- include: code/78-production/env.php -->

<!-- include: code/78-production/env.out -->

- What the server sets, `APP_ENV` and `DATABASE_URL`, wins; `.env` only fills in what is missing.
- `loadEnv()` also reads `.env.prod`, if there is one: a place for settings that every server shares, and that aren't secret.

### An autoloader for production

<!-- include: code/78-production/classmap.php -->

<!-- include: code/78-production/classmap.out -->

- In development, Composer looks for a class's file in its folder, so a new class works at once.
- `composer dump-autoload --classmap-authoritative` writes the list of every class once, when the image is built. Then the autoloader never looks at the disk for a class that isn't on the list: it is faster, and nothing can be loaded that wasn't there at build time.

## Common mistakes

### Production started as development

<!-- include: code/78-production/debug.bad.yaml -->

✗ Debug mode on a server shows every visitor the stack trace of an error, with paths, queries and settings, and makes every page slower. The secret is in a file in Git: everyone who can read the repository can sign Shelf's links and forge its CSRF tokens.

**Fix:** `APP_ENV=prod` and `APP_DEBUG=0` in the image itself, as *Shelf*'s Dockerfile sets them; the secret from the server's environment, as `compose.prod.yaml` reads it, and a new one for each server: `openssl rand -hex 32`.

### Building on the server

`git pull` and `composer install` on the server mean the server needs Git, Composer and access to every package host; two servers can end up with different packages; and while it runs, half of the files are new and half are old.

**Fix:** build the image once, test it, and run that image on every server. Nothing is installed on a running server.

### Data inside the container

A SQLite file, or uploaded covers, in the container's own files are gone the moment the container is made again from a new image.

**Fix:** a volume for everything that must outlive the container. The image holds only what can be built again.

## Principle check

**KISS:** two containers, one file each for PHP, Caddy and Compose; no orchestration. **Secure by default:** HTTPS with HSTS, no debug, no tools on the server, PHP as `www-data` and not as root, the secret only in the environment. **YAGNI:** one server, SQLite, and Caddy's own certificates: enough for a library; a second server can come when one isn't enough.

## Security

- **HTTPS everywhere.** Caddy answers `http://` only with a redirect to `https://`, and `Strict-Transport-Security` tells browsers to use HTTPS for a year.
- **Least privilege.** PHP runs as `www-data`, and may write only to `var/` and `data/`. A bug that lets an attacker write files can't change the code.
- **Nothing extra.** No Composer, Git, PHPUnit or sample passwords in the image (`.dockerignore`). What isn't there can't be attacked.
- **Errors in the log, never on the page.** `display_errors` is off, `expose_php` is off, and Symfony shows its plain error pages in `prod`.
- **The health of the headers.** Checking *Shelf*'s headers in production found that the security headers of chapter 64 had been missing on every page since chapter 73. See below.

## Shelf

*Shelf* gets a production image and a `compose.prod.yaml`. On your computer, try it with:

```
cd shelf
SHELF_APP_SECRET=$(openssl rand -hex 32) docker compose -f compose.prod.yaml up -d --build
docker compose -f compose.prod.yaml exec app php bin/console shelf:import
```

Then open https://localhost. Caddy makes a certificate for `localhost` itself, so the browser warns once. On a server, `SHELF_HOST=shelf.example` (the site's domain, which points to the server) gets a real certificate.

### The image

`shelf/Dockerfile`:

<!-- include: code/78-production/shelf/Dockerfile -->

- **`vendor`** installs the packages with Composer, which only this stage has. `composer.json` and `composer.lock` are copied first: Docker keeps that layer until they change, so a change to *Shelf*'s code doesn't download every package again.
- **`app`** is the image that runs *Shelf*: the official PHP-FPM image, PHP's production settings, the code and packages from `vendor`, and the cache built by `cache:warmup`.
- **`web`** is Caddy, with its settings and the files of `public/`.

What stays out of the image, `shelf/.dockerignore`:

<!-- include: code/78-production/shelf/.dockerignore -->

PHP's settings, after `php.ini-production`, `shelf/docker/php.ini`:

<!-- include: code/78-production/shelf/docker/php.ini -->

Before PHP-FPM starts, the database gets its migrations, `shelf/docker/entrypoint.sh`:

<!-- include: code/78-production/shelf/docker/entrypoint.sh -->

Docker runs this file as a program, so it must be executable, in Git too: on a disk or a system that doesn't keep the mode, `git update-index --chmod=+x docker/entrypoint.sh` sets it. The course's CI found that it wasn't: the container refused to start with "permission denied".

### HTTPS

`shelf/docker/Caddyfile`:

<!-- include: code/78-production/shelf/docker/Caddyfile -->

`php_fastcgi` passes each request to PHP-FPM with `HTTPS=on`, so Symfony knows the visitor used HTTPS, makes `https://` addresses, and sends the `Secure` session cookie. Caddy and PHP talk FastCGI, not HTTP, so Symfony needs no list of trusted proxies.

### The containers

`shelf/compose.prod.yaml`:

<!-- include: code/78-production/shelf/compose.prod.yaml -->

- `${SHELF_APP_SECRET:?...}` refuses to start without a secret.
- `DATABASE_URL` and `SHELF_COVERS` point into `data/`, the volume.
- `SHELF_LOG: php://stderr`: *Shelf*'s log goes to the container's error output.

### The log as a stream

`FileLogger` (chapter 66) made a folder for its file, and locked it. A stream such as `php://stderr` has no folder and can't be locked, `shelf/src/FileLogger.php`:

<!-- include: code/78-production/shelf/src/FileLogger.php -->

`shelf/tests/FileLoggerTest.php` gets a test for a stream:

<!-- include: code/78-production/shelf/tests/FileLoggerTest.php -->

The log's path is now a whole path, or a stream, `shelf/config/services.yaml`:

<!-- include: code/78-production/shelf/config/services.yaml -->

`.env` gives the whole path, with `%kernel.project_dir%`, which `resolve:` fills in, and one file per environment, so `.env.test` needs no line of its own any more, `shelf/.env`:

<!-- include: code/78-production/shelf/.env -->

`shelf/.env.test`:

<!-- include: code/78-production/shelf/.env.test -->

### The missing headers

The production check showed only `X-Content-Type-Options` on *Shelf*'s pages: no `Content-Security-Policy`, no `Referrer-Policy`. Since chapter 73, `SecurityHeaders` added them only to answers whose `Content-Type` was `text/html`; but a page from `render()` gets its `Content-Type` after this event, when Symfony prepares the answer. Chapter 73's transcript showed it, and nobody noticed. A test now checks the headers of every kind of page, `shelf/tests/Web/PagesTest.php`:

<!-- include: code/78-production/shelf/tests/Web/PagesTest.php -->

And the fix, `shelf/src/EventSubscriber/SecurityHeaders.php`:

<!-- include: code/78-production/shelf/src/EventSubscriber/SecurityHeaders.php -->

`shelf/web.out` shows both headers on every page again.

## Exercises

The exercises are in `exercises/78-production/`. Check your work with:

```
composer exercise 78 beginner
```

1. **Beginner, `settings.php`:** check a production `php.ini` before a deploy: errors logged and never shown, PHP's version hidden, OPcache that doesn't check the files.
2. **Intermediate, `Health.php`:** a health check for a monitor: `200` while the database answers, `503` as problem details when it doesn't, and never the database's error message.
3. **Advanced, `startup.php`:** check the settings a server gives Shelf before it starts: the environment, the secret, the database, the domain.

## Summary

- Production runs a web server and PHP-FPM, not PHP's built-in server.
- The image is built once: packages without the development tools, an authoritative class map, a warmed cache.
- `APP_ENV=prod` and `APP_DEBUG=0`; PHP's production settings; OPcache that never checks the files.
- Settings and secrets come from the server's environment; data lives in volumes; logs go to the error output.
- Caddy gets and renews the certificate, and sends every visitor to HTTPS.

## Further reading

- Symfony documentation, How to deploy a Symfony application: https://symfony.com/doc/7.4/deployment.html
- Symfony documentation, Performance: https://symfony.com/doc/7.4/performance.html
- PHP manual, OPcache configuration: https://www.php.net/manual/en/opcache.configuration.php
- Docker documentation, Multi-stage builds: https://docs.docker.com/build/building/multi-stage/
- Caddy documentation, Automatic HTTPS: https://caddyserver.com/docs/automatic-https
- Caddy documentation, php_fastcgi: https://caddyserver.com/docs/caddyfile/directives/php_fastcgi
