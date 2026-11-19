# 45. Docker for our setup

## Goals

- You will be able to explain what an image and a container are, and why the course's checks run in them.
- You will be able to read the course's `Dockerfile` and `compose.yaml`, line by line.
- You will be able to serve *Shelf* in the browser with `docker compose up web`, and stop it again.
- You will be able to keep a container's port private to your own computer.

## Before you start

- Chapter 2: installing PHP, or using Docker instead.
- Chapter 44: the built-in web server, `php -S`.

## The idea

"It works on my computer" is the oldest problem in programming. Your PHP is 8.6, a colleague's is 8.4; you have the `mbstring` extension, the server doesn't. The code is the same, and the results differ.

**Docker** packs a program together with everything it needs, PHP, extensions, settings, into an **image**: a read-only snapshot of a small Linux system. A **container** is an image that runs, with its own files and its own network, separate from your computer. The same image gives the same PHP everywhere: on your laptop, in CI, on a server.

Two files describe the course's setup:

- the **`Dockerfile`** says how to build the image, step by step, starting from the official PHP image;
- **`compose.yaml`** says which containers to run from it, with which folders, ports and commands. **Docker Compose** reads it: `docker compose run` starts a container for one command, `docker compose up` starts a service that keeps running.

This is how the book checks itself: `composer verify` runs every example in exactly these images, for PHP 8.6 and 8.5. You have used them since chapter 2 if you chose Docker; now you can read them, and use them for the web.

**About the licence.** Docker Engine, which runs containers, is free and open source, and on Linux it is all you need. Docker Desktop, the app for Windows and macOS, is free for personal use, education, open-source projects and small companies; larger companies need a paid subscription. Free alternatives exist that read the same `compose.yaml`, such as Podman (with `podman compose`), Rancher Desktop and, on macOS, Colima. Everything in this course works with any of them.

## In code

### The image: `Dockerfile`

<!-- include: Dockerfile -->

- `ARG PHP=8.6-cli` is a setting with a default; `compose.yaml` passes `8.5-cli` for the second image. `FROM php:${PHP}` starts from the official PHP image of that version, the `cli` variant, which has PHP for the command line and its built-in server, nothing more.
- `RUN` runs commands while the image is built: here, it installs `git` and `unzip` for Composer, and the `mbstring` extension, then removes the package lists to keep the image small.
- `COPY --from=composer:2.11 ...` copies the `composer` program out of Composer's own official image.
- `WORKDIR /app` is the folder where commands run inside the container.

### The services: `compose.yaml`

<!-- include: compose.yaml -->

- `x-php: &php` is a block of settings that the services share. `&php` names it, and `<<: *php` copies it into a service. It's YAML, the format of this file, keeping things DRY.
- `volumes: [".:/app"]` puts the course folder into the container at `/app`. The container doesn't get a copy: it works on your files, so a change you save is there at once, and the files PHP writes are yours.
- `user: "${HOST_UID:-1000}:${HOST_GID:-1000}"` runs PHP as your user, not as the container's all-powerful `root`. Files it creates belong to you, and a mistake inside the container can't damage files that only `root` may change.
- `php` and `php85` are the two images for the checks.
- `web` is new in this chapter. `command: php -S 0.0.0.0:8000 -t shelf/public` starts the built-in server for the folder `shelf/public`. `ports: ["127.0.0.1:8000:8000"]` connects port 8000 of your computer to port 8000 of the container.

### Two addresses

Inside the container, the server listens on `0.0.0.0`, which means "on every network the container has". With `localhost`, it would only answer requests from inside the container itself, and your browser, outside, would never reach it.

Outside, `127.0.0.1:8000:8000` means: only this computer may connect. Without `127.0.0.1:`, Docker opens the port to every network your computer is on, such as the Wi-Fi in a café, and anyone there could use your development server. Always write the address.

### Where does PHP run?

