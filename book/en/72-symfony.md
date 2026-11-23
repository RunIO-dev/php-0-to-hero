# 72. Why a framework, and what Symfony gives us

## Goals

- You will be able to explain what a framework does, and what it costs.
- You will be able to choose a Symfony version by how long it is supported.
- You will be able to use three of Symfony's components on their own: the request, routing, and the kernel.
- You will be able to find your way around a Symfony app: `bin/console`, `config/`, `public/`, `src/` and `var/`.

## Before you start

- Chapter 36: Composer, packages and the autoloader.
- Chapter 48: *Shelf*'s front controller, `App`, `Request` and `Response`.
- Parts 7 to 10: everything *Shelf* does on the web.

## The idea

In Parts 7 to 10, *Shelf* got a web layer that we wrote ourselves: a front controller, a `Request` and a `Response`, routes in `App::handle()`, templates, sessions, CSRF tokens, settings from `.env`, logging, problem details and API tokens. Writing it was the way to learn how the web works. Every web app needs the same parts, though, and writing them again for each app is slow, and each new version brings new bugs.

A **framework** is that layer, written once, used by many apps, and kept up to date by people who do nothing else. It gives you three things:

- **Parts that are already written and tested:** the request, routing, templates, forms, security, a database layer.
- **Conventions:** where each file goes, how a page is named, how settings are read. A developer who knows the framework finds their way around your app on the first day.
- **Upkeep:** security fixes, and new PHP versions supported, for years.

It also costs something. You learn its way of doing things, your app depends on its releases, and an upgrade to a new major version takes work. For a script of fifty lines, no framework is the right choice. For an app with logins, forms and a database, which must run for years, a framework usually pays for itself.

**Symfony** is one of PHP's two big frameworks. It is two things:

- **Components:** about fifty separate packages, each for one job: `symfony/http-foundation` for the request and the response, `symfony/routing`, `symfony/console`. Many other projects use them without the rest; Composer itself uses `symfony/console`, and so do PHPStan and PHP-CS-Fixer.
- **The framework:** `symfony/framework-bundle`, which puts the components together into an app, with a **kernel** that answers every request, and a **container** that makes the app's objects.

What we built by hand, and what replaces it in this part:

| *Shelf*, by hand | Symfony | Chapter |
|---|---|---|
| `public/index.php`, `App::handle()` | `public/index.php`, the kernel | 72 |
| `Web\Request`, `Web\Response` | `symfony/http-foundation` | 72, 73 |
| the routes in `App` | routing, `#[Route]` | 73 |
| `Templates`, `e()` | Twig | 73 |
| `Config`, `.env` | `.env`, `%env()%` in the configuration | 72, 73 |
| `PdoBookRepository` and the others | Doctrine ORM | 74 |
| the checks of each form, `Csrf` | forms and validation | 75 |
| `Accounts`, roles, API tokens | the security bundle: login, voters, tokens | 76 |
| `AppTest`, `web.http` | `WebTestCase` | 77 |

The domain classes stay: `Book`, `Member`, `Loan`, `Library` and their rules don't know about the web, so they don't change when the web layer does.

### Versions

Symfony brings out a new minor version every six months, in May and November, and a new major version every two years. The last minor version of each major one, `.4`, is a **long-term support** (LTS) version. Symfony 7.4, from November 2025, gets bug fixes until November 2028, and security fixes until November 2029. The newer versions, 8.1 today, get the newest features, but each is supported for only about eight months: you upgrade twice a year.

This course uses **Symfony 7.4 LTS**, so *Shelf* needs no upgrade for years. Everything in this part works the same way in 8.x. In `composer.json`, `"symfony/framework-bundle": "7.4.*"` keeps every Symfony package at 7.4: `composer update` brings the fixes of 7.4, never 8.0.

### A new Symfony app

A new app starts from Symfony's skeleton, the smallest app that runs:

```
composer create-project symfony/skeleton:"7.4.*" my-app
```

```
my-app/
  bin/console          Symfony's command line
  config/              the settings: bundles.php, packages/*.yaml, routes.yaml, services.yaml
  public/index.php     the front controller, and the only folder the web server shows
  src/Kernel.php       the kernel: it reads config/
  src/Controller/      the controllers, which answer requests
  var/                 the cache and the logs, which Symfony makes (Git ignores it)
  vendor/              the packages (Git ignores it)
  .env                 the settings, without secrets, as in chapter 65
  composer.json, composer.lock, symfony.lock
```

