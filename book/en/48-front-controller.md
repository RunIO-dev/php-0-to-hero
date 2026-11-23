# 48. A front controller and routing

## Goals

- You will be able to send every request to one file, a front controller, and decide there what to answer.
- You will be able to route requests by method and path, and answer `404` and `405` correctly.
- You will be able to describe a request and a response as objects, and test the pages without a server.
- You will be able to run *Shelf*'s pages through one `App` class.

## Before you start

- Chapter 44: requests, responses and status codes.
- Chapter 47: forms and Post/Redirect/Get.
- Chapter 41: tests with PHPUnit.

## The idea

So far, every page was its own file: `/index.php` ran `index.php`, `/add.php` ran `add.php`. That is simple, and it stops scaling soon: each file repeats the same start (load the autoloader, create the `Library`), each one has to remember the same checks, and the addresses are file names, `.php` included.

A **front controller** turns this around. Every request goes to one file, `public/index.php`. It reads the method and the path, and looks up what to do in a table of **routes**: `GET /` shows the list, `GET /add` the form, `POST /add` adds a book. Everything they share is done once, in one place. Addresses become words of your choice, `/add` instead of `/add.php`, and they can stay the same when the code behind them moves.

Two answers belong to routing itself:

- `404 Not Found`: there is no route for this path;
- `405 Method Not Allowed`: the path exists, but not for this method, such as a `GET` to an address that only takes `POST`. The `Allow` header lists the methods it does take.

Every framework works this way, Symfony included (Part 11). Here you build the small version yourself, so that nothing there will be magic.

## In code

### One file for every request

<!-- include: code/48-front-controller/front.php -->

- `$routes` is the table: for each path, the methods it takes, and for each method a function that returns the answer. Looking up `$routes[$path][$method]` is the whole routing.
- A path that isn't in the table gets `404`; a path with the wrong method gets `405` with `Allow`.
- `php -S localhost:8000 front.php` starts the built-in server with `front.php` as its **router script**: every request runs it, whatever the path.

<!-- include: code/48-front-controller/front.http -->

`/books?sort=title` finds the route `/books`: the query string isn't part of the path.

### The built-in server and `index.php`

With a folder instead of a router script, `php -S localhost:8000 -t public`, the built-in server serves the files in `public/` as they are, and sends every path that isn't a file, such as `/add`, to `public/index.php`. That is the front controller with no extra setting, and it is how *Shelf* is served by `docker compose up web`. Real web servers are set up the same way in Part 12.

### Requests and responses as objects

A page that reads `$_POST` and calls `header()` and `echo` can only be tried through a server. Two small classes change that:

- a `Request` holds what the visitor asked for: method, path, query fields and form fields;
- a `Response` holds the answer: status, headers and body, and sends them only when `send()` is called.

A function that takes a `Request` and returns a `Response` can then be called from a test, like any other function. *Shelf*'s classes below are exactly that.

## Common mistakes

### Routing on the whole `REQUEST_URI`

<!-- include: code/48-front-controller/uri.bad.php -->

✗ `/books` works, and `/books?sort=title`, the same page with a query string, is "Not found". `REQUEST_URI` is the path *and* the query string.

**Fix:** route on the path alone: `parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)`.

### `404` for the wrong method

Answering `404` for a `GET` to `/add`'s `POST` tells the visitor, and programs, that nothing is there. It's there, but not for this method.

**Fix:** `405` with an `Allow` header, as `front.php` does.

### Pages that still work as files

After moving to a front controller, an old `public/add.php` that is still there still runs, outside the front controller and its checks.

**Fix:** delete the old pages. `public/` keeps `index.php` and the files that are sent as they are, such as images and CSS.

## Principle check

**DRY:** the start of every request, the routing and the answers to wrong addresses are written once. **KISS:** a route table is an array, and routing is one lookup. **Secure by default:** only routes in the table run; a forgotten file in `public/` is the one way around it, so `public/` holds nothing else that runs.

## Security

A front controller is one door instead of many: checks that every request needs, later a login and a CSRF token (chapter 51), are added in one place and can't be forgotten on one page. Keep the folder the server serves, `public/`, free of everything else: no `.env`, no data, no scripts. *Shelf*'s data and code are one folder up, where no address reaches them.

## Shelf

*Shelf* gets a front controller and its first web classes, in the namespace `Shelf\Web`, in `shelf/src/Web/`.

**The request.** `shelf/src/Web/Request.php`:

<!-- include: code/48-front-controller/shelf/src/Web/Request.php -->

`fromGlobals()` reads PHP's request once, and keeps only fields that are strings, so `name[]=x` can't cause a `TypeError` anywhere later (chapter 47). `field()` gives a form field, trimmed.

**The response.** `shelf/src/Web/Response.php`:

<!-- include: code/48-front-controller/shelf/src/Web/Response.php -->

`html()` and `redirect()` name the two answers *Shelf* needs most.

**The app.** `shelf/src/Web/App.php` holds the routes and the pages:

<!-- include: code/48-front-controller/shelf/src/Web/App.php lines 1-40, 59-86, 106-110 -->

- `handle()` is `front.php`'s table, with `Request` in and `Response` out.
- The list and the form are the pages of chapters 46 and 47, unchanged, inside `ob_start()` and `ob_get_clean()`, which catch what is printed as a string instead of sending it.
- The addresses are now `/` and `/add`.

The class mixes two jobs, routing and HTML, and the HTML makes it long. Chapter 49 moves the HTML into templates.

**The front controller.** `shelf/public/index.php` becomes:

<!-- include: code/48-front-controller/shelf/public/index.php -->

Delete `shelf/public/add.php`: `App` answers `/add` now.

**The tests.** The pages are tested without a server, with the `InMemoryBookRepository` from chapter 42. Create `shelf/tests/Web/AppTest.php`:

<!-- include: code/48-front-controller/shelf/tests/Web/AppTest.php -->

`shelf/web.http` sends the requests of chapter 47 to the new addresses, and checks that `/add.php` is gone and that `DELETE /add` is refused:

<!-- include: code/48-front-controller/shelf/web.http requests 2, 6, 8-9 -->

## Exercises

The exercises are in `exercises/48-front-controller/`. Check your work with:

```
composer exercise 48 beginner
```

1. **Beginner, `site.php`:** a front controller for three pages, with `404` for every other path.
2. **Intermediate, `router.php`:** a `Router` class: add routes, then dispatch a method and a path, with `404` and `405`.
3. **Advanced, `params.php`:** routes with a part of the path as a value, such as `/books/42`.

## Summary

- A front controller is one file that receives every request and decides what to answer.
- A route table maps a path and a method to the code that answers; routing is a lookup.
- No route: `404`. A route without this method: `405` with `Allow`.
- Route on the path, without the query string: `parse_url(..., PHP_URL_PATH)`.
- A `Request` in and a `Response` out make pages testable without a server.

## Further reading

- PHP manual, The built-in web server (router scripts): https://www.php.net/manual/en/features.commandline.webserver.php
- PHP manual, Output control (`ob_start`, `ob_get_clean`): https://www.php.net/manual/en/book.outcontrol.php
- RFC 9110, HTTP Semantics, section 15.5.6 (405 Method Not Allowed) and 10.2.1 (Allow): https://www.rfc-editor.org/rfc/rfc9110.html
