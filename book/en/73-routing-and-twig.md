# 73. Routing, controllers and Twig

## Goals

- You will be able to give a page its address with a `#[Route]` attribute, and answer it in a controller.
- You will be able to write templates in Twig, which escapes every value on its own.
- You will be able to let Symfony's container make your objects, and hand them to the controllers.
- You will be able to move *Shelf*'s pages and API from the hand-written `App` into Symfony.

## Before you start

- Chapter 72: the kernel, routing, and *Shelf* as a Symfony app.
- Chapter 49: templates and escaping.
- Chapters 50, 51 and 62: sessions, CSRF tokens and logging in.

## The idea

In chapter 72, Symfony took every request, and gave each one to the old `App`. This chapter moves the work into Symfony itself, piece by piece, and `App` goes. Each piece of the old web layer has a Symfony counterpart:

- **A route** says which address and method a piece of code answers. In `App`, the routes were one array in `handle()`. In Symfony, each **controller** method carries its own, as an attribute: `#[Route('/book', name: 'book', methods: ['GET'])]`. The router reads them all, finds the one for each request, and answers `404` or `405` on its own.
- **A controller** is a method that takes the request and returns a response, as `App`'s private methods did. It stays thin: it reads the request, asks `Library`, and answers.
- **Twig** replaces the PHP templates of chapter 49. A Twig template is HTML with three kinds of braces: `{{ value }}` prints a value, escaped for HTML; `{% if %}` and `{% for %}` are logic; `{# … #}` is a comment. Escaping is the default, so forgetting `e()` is no longer possible.
- **The container** makes the objects. In the old front controller, we wrote `new Library(new PdoBookRepository($db), …)` by hand. Symfony's container reads each constructor's types, and passes what they ask for: **autowiring**. `config/services.yaml` says what it can't guess: where the database is, and which class answers for an interface.
- **Events** carry what every request needs. `App::handle()` checked logins and CSRF tokens before each route. In Symfony, small classes listen to the kernel's events (chapter 72), and do it there.

Every route has a **name**. Templates and controllers make addresses from the name, `path('book', {title: 'Kindred'})`, never by writing `/book?title=…` themselves. When an address changes, only its route does.

## In code

These examples use Twig on its own, in a Composer project of their own (`code/73-routing-and-twig/composer.json`).

### Escaping on its own

A template, `code/73-routing-and-twig/templates/book.html.twig`:

<!-- include: code/73-routing-and-twig/templates/book.html.twig -->

<!-- include: code/73-routing-and-twig/escape.php -->

- `{{ book.title }}` reads `$book['title']`, or `$book->title`, or `$book->title()`: whichever exists. Templates don't care whether data is an array or an object.
- The script in the title arrives as text, `&lt;script&gt;`: Twig escaped it, without being asked.
- `strict_variables` makes a missing value an error, as below; without it, Twig prints nothing.

### A page inside a layout

The layout, `code/73-routing-and-twig/templates/layout.html.twig`:

<!-- include: code/73-routing-and-twig/templates/layout.html.twig -->

A page that **extends** it, and fills its **blocks**, `code/73-routing-and-twig/templates/list.html.twig`:

<!-- include: code/73-routing-and-twig/templates/list.html.twig -->

<!-- include: code/73-routing-and-twig/list.php -->

- `{% extends %}` must come first. The page fills the blocks; a block it leaves out keeps the layout's default.
- `{{ parent() }}` prints the layout's own content of the block: the page's title ends in "Shelf".
- `{% for %}` … `{% else %}` covers the empty list.
- **Filters** change a value: `|length`, `|url_encode`, `|map`. `max()` is a function.

### A name the page didn't pass

<!-- include: code/73-routing-and-twig/strict.php -->

The template uses `book.isbn`, which the page left out. With `strict_variables`, the mistake stops the page; Symfony sets it in its tests, so a missing value fails a test instead of leaving a gap on the page.

## Common mistakes

### |raw

<!-- include: code/73-routing-and-twig/templates/raw.bad.html.twig -->

✗ `|raw` switches escaping off. A title with `<script>` in it runs in every visitor's browser: XSS (chapter 64), in a template that looked safe.

**Fix:** never `|raw` on anything a person typed. If a value really holds HTML, such as text from an editor, clean it first with a library made for that, and still think twice.

### Addresses written by hand

`<a href="/book?title={{ book.title }}">` breaks for a title with `&` or `#` in it, and every link must be found and changed when the address does.

**Fix:** `{{ path('book', {title: book.title}) }}` encodes the title, and follows the route.

### Making objects in the controller

