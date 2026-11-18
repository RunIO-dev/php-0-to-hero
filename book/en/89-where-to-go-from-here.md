# 89. Where to go from here

## Goals

- You will be able to answer your own questions from the PHP manual: a function's signature, its changes, the version that added it.
- You will be able to make a first contribution to an open-source project, from an issue to a pull request.
- You will know the next topics an application meets, and their first idea: events, queues, performance.
- You will know what Fibers are, and when application code needs them.

## Before you start

- Chapter 36: Composer.
- Chapter 43: Git.
- Chapter 56: relations and joins.
- Chapters 72 to 79: *Shelf* as a Symfony app, in production.
- Chapters 80 to 88: *Shelf*, complete.

## The idea

*Shelf* is finished, as far as this course goes: a Symfony app with a database, logins, forms, an API, tests, a production image and a deploy. Every chapter answered the questions it raised. From now on, the questions are yours, and nobody has written the chapter for them yet. This chapter is about finding the answers.

**The manual is the first place to look.** Every function, class and setting of PHP has a page at php.net, in the same order:

- the **signature**: the parameters with their types and defaults, and the return type, `str_contains(string $haystack, string $needle): bool`;
- what the function does, each parameter, and the **return values**, including what it returns on failure;
- the **changelog**: the PHP version that added a parameter, changed a default, or deprecated the function;
- examples, and notes.

The line at the top, "(PHP 8 >= 8.4.0)", says since when the function exists. Before you use a new function, check that line against the PHP of every server your code runs on. When you move to a new PHP version, read its **migration guide** in the manual's appendices: what is new, what changed, what was removed.

**Open source is the next teacher.** Symfony, Doctrine, PHPUnit and PHP itself are written in public. Reading their code shows how experienced people solve the problems you meet, and their issues show which problems are hard. A first contribution is usually small: a wrong sentence in the documentation, a missing test, a bug you hit yourself. Every project says how it wants them, in a `CONTRIBUTING.md` file: often an issue first, then a pull request with a test, in the project's code style. This book's repository works the same way.

**Three topics come next for most applications:**

- **Events.** Code says what happened ("a loan was made"), and other code reacts, without the first one knowing who listens. A new reaction, such as a log line or a notice, needs no change to the code that lends. Symfony's `EventDispatcher` is the one *Shelf* already uses for its security headers (chapter 78).
- **Queues.** Some work is too slow for a request: sending an email, making a report, resizing a cover. The request puts a **message** into a **queue** and answers at once; a **worker**, another process, takes the messages and handles them. If a message fails, it is tried again later. Symfony's Messenger component does this, with a table in the database as the queue, or a message broker.
- **Performance.** A page is slow for a reason you can measure: too many queries, a slow query, work done on every request that could be done once. Measure first, with a profiler or by counting queries, then change the one thing the measurement shows, and measure again.

**Fibers** come up when you read about asynchronous PHP: a function that can pause in the middle and be resumed later. Libraries that wait for many network answers at once build on them. Application code rarely uses them directly, so they get one example here, not a chapter.

## In code

The examples are a Composer project of their own (`code/89-where-to-go-from-here/composer.json`):

<!-- include: code/89-where-to-go-from-here/composer.json -->

### The signature, as the manual writes it

PHP knows every function's signature itself, through Reflection (`code/89-where-to-go-from-here/manual.php`):

<!-- include: code/89-where-to-go-from-here/manual.php -->

<!-- include: code/89-where-to-go-from-here/manual.out -->

- `array_search()` returns the key it found, or `false`: a key `0` is a find, so check with `=== false`, as the manual's warning on that page says.
- `json_decode()` has four parameters, three with defaults; the manual explains `$flags` with `JSON_THROW_ON_ERROR` (chapter 23).
- The manual writes the union as `int|string|false`; the order of a union type doesn't matter.

### Events

<!-- include: code/89-where-to-go-from-here/events.php -->

<!-- include: code/89-where-to-go-from-here/events.out -->

- The listener with priority 10 runs first; the code that lends knows neither listener.
- An event is an object: listeners can read it, and change it for the ones after them.

### A queue

<!-- include: code/89-where-to-go-from-here/queue.php -->

<!-- include: code/89-where-to-go-from-here/queue.out -->

- The page answers before any email is sent.
- Jonas's message fails once and goes back into the queue; the second try works. A real worker waits before the next try, longer each time.

### Counting queries

<!-- include: code/89-where-to-go-from-here/queries.php -->

<!-- include: code/89-where-to-go-from-here/queries.out -->