<!-- include: code/45-docker/sapi.php -->

<!-- include: code/45-docker/server.php -->

`PHP_SAPI` names how PHP was started: `cli` in a terminal, `cli-server` behind the built-in server. It's the same PHP, the same image and the same code; only the way of asking differs.

### Running and stopping

From the course folder:

```
docker compose build          # once, and after the Dockerfile changes
docker compose up web         # serve shelf/public at http://localhost:8000
```

Stop it with Ctrl+C. `docker compose up -d web` runs it in the background instead, and `docker compose down` stops it.

## Common mistakes

### The port is taken

```
Error response from daemon: ... Bind for 127.0.0.1:8000 failed: port is already allocated
```

Another program already listens on port 8000 of your computer: another project's server, often.

**Fix:** stop the other program, or choose another port on your side, the first number: `127.0.0.1:8080:8000`, then open `http://localhost:8080`. The port inside the container stays 8000.

### `localhost` inside the container

`command: php -S localhost:8000 ...` starts without an error, but the browser says "connection reset" or "empty response".

**Fix:** `0.0.0.0` inside the container; the `ports` mapping decides who outside may connect.

### Old code in a new image, or the other way round

A change to the `Dockerfile` does nothing until the image is built again; a change to your PHP files needs no build at all, because the folder is shared.

**Fix:** `docker compose build` after changing the `Dockerfile` or `compose.yaml`'s `build:` settings; for PHP code, reload the page.

## Principle check

**KISS:** one `Dockerfile` and one `compose.yaml` for everything: checks, both PHP versions and the web. **Secure by default:** the port is bound to `127.0.0.1`, and the container runs as your user, not as `root`.

## Security

A container separates a program from your computer, but only as far as you let it. Three settings matter here: the port mapping (`127.0.0.1:` keeps it private), the user (not `root`), and the volume (only the course folder is shared, not your whole home folder). The built-in server is for development only: it handles one request at a time and has no protection against heavy traffic. Part 12 builds a production image with a real web server.

## Shelf

*Shelf* gets its first web page: a folder `shelf/public/` for everything the web server may serve, and in it, `index.php`. Only `public/` is served: `src/`, `books.json` and `members.json` stay outside, so no address can reach them.

Create `shelf/public/index.php`:

<!-- include: code/45-docker/shelf/public/index.php -->

Then run `docker compose up web` (or, without Docker, `php -S localhost:8000 -t shelf/public`) and open `http://localhost:8000`.

*Shelf*'s pages get their own requests file, `shelf/web.http`, which the book's checks send to them, as this chapter's examples:

<!-- include: code/45-docker/shelf/web.http -->

## Exercises

The exercises are in `exercises/45-docker/`. Check your work with:

```
composer exercise 45 beginner
```

1. **Beginner, `name.php`:** a page that shows the library's name from an environment variable, as Docker passes settings.
2. **Intermediate, `health.php`:** a health check that tells Docker, or a person, whether the site can work.
3. **Advanced, `ports.php`:** read port mappings such as `127.0.0.1:8000:8000`, and say who can connect.

## Summary

- An image packs PHP and everything it needs; a container is an image that runs.
- The `Dockerfile` builds the image; `compose.yaml` runs containers from it, with folders, ports and commands.
- `docker compose up web` serves *Shelf* at `http://localhost:8000`; Ctrl+C or `docker compose down` stops it.
- Inside the container, listen on `0.0.0.0`; outside, bind the port to `127.0.0.1`.
- Docker Engine is free; Docker Desktop is free for individuals and small companies, and free alternatives read the same files.

## Further reading

- Docker documentation, Get started: https://docs.docker.com/get-started/
- Docker documentation, Compose file reference: https://docs.docker.com/reference/compose-file/
- PHP official Docker images: https://hub.docker.com/_/php
- PHP manual, `PHP_SAPI`: https://www.php.net/manual/en/function.php-sapi-name.php