`new PdoBookRepository(new PDO(…))` inside a controller makes a database connection per controller, puts the database's settings in the code, and can't be swapped in a test.

**Fix:** ask for `Library` in the constructor, and let the container make it, once, from `services.yaml`.

## Principle check

**Secure by default:** Twig escapes everything; the router refuses every method a route doesn't list. **DRY:** the frame is written once, in `base.html.twig`; each address is written once, in its route. **KISS:** a controller reads, asks `Library`, and answers.

## Security

- **Escaping depends on where.** Twig escapes for HTML. Inside a `<script>` or a `style` attribute, other rules apply: `|escape('js')`, `|escape('css')`. *Shelf* has no script, and its CSP allows none (chapter 64).
- **CSRF is still needed.** Symfony makes and checks the tokens (`symfony/security-csrf`); *Shelf* asks for one in every form, and checks every `POST`, in `CheckCsrf`. Chapter 75's forms do it on their own.
- **Errors show nothing on a server.** In the `prod` environment, Symfony shows an error page without details; in `dev`, its page with the stack trace. *Shelf*'s `ErrorPages` keeps chapter 66's page with a reference.

## Shelf

*Shelf*'s pages and API move into controllers, its templates into Twig, and the checks that `App` did before each route into event subscribers. `App`, `Request`, `Response`, `Templates`, the sessions and `Csrf` go, with their tests. The rules in `Library` don't change at all.

Twig, and Symfony's CSRF tokens:

```
cd shelf
composer require symfony/twig-bundle symfony/security-csrf
```

`shelf/composer.json`:

<!-- include: code/73-routing-and-twig/shelf/composer.json -->

Flex added the bundle, `shelf/config/bundles.php`:

<!-- include: code/73-routing-and-twig/shelf/config/bundles.php -->

### The container

`shelf/config/services.yaml` says what autowiring can't guess: how the database is made, which class answers for each repository's interface, and where the covers and the log are:

<!-- include: code/73-routing-and-twig/shelf/config/services.yaml -->

`%kernel.project_dir%` is `shelf/`; `%env(SHELF_DATABASE)%` reads the setting from `.env` when the app runs, not when the container is built, so a server can set it as an environment variable.

### The framework's settings

The session, with chapter 62's cookie, and CSRF tokens, `shelf/config/packages/framework.yaml`:

<!-- include: code/73-routing-and-twig/shelf/config/packages/framework.yaml -->

`shelf/config/packages/twig.yaml` gives every template the logged-in member, as `current`:

<!-- include: code/73-routing-and-twig/shelf/config/packages/twig.yaml -->

### The controllers

The pages about books, `shelf/src/Controller/BookController.php`:

<!-- include: code/73-routing-and-twig/shelf/src/Controller/BookController.php lines 27-90 -->

- `AbstractController` gives `render()`, `redirectToRoute()`, `addFlash()` and `createNotFoundException()`.
- `#[MapQueryParameter] string $title` is `$_GET['title']`, as a string; a title sent as an array answers `404`.
- A missing book throws; `ErrorPages`, below, turns that into the "Not found" page.
- `redirectToRoute(…, status: 303)` is chapter 47's Post/Redirect/Get.

Logging in and out, and a member's loans, `shelf/src/Controller/AccountController.php`:

<!-- include: code/73-routing-and-twig/shelf/src/Controller/AccountController.php -->

The session is Symfony's: `migrate(true)` gives it a new ID, as `regenerate()` did. Chapter 76 hands the login itself to Symfony's security.

The API, `shelf/src/Controller/ApiController.php`:

<!-- include: code/73-routing-and-twig/shelf/src/Controller/ApiController.php lines 33-95 -->

`#[Route('/api', name: 'api_')]` on the class puts `/api` before every path and `api_` before every name. The class doesn't extend `AbstractController`, which it doesn't need; `#[AsController]` tells Symfony it is one. The JSON and problem details come from `shelf/src/Web/Json.php`, which takes over `Response::json()` and `Response::problem()`:

<!-- include: code/73-routing-and-twig/shelf/src/Web/Json.php -->

The logged-in member, for the controllers, the subscribers and the templates, `shelf/src/Web/CurrentMember.php`:

<!-- include: code/73-routing-and-twig/shelf/src/Web/CurrentMember.php -->

`Covers` takes Symfony's `UploadedFile` now, in place of our `Upload`, `shelf/src/Web/Covers.php`:

<!-- include: code/73-routing-and-twig/shelf/src/Web/Covers.php -->

### What every request needs

Each is an **event subscriber**: a class that names the events it listens to, and with which priority. The kernel's router listens to the request with priority 32, so these, with less, run after it, and know the route.