Symfony's packages come with **recipes**: when Composer installs one, the Symfony Flex plugin (`symfony/flex`) adds its configuration files, and records what it did in `symfony.lock`. More packages are added with `composer require` as the app needs them: Twig in chapter 73, Doctrine in 74.

## In code

These examples use three components on their own, in a Composer project of their own (`code/72-symfony/composer.json`). They show what the framework does with each request.

### The request and the response

<!-- include: code/72-symfony/request.php -->

- `Request` is chapter 48's `Request`, with more: `query` is `$_GET`, `request` is `$_POST`, `headers` are the headers. A page reads the real request with `Request::createFromGlobals()`.
- `getString()` and `getInt()` give a field as that type, or the default, never an array that someone sent as `title[]=x`.
- `Response` holds the status, the headers and the body, as ours does. `Response::HTTP_CREATED` names the code.

### Routing

<!-- include: code/72-symfony/routing.php -->

- A route has a name, a path with placeholders such as `{id}`, the requirements of each placeholder, and the methods it answers.
- `match()` finds the route and the values of the placeholders, or throws: no route is `404`, a wrong method `405`, with the methods that are allowed.
- The generator goes the other way, from a route's name to its address. A template that links with the name keeps working when the address changes.

### The kernel

Every Symfony app answers every request with this loop:

<!-- include: code/72-symfony/kernel.php -->

- `HttpKernel::handle()` takes a request and returns a response, as `App::handle()` does.
- On the way, it sends **events**. The `RouterListener` listens for the first one and finds the route; the `_controller` of the route is called, with `{id}` as `$id`; a listener for `KernelEvents::EXCEPTION` turns an exception into a response.
- The framework adds many listeners: for sessions, security, error pages. That is how a framework is built from components: each listener does one job, and an app can add its own.

## Common mistakes

### The library's work in the controller

<!-- include: code/72-symfony/fat.bad.php -->

✗ The rules of the library, how many copies there are and how long a loan lasts, live in a controller now. They can't be tested without a request, the command line and the API can't use them, and an upgrade of the framework touches them.

**Fix:** a controller translates between HTTP and the app: it reads the request, calls `Library::lend()`, and turns the result into a response. The rules stay in `Library`, which knows nothing about Symfony, as *Shelf*'s does.

### Changing files in vendor/

A bug in a package, or a behaviour you want different, and the fix is one line in `vendor/symfony/...`. The next `composer install`, on a colleague's computer or a server, brings the original back, and the fix is gone without a word.

**Fix:** change the behaviour through the configuration, or with a class of your own that the container uses instead. A real bug goes to the package's issue tracker.

### The newest version for an app that must run for years

An app started on a version that is supported for eight months must be upgraded twice a year, or it runs without security fixes.

**Fix:** pick the version by its support. An app that changes slowly takes the LTS version; one that wants the newest features plans the upgrades into its work.

## Principle check

**KISS:** the framework's way is the one every Symfony developer knows; don't invent another. **DRY:** the request, routing and security are written once, for every app. **YAGNI:** install a package when the app needs it, not because it exists.

## Security

- **Debug mode is for developers.** With `APP_ENV=dev`, an error shows its message, its stack trace and the app's settings. A server runs with `APP_ENV=prod` (Part 12).
- **The secret.** `APP_SECRET` signs what Symfony gives out, such as login links. `.env.dev` holds one for developers' computers, which is in Git, so it is no secret; a server sets its own, as an environment variable.
- **Updates.** `composer update` within 7.4 brings the security fixes; `composer audit` lists the known security problems of the installed packages.
- **Only `public/` is served.** The web server's document root is `public/`, so `.env`, `config/` and `var/` can't be downloaded.

## Shelf

*Shelf* becomes a Symfony app, without changing what it does. Symfony answers every request now, and hands each one to the `App` of Parts 7 to 10. Chapter 73 moves the pages into Symfony, one kind at a time.

In `shelf/`, Flex first, then the framework and the packages it needs:

```
composer config allow-plugins.symfony/flex true
composer config allow-plugins.symfony/runtime true
composer config extra.symfony.require "7.4.*"
composer require symfony/flex
composer require symfony/framework-bundle symfony/runtime symfony/console symfony/dotenv symfony/yaml
```

The recipes added their files, and a few lines to `.env` and `.gitignore`. Where *Shelf* had a file already, such as `public/index.php`, the recipe left it alone, so it is changed by hand below.

**The packages,** `shelf/composer.json`:

