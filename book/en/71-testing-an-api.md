# 71. Testing an API

## Goals

- You will be able to test an API without a server, by calling the code that answers requests.
- You will be able to test the contract that programs rely on: fields, types, status codes and headers.
- You will be able to test every refusal: wrong input, no token, the wrong role.
- You will be able to choose what to test without a server, and what through real HTTP requests.

## Before you start

- Chapters 41 and 42: PHPUnit, data providers, and tests that catch bugs.
- Chapter 60: contract tests for two repositories.
- Chapters 67 to 70: *Shelf*'s API.

## The idea

A web page is read by a person, who notices when a heading moves. An API is read by programs, which notice nothing until they break: the phone app crashes when `year` comes as `"1979"` instead of `1979`, or when an error that was problem details turns into an HTML page. Nobody changed the app; the API changed under it. So an API's tests check what its programs rely on, its **contract**:

- **The shape of each answer.** Every field, with its type: `title` is a string, `year` a number, `isbn` a string or `null`. No field disappears, and none changes its type.
- **The status and the headers.** `201` with `Location` after a `POST`; `Content-Type: application/json`; `Link` on a list.
- **Every refusal.** Each way a request can be wrong gets its status, as problem details. And every address that needs a token refuses a request without one: a new address that forgets the check should fail a test the day it is written.

Most of these tests need no server. *Shelf*'s `App::handle()` takes a `Request` and returns a `Response` (chapter 48): a test builds the request, calls it, and looks at the answer, in a millisecond. That covers the routes, the checks and the JSON.

Some things only show over real HTTP: the front controller, PHP's own headers, how the built-in server passes `Authorization`, the cookies. *Shelf*'s `web.http` sends real requests to a real server, and compares the whole transcript (Part 7). Few of those, many of the fast ones.

A test should compare what matters, not every character. Decode the JSON, then compare values or the shape: a space, or the order of two fields, means nothing to a program.

## In code

The API of chapters 67 and 68, as one function that takes a request and returns the answer, `code/71-testing-an-api/api.php`:

<!-- include: code/71-testing-an-api/api.php -->

A small helper names what is wrong with an object, compared with its shape, `code/71-testing-an-api/shape.php`:

<!-- include: code/71-testing-an-api/shape.php -->

- The shape is an array: field name → type, as `get_debug_type()` names types (`string`, `int`, `null`). A `?` allows `null`.
- A missing field, a field with another type, and a field that isn't in the shape are all reported. An extra field is not always a mistake, but it is worth a decision: a field once sent is part of the contract.

The tests, `code/71-testing-an-api/ApiTest.php`:

<!-- include: code/71-testing-an-api/ApiTest.php -->

- `BOOK` and `PROBLEM` write the contract down, once.
- `testEveryMistakeIsAProblem()` runs for every case of the data provider (chapter 42): one test, six requests, and adding a seventh is one line.
- Each test decodes the body and looks at values: the status, the `Content-Type`, the shape, one field.

## Common mistakes

### Comparing every character

<!-- include: code/71-testing-an-api/brittle.bad.php -->

✗ The test fails when the API adds `JSON_PRETTY_PRINT`, sends the fields in another order, or gains a field; it passes when `year` is a number for the wrong book. It tests the spelling, not the contract.

**Fix:** decode the JSON; compare the shape and the values that matter, as `ApiTest` does.

### Only the happy path

Tests that send only good requests, with the right token, prove that the API works for the people who use it as meant. Attackers, and programs with bugs, don't.

**Fix:** a test for each refusal: no token, an unknown token, the wrong role, broken JSON, the wrong types, a page that doesn't exist. They are the tests that fail the day someone removes a check.

### Tests that need a running server, or real data

A test that calls `https://shelf.example/api/books` fails when the network does, changes the real library, and runs in seconds instead of milliseconds.

**Fix:** call the code directly, with repositories in memory, as *Shelf*'s tests do. Keep real HTTP for a few end-to-end checks, against a server the tests start themselves, as `web.http` does.

## Principle check

**DRY:** the shapes are written once, and every test checks against them. **KISS:** a function and an array describe a contract; no extra library. **Secure by default:** every refusal has a test, and every address that isn't public is checked for its token in one place.

## Security

- **Test the refusals first.** A missing `allow()` or a forgotten token check is a security hole that every happy-path test passes.
- **Test what must not be there.** A test that the token answer holds only `token` and `expires_in`, and a member's answer no `password_hash`, catches a leak the day a field is added to a query.
- **Tests never use real secrets.** *Shelf*'s tests make their own tokens and passwords, with a low cost.

## Shelf

*Shelf* gets a contract test for its API. Writing it found one inconsistency: a book that is already on the shelf was answered `422`, though chapter 68 says `409 Conflict`. Now it is `409`.

**The shape helper,** for the tests, `shelf/tests/JsonShape.php`:

<!-- include: code/71-testing-an-api/shelf/tests/JsonShape.php -->

**The contract,** `shelf/tests/Web/ApiContractTest.php`:

<!-- include: code/71-testing-an-api/shelf/tests/Web/ApiContractTest.php -->

- The shapes of a book, a loan, a token and a problem, written once.
- A book has the same shape in the list, alone, and after `POST`.
- Eleven mistakes, each with its status, all as problem details.
- Every address that isn't public, without a token: `401` with `WWW-Authenticate`.
- The API leaves the session alone: programs use tokens, never cookies.

**The fix.** A new exception for a book that is already there, `shelf/src/BookExists.php`:

<!-- include: code/71-testing-an-api/shelf/src/BookExists.php -->

`Library::add()` throws it, `shelf/src/Library.php`:

<!-- include: code/71-testing-an-api/shelf/src/Library.php -->

`App::apiAdd()` answers it with `409`, `shelf/src/Web/App.php`:

<!-- include: code/71-testing-an-api/shelf/src/Web/App.php -->

`shelf/tests/LibraryTest.php` expects the new exception:

<!-- include: code/71-testing-an-api/shelf/tests/LibraryTest.php -->

`shelf/web.http` sends the same book twice:

<!-- include: code/71-testing-an-api/shelf/web.http -->

Run all of *Shelf*'s tests:

```
vendor/bin/phpunit -c shelf
```

## Exercises

The exercises are in `exercises/71-testing-an-api/`. This time you write the tests: each `bugs/` folder holds versions of the code with one mistake each, and your tests must pass on the right code and fail on every bug. Check your work with:

```
composer exercise 71 beginner
```

1. **Beginner, `BookTest.php`:** test one book's answer: the status, the `Content-Type`, the JSON, and the problem for an unknown book.
2. **Intermediate, `NotesTest.php`:** test who may read and delete which notes: `401`, a member's own notes, someone else's as `404`.
3. **Advanced, `BooksTest.php`:** test a list: its pages, filter, sort, headers and refusals.

## Summary

- An API's tests check its contract: the shape of each answer, the status, the headers, and every refusal.
- Test without a server, by calling the code that answers; keep a few real HTTP checks.
- Decode the JSON and compare values and shapes, not characters.
- A test for each refusal catches the day someone removes a check.
- Every address that isn't public is tested for its token.

## Further reading

- PHPUnit manual, Writing tests (data providers): https://docs.phpunit.de/en/13.4/writing-tests-for-phpunit.html
- PHP manual, `get_debug_type`: https://www.php.net/manual/en/function.get-debug-type.php
- OWASP, Web Security Testing Guide, API testing: https://owasp.org/www-project-web-security-testing-guide/