- The first way sends one query for the list, and one more per member: with a thousand members, a thousand and one. This is called the **N + 1 problem**, and an ORM makes it easy to write without seeing it: each `$member->loans` can be a query of its own.
- The JOIN sends one, whatever the number of members. In Doctrine, `SELECT l, b … JOIN l.book b` does the same (chapter 74).

### A fiber

<!-- include: code/89-where-to-go-from-here/fiber.php -->

<!-- include: code/89-where-to-go-from-here/fiber.out -->

- `Fiber::suspend()` pauses the function and hands a value out; `resume()` continues it, with a value in.
- The two take turns, in one process: nothing runs at the same time.

## Common mistakes

### A handler that isn't safe to run twice

<!-- include: code/89-where-to-go-from-here/reminder.bad.php -->

✗ A queue delivers each message **at least once**. If the worker stops between sending the email and marking the message as done, the message comes again, and Amira gets the same reminder twice. With a payment instead of an email, she pays twice.

**Fix:** make each handler **idempotent**, safe to run again: give each message an id, store the ids that are done, and check that first. The advanced exercise does this.

### Optimising without measuring

A cache here, a faster loop there, and the page is as slow as before: the time went into thirty queries that nobody counted.

**Fix:** measure, change one thing, measure again. Symfony's profiler, in `dev`, shows each page's queries and their time; a profiler such as Xdebug's shows where the time goes in the code.

### An answer for another PHP

An answer from a forum or a generator uses `utf8_encode()`, deprecated since PHP 8.2, or a library version that no longer exists. It works on the writer's computer, years ago.

**Fix:** check each function in the manual, with its changelog, and each package's version against your `composer.json`. Then let PHPStan and your tests check the rest.

## Principle check

**KISS:** a queue, an event or a cache each adds a moving part; add one when a measurement or a requirement asks for it, not before. **YAGNI:** *Shelf* sends no emails yet, so it has no queue yet. **Secure by default:** what you learn from a forum or a library's issue is checked against the manual and the tests before it goes into your code.

## Security

- **Read the security pages.** The manual's security chapter, Symfony's security advisories and `composer audit` (chapter 38) tell you when a version you use has a known problem. Keep PHP and every package on a supported version.
- **Report vulnerabilities privately.** A security problem you find in an open-source project goes to the address in its `SECURITY.md`, never into a public issue, so that users can update before attackers read about it.
- **Messages are input.** A queue's message was written by your code, but read later, maybe by another version of it: a worker checks it as it checks a request.

## Shelf

*Shelf* gets no new code in this chapter: everything it needs, it has. Where each next topic would fit, when it is needed:

- **Events:** a `LoanMade` event when a book is lent, for a log line or a notice to the librarian, without changing `Library`.
- **A queue:** reminder emails, a day before a loan is due, sent by a worker that a scheduled command fills.
- **Performance:** the books page with a hundred thousand books, an index on the columns it searches, and its queries counted in the profiler.

How to run every part of it is in `shelf/README.md`: development, the tests and the checks, and the production containers.

## Exercises

The exercises are in `exercises/89-where-to-go-from-here/`. Check your work with:

```
composer exercise 89 beginner
```

1. **Beginner, `available.php`:** from the manual's "since" versions, the functions a server's PHP doesn't have yet.
2. **Intermediate, `Dispatcher.php`:** a small event dispatcher, with priorities and events that stop the listeners after them.
3. **Advanced, `Worker.php`:** a queue's worker: retries that wait longer each time, failed messages kept for a person, and no message handled twice.

## Summary

- The manual has every function's signature, return values and changelog; check the version that added a function against your servers.
- Open-source projects take contributions through issues and pull requests, as their `CONTRIBUTING.md` describes.
- Events let other code react to what happened; queues move slow work out of the request, and their handlers must be safe to run twice.
- Measure before you optimise: count the queries, then fix the one the measurement shows.
- Fibers pause and resume a function; libraries use them, application code rarely does.

## Further reading

- PHP manual, How to read a function definition: https://www.php.net/manual/en/about.prototypes.php
- PHP manual, Migrating from PHP 8.4.x to PHP 8.5.x: https://www.php.net/manual/en/migration85.php
- PHP, Supported versions: https://www.php.net/supported-versions.php
- PHP manual, Fibers: https://www.php.net/manual/en/language.fibers.php
- Symfony documentation, Events and event listeners: https://symfony.com/doc/7.4/event_dispatcher.html
- Symfony documentation, Messenger: https://symfony.com/doc/7.4/messenger.html
- Symfony documentation, Profiler: https://symfony.com/doc/7.4/profiler.html
- GitHub documentation, Contributing to a project: https://docs.github.com/en/get-started/exploring-projects-on-github/contributing-to-a-project
