# 66. Logging and errors in production

## Goals

- You will be able to log events with PSR-3, PHP's common logging interface, at the right level.
- You will be able to write log lines that are useful, safe, and can't be faked.
- You will be able to show visitors a short error page, and keep the details in the log.
- You will be able to give *Shelf* a log, and its first package from Composer.

## Before you start

- Chapter 38: Composer, `composer require`, and PSR standards.
- Chapter 19: exceptions.
- Chapter 65: settings, and `SHELF_ENV`.

## The idea

A program on a server runs without anyone watching. When something goes wrong at three in the morning, or someone tries a thousand passwords, the only witness is the **log**: a file the program adds a line to for each event that matters. Without a log, you learn about a problem from an angry email, and can't find out what happened. That's A09 of the OWASP Top 10 (chapter 64): attacks that nobody notices.

Errors need the opposite treatment for the two audiences: **show little, log much.**

- **The visitor** gets a short, friendly page: something went wrong, try again. A stack trace, a SQL statement or a file path helps an attacker, not a visitor (A02 and A10).
- **The log** gets everything: the exception, the file and line, and a **reference**, a short random code that is also on the visitor's page. When someone reports "it said reference 3fa9c1d2", you find the line at once.

**PSR-3** is a standard of PHP-FIG, the group behind PSR-4 autoloading (chapter 36): one interface, `Psr\Log\LoggerInterface`, with a method for each of eight levels, from `debug` to `emergency`. Code that logs needs only the interface; the program decides where the lines go: a file, a central service, the screen in a test. Most PHP libraries and frameworks use it; Symfony's logger, Monolog, implements it (Part 11). Four levels cover most needs:

| Level | When | Example |
|---|---|---|
| `error` | Something failed and needs a person to look. | A page crashed; a cover could not be saved. |
| `warning` | Unusual, maybe an attack, but handled. | A failed login; a refused page. |
| `info` | A normal event worth keeping. | A member logged in; a book was lent. |
| `debug` | Details for finding a bug, on a developer's computer. | How long a query took. |

## In code

The examples are a small Composer project, `code/66-logging/`, created as in chapter 38:

```
composer require psr/log
```

Its logger writes to the screen, so you can see the lines, `code/66-logging/lines.php`:

<!-- include: code/66-logging/lines.php -->

- `AbstractLogger`, from `psr/log`, gives every level's method; a logger writes only `log()`.
- `{member}` in a message is a **placeholder**: the logger fills it in from the context array. The message itself stays the same for every member, so you can search the log for all "borrowed" lines.
- What is left of the context, such as an exception, goes after the message, as JSON.
- `addcslashes()` writes control characters, such as a line break, as `\n`. Why that matters is below.

### Levels and context

<!-- include: code/66-logging/levels.php -->

`lend()` gets a `LoggerInterface`, not a `LineLogger`: it doesn't know or care where the lines go.

### Show little, log much

<!-- include: code/66-logging/production.php -->

- `display_errors` off: PHP never writes an error into the page.
- `set_exception_handler()` runs for every exception that nothing caught. It logs everything, with a reference, and shows the visitor a short message with the same reference, and status `500`.
- In this example the log line goes to the screen too; on a server, only the visitor's message would.

## Common mistakes

### Details for everyone

<!-- include: code/66-logging/details.error.php -->

⚠ With `display_errors` on, the visitor reads the file name, the line, and the SQL. On a developer's computer that is what you want; on a server, it is a map of your code for whoever triggers an error.

**Fix:** decide by the environment: on in development, off in production, as *Shelf* does with `SHELF_ENV`.

### Secrets in the log

<!-- include: code/66-logging/password.bad.php -->

✗ Every password ever typed is now in a file that more people read than the database: colleagues, backup tools, log services.

**Fix:** log what you choose: the name, never the password, a token or a whole form. The intermediate exercise hides secrets that slip into a context.

### Forged log lines

<!-- include: code/66-logging/injection.php -->

A visitor typed a line break and a whole fake line into the name field. If the logger wrote it as it is, the log would show "Jonas logged in" at a time when he didn't, and anyone reading the log would believe it. This is **log injection**. Here, the line break is written as `\n`, and the fake line stays inside the warning, where it belongs.

**Fix:** escape control characters, or write each line as JSON, which does it (the advanced exercise).

## Principle check

**Secure by default:** visitors never see details, every uncaught error is logged with a reference, and input can't break a log line. **KISS:** one short logger class, behind a standard interface. **DRY:** the code logs through `LoggerInterface` everywhere; where the lines go is decided once, in the front controller.