Members only, and staff only, as chapter 63's `refused()`, `shelf/src/EventSubscriber/RequireLogin.php`:

<!-- include: code/73-routing-and-twig/shelf/src/EventSubscriber/RequireLogin.php -->

Every `POST` of a page needs its token, `shelf/src/EventSubscriber/CheckCsrf.php`:

<!-- include: code/73-routing-and-twig/shelf/src/EventSubscriber/CheckCsrf.php -->

Chapter 64's headers, on every answer, `shelf/src/EventSubscriber/SecurityHeaders.php`:

<!-- include: code/73-routing-and-twig/shelf/src/EventSubscriber/SecurityHeaders.php -->

What an exception ends in, `shelf/src/EventSubscriber/ErrorPages.php`:

<!-- include: code/73-routing-and-twig/shelf/src/EventSubscriber/ErrorPages.php lines 27-83 -->

### The templates

Chapter 49's `layout.php` is `shelf/templates/base.html.twig`:

<!-- include: code/73-routing-and-twig/shelf/templates/base.html.twig -->

Two small pieces that several pages include, `shelf/templates/_flash.html.twig`:

<!-- include: code/73-routing-and-twig/shelf/templates/_flash.html.twig -->

`shelf/templates/_cover.html.twig`:

<!-- include: code/73-routing-and-twig/shelf/templates/_cover.html.twig -->

The list, `shelf/templates/list.html.twig`:

<!-- include: code/73-routing-and-twig/shelf/templates/list.html.twig -->

One book, `shelf/templates/book.html.twig`:

<!-- include: code/73-routing-and-twig/shelf/templates/book.html.twig -->

The other pages are the same templates in Twig: `add.html.twig`, `login.html.twig`, `loans.html.twig`, `missing.html.twig`, `forbidden.html.twig` and `expired.html.twig`, in `code/73-routing-and-twig/shelf/templates/`.

### What went

`App`, `Request`, `Response`, `Templates`, `Session`, `PhpSession`, `Csrf` and `Upload` are removed, with the PHP templates and the tests of these classes (`AppTest`, `ApiContractTest` and the others in `tests/Web/`). Chapter 77 tests the pages and the API again, the Symfony way. Until then, `web.http` checks every page and every address of the API from the outside.

PHPStan has no PHP templates left to check, `shelf/phpstan.neon.dist`:

<!-- include: code/73-routing-and-twig/shelf/phpstan.neon.dist -->

### The check

`shelf/web.http` sends the same requests:

<!-- include: code/73-routing-and-twig/shelf/web.http requests 0-1, 5 -->

The answers are the pages of chapter 72, with these differences:

- every route is listed by `debug:router`;
- a redirect has a small page as its body, for a browser that doesn't follow it;
- addresses in links encode a space as `%20` instead of `+`; both mean the same;
- every answer has `nosniff`, and every page the CSP;
- the CSRF tokens in the forms change on every page: Symfony mixes each one with a new random value, so that the page never shows the same secret twice (a defence against an attack called BREACH). The check shows them as `…`. Each one is accepted until the session ends.

Try it:

```
docker compose up web
php bin/console debug:router
php bin/console debug:autowiring Library
```

## Exercises

The exercises are in `exercises/73-routing-and-twig/`. Check your work with:

```
composer exercise 73 beginner
```

1. **Beginner, `book.html.twig`:** a book's card in Twig: an ISBN only when there is one, and every value escaped.
2. **Intermediate, `loans.html.twig`:** a member's loans, inside a layout, with blocks and a loop.
3. **Advanced, `LoanController.php`:** a controller with `#[Route]` attributes: a page and a JSON answer, with `404` for what doesn't exist.

## Summary

- A `#[Route]` attribute gives a controller method its address, its name and its methods; the router answers `404` and `405` itself.
- Controllers stay thin: read the request, ask the domain classes, answer.
- Twig escapes every `{{ value }}`; `{% extends %}` and blocks share the frame; `path()` makes addresses from route names.
- The container makes the objects, from their constructors' types and `services.yaml`.
- Event subscribers do what every request needs: logins, CSRF, headers, error pages.

## Further reading

- Symfony documentation, Routing: https://symfony.com/doc/7.4/routing.html
- Symfony documentation, Controller: https://symfony.com/doc/7.4/controller.html
- Symfony documentation, Creating and using templates: https://symfony.com/doc/7.4/templates.html
- Twig documentation, Twig for template designers: https://twig.symfony.com/doc/3.x/templates.html
- Symfony documentation, Service container: https://symfony.com/doc/7.4/service_container.html