<!-- include: code/72-symfony/shelf/composer.json -->

**The kernel.** It reads `config/`; `MicroKernelTrait` does the reading. `shelf/src/Kernel.php`:

<!-- include: code/72-symfony/shelf/src/Kernel.php -->

The recipe writes `namespace App;`. *Shelf*'s classes are in `Shelf\`, so the kernel is too.

**The front controller.** Symfony's Runtime (`symfony/runtime`) reads `.env`, and calls the function the file returns with the settings; the kernel it returns answers the request. `shelf/public/index.php`:

<!-- include: code/72-symfony/shelf/public/index.php -->

**The command line,** the same way, `shelf/bin/console`:

<!-- include: code/72-symfony/shelf/bin/console -->

**The configuration.** The bundles, `shelf/config/bundles.php`:

<!-- include: code/72-symfony/shelf/config/bundles.php -->

The framework's settings, `shelf/config/packages/framework.yaml`:

<!-- include: code/72-symfony/shelf/config/packages/framework.yaml -->

The services, `shelf/config/services.yaml`. Every class in `src/` can be made by the container now; chapter 73 lets it make the repositories and `Library`:

<!-- include: code/72-symfony/shelf/config/services.yaml -->

The routes are the `#[Route]` attributes of the controllers, `shelf/config/routes.yaml`:

<!-- include: code/72-symfony/shelf/config/routes.yaml -->

`config/routes/framework.yaml` adds a route for developers, `/_error/404`, which shows an error page as visitors would see it. `config/preload.php` is for production servers (Part 12).

**The settings.** `.env` gets Symfony's own two, `shelf/.env`:

<!-- include: code/72-symfony/shelf/.env -->

`shelf/.env.dev` sets the secret for developers' computers:

<!-- include: code/72-symfony/shelf/.env.dev -->

`shelf/.gitignore` leaves out `/var/`, where Symfony keeps its cache, and `config/reference.php`, which Symfony writes in debug mode for apps whose configuration is in PHP:

<!-- include: code/72-symfony/shelf/.gitignore -->

**The bridge.** One controller answers every address, with the lowest priority, so that any route chapter 73 adds comes first. It does what the old `index.php` did. `shelf/src/Controller/LegacyController.php`:

<!-- include: code/72-symfony/shelf/src/Controller/LegacyController.php -->

This is a common way to move an app to a framework without stopping it: the framework answers, the old code still does the work, and the pages move over one at a time, until the bridge has nothing left to do.

**The check.** `shelf/web.http` sends the same requests as in chapter 71, and lists Symfony's routes first:

<!-- include: code/72-symfony/shelf/web.http requests 0, 12, 38 -->

The answers are the same, with two differences: Symfony writes every header name the same way (`Content-Type`, where PHP wrote `Content-type`), and it knows the name of `422`, `Unprocessable Content`, which PHP's built-in server didn't. The `204` after logging out has no `Content-Type` now: there is no body, so there is no type.

PHPStan reads Symfony's classes through *Shelf*'s autoloader, `shelf/phpstan.neon.dist`:

<!-- include: code/72-symfony/shelf/phpstan.neon.dist -->

Try the command line:

```
cd shelf
php bin/console list
php bin/console debug:router
php bin/console about
```

## Exercises

The exercises are in `exercises/72-symfony/`. Check your work with:

```
composer exercise 72 beginner
```

1. **Beginner, `page.php`:** a book's page with Symfony's `Request` and `Response`: the title escaped, `400` without one.
2. **Intermediate, `routes.php`:** the library's addresses as routes, with requirements and methods.
3. **Advanced, `api.php`:** a small API on `HttpKernel`, with every error as problem details.

## Summary

- A framework is the web layer, written once and kept up to date: parts, conventions and upkeep, for the price of learning it and following its releases.
- Symfony is components, usable on their own, and the framework that puts them together.
- Every request goes through the kernel: events, the router, the controller, a response.
- Pick the version by its support: *Shelf* uses 7.4 LTS, supported until November 2029.
- Controllers translate between HTTP and the app; the rules stay in the domain classes.

## Further reading

- Symfony documentation, Installing and setting up: https://symfony.com/doc/7.4/setup.html
- Symfony documentation, Symfony versus flat PHP: https://symfony.com/doc/7.4/introduction/from_flat_php_to_symfony.html
- Symfony releases, 7.4: https://symfony.com/releases/7.4
- Symfony documentation, the HttpKernel component: https://symfony.com/doc/7.4/components/http_kernel.html