## Security

- **What to log:** logins, failed and successful; refused pages; changes to members and roles; errors. With the time, and who, but no secrets.
- **Personal data.** A log with names and addresses is personal data too. Keep only what you need, for as long as you need it, and delete old logs. Rules such as the GDPR apply to logs as much as to the database.
- **Someone must read it.** A log nobody reads catches nothing. On a server, a tool can alert someone when errors pile up (Part 12).
- **Protect the file.** *Shelf*'s log is in `var/`, outside `public/`, so the web server never serves it.

## Shelf

*Shelf* gets a log: logins, refusals and every uncaught error, with an error page that shows little in production.

**Its first package.** In the folder `shelf/`:

```
composer require psr/log
```

`shelf/composer.json` now requires it, and `shelf/composer.lock` pins the version. After pulling this chapter, run `composer install` in `shelf/` once. `shelf/composer.json`:

<!-- include: code/66-logging/shelf/composer.json -->

**The setting.** `shelf/.env` gets `SHELF_LOG`, the path of the log file:

<!-- include: code/66-logging/shelf/.env -->

`Config` reads it like the others, `shelf/src/Config.php`:

<!-- include: code/66-logging/shelf/src/Config.php -->

**The logger,** `shelf/src/FileLogger.php`:

<!-- include: code/66-logging/shelf/src/FileLogger.php -->

`LOCK_EX` keeps two requests that log at the same moment from mixing their lines. The tests pass a fixed time.

**What gets logged.** `App` gets a `LoggerInterface`, and logs failed and successful logins, and refusals. `shelf/src/Web/App.php`:

<!-- include: code/66-logging/shelf/src/Web/App.php -->

**The error page,** `Response::error()`, `shelf/src/Web/Response.php`:

<!-- include: code/66-logging/shelf/src/Web/Response.php -->

It is plain HTML, without the templates: when the error is in a template, an error page made from templates would fail too. On a developer's computer, the details follow, escaped.

**The front controller** catches every error that the pages didn't, and sets PHP's own error settings from `SHELF_ENV`, `shelf/public/index.php`:

<!-- include: code/66-logging/shelf/public/index.php -->

`error_log` sends PHP's own warnings to the same file; their lines look a little different. If `.env` itself is broken, `Config::load()` fails before there is a logger, and PHP's default handling shows the message: on a server, set the environment variables before the first request.

**The tests.** A logger for tests keeps each line in an array, `shelf/tests/ArrayLogger.php`:

<!-- include: code/66-logging/shelf/tests/ArrayLogger.php -->

`shelf/tests/FileLoggerTest.php`:

<!-- include: code/66-logging/shelf/tests/FileLoggerTest.php -->

`shelf/tests/Web/ResponseTest.php`:

<!-- include: code/66-logging/shelf/tests/Web/ResponseTest.php -->

`AppTest` passes an `ArrayLogger`, and checks that a failed login and a refusal are logged, without the password, `shelf/tests/Web/AppTest.php`:

<!-- include: code/66-logging/shelf/tests/Web/AppTest.php -->

`ConfigTest` knows the new setting, `shelf/tests/ConfigTest.php`:

<!-- include: code/66-logging/shelf/tests/ConfigTest.php -->

Log in with a wrong password, then look at the log:

```
tail shelf/var/log/shelf.log
```

## Exercises

The exercises are in `exercises/66-logging/`. Check your work with:

```
composer exercise 66 beginner
```

1. **Beginner, `lend.php`:** log a loan, and a refused one, at the right levels, with placeholders.
2. **Intermediate, `redact.php`:** hide passwords, tokens and secrets that slip into a log's context.
3. **Advanced, `JsonLinesLogger.php`:** a PSR-3 logger that writes one JSON object per line.

## Summary

- Log what matters, with PSR-3's `LoggerInterface`, at the right level, with placeholders and context.
- Show visitors a short error page with a reference; keep the details in the log.
- `display_errors` on in development only.
- Never log secrets; escape control characters, so input can't forge log lines.
- A log is only useful when someone reads it.

## Further reading

- PHP-FIG, PSR-3: Logger Interface: https://www.php-fig.org/psr/psr-3/
- PHP manual, `set_exception_handler`: https://www.php.net/manual/en/function.set-exception-handler.php
- PHP manual, error handling configuration: https://www.php.net/manual/en/errorfunc.configuration.php
- OWASP, Logging Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html
