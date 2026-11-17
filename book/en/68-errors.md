# 68. Status codes and error responses

## Goals

- You will be able to pick the status code that tells a program what happened.
- You will be able to answer every error in one shape, problem details, that programs can read.
- You will be able to name every mistake in a request at once.
- You will be able to make *Shelf*'s API answer its errors as problem details, never as HTML pages.

## Before you start

- Chapter 67: REST, and JSON answers.
- Chapter 44: status codes.
- Chapter 66: an error page with a reference.

## The idea

A person reads an error page and understands "There is no such book." A program reads the **status code** first: it decides whether to show the result, ask its user to fix a field, log in again, or try later. So the code must be exact.

The codes an API needs most, by their first digit:

| Code | Name | When |
|---|---|---|
| `200` | OK | The answer is in the body. |
| `201` | Created | Something new was made; `Location` says where. |
| `204` | No Content | Done, and nothing to send back, such as after a `DELETE`. |
| `400` | Bad Request | The request can't be read: the body is not valid JSON. |
| `401` | Unauthorized | We don't know who you are: no login, or a wrong token (chapter 69). |
| `403` | Forbidden | We know who you are, and you may not. |
| `404` | Not Found | The resource doesn't exist. |
| `405` | Method Not Allowed | The address exists, the method doesn't; `Allow` lists the ones that do. |
| `409` | Conflict | The request clashes with what is there: the book is already on the shelf. |
| `415` | Unsupported Media Type | The body is not in a format we take: a form instead of JSON. |
| `422` | Unprocessable Content | The JSON is fine, the data isn't: a year as text. |
| `429` | Too Many Requests | Slow down (chapter 62's guessing, for an API). |
| `500` | Internal Server Error | Our mistake, not the program's. |

`4xx` means the request was wrong, so the program must change something before it tries again; `5xx` means the server was wrong, so the same request may work later.

The status alone isn't enough: a `422` should say which fields are wrong. If every API invented its own shape for that (`{"error": ...}`, `{"ok": false, "message": ...}`), every program would need code for each. **Problem details** (RFC 9457, an internet standard) is one shape for all of them, sent as `application/problem+json`:

- `type`: an address that names the kind of problem. `about:blank` means "nothing more than the status".
- `title`: a short name for that kind, the same every time.
- `status`: the status code again, for whoever only has the body.
- `detail`: what went wrong in this request, for a person.
- More members as the problem needs them, such as `errors`, the list of wrong fields.

## In code

The example's pages share `code/68-errors/catalog.php`, with `json()` from chapter 67, and `problem()`:

<!-- include: code/68-errors/catalog.php -->

One book, `code/68-errors/book.php`:

<!-- include: code/68-errors/book.php -->

- An unknown ID is `404`, as problem details.
- `DELETE` answers `204`: no body at all. PHP sends `Content-Type: text/html` with every answer unless told otherwise; an empty `default_mimetype` leaves it out.

Adding a book, `code/68-errors/books.php`:

<!-- include: code/68-errors/books.php -->

- The checks go from the outside in: the method, the format, whether the JSON can be read, the data, and only then the library's own rules.
- `$_SERVER['CONTENT_TYPE']` is the request's `Content-Type`. `str_starts_with()`, because a program may add `; charset=utf-8`.
- All wrong fields are collected first, then answered together: a program can show its user every mistake at once, as a form does.
- A `type` of our own, `/problems/invalid-book`, names this kind of problem. An address relative to the API is allowed; a good API serves a page there that explains it.

The requests:

<!-- include: code/68-errors/books.http -->

## Common mistakes

### 200 for everything

<!-- include: code/68-errors/ok.bad.php -->

✗ A program sees `200`, and must read the body to learn that it failed. Tools that watch an API count it as a success, caches may keep the answer, and every program needs to know this API's own `ok` field.

**Fix:** the status says how it went; the body says the details, as problem details.

### An HTML page as an error

A program asks the API for an address that doesn't exist, and gets the web server's "404 Not Found" page, or *Shelf*'s own, with a menu and a stylesheet. Its JSON reader fails on the first `<`.

**Fix:** under `/api/`, every answer is JSON: errors too, including the ones nothing caught. *Shelf* does it below.

### Too much detail

A `500` with the exception's message and stack trace helps an attacker as much as on a page (chapter 66).

**Fix:** the same rule as for pages: a short detail and a reference, the rest in the log. Details only on a developer's computer.

## Principle check

**KISS:** one function answers every problem, in a shape that a standard defines. **DRY:** the codes and the shape are the same on every address, so a program handles errors once. **Secure by default:** a `500` tells a program what it tells a person: that something went wrong, and the reference.

## Security

- **403 or 404?** A `403` for `/api/loans/17` says that loan 17 exists. Where that matters, answer `404` for "not yours" too, so that a stranger can't learn what exists.
- **Validation messages are input to someone's screen.** A program may show `detail` in a web page: the details hold text that came from the request (`"kindred" is already on the shelf`), so the program must escape it, as *Shelf*'s templates do.
- **Same message, same timing.** Chapter 62's rule for logins applies to an API's login too (chapter 69).

## Shelf

*Shelf*'s API gets one book at `/api/book?title=...`, and answers every error as problem details: unknown addresses, wrong methods, unknown books, and errors that nothing caught.

**The answer.** `Response` gets `problem()`, and `withHeader()` to add the `Allow` header to it; `error()` answers problem details when the API was asked, `shelf/src/Web/Response.php`:

<!-- include: code/68-errors/shelf/src/Web/Response.php -->

**The routes.** A path that starts with `/api/` gets problem details for a `404` or a `405`; the pages keep their HTML. `apiBook()` finds a book in any case, as the book page does (chapter 55). `shelf/src/Web/App.php`:

<!-- include: code/68-errors/shelf/src/Web/App.php -->

**Errors nobody caught.** The front controller reads the request before anything else, so that the `catch` knows whether the API was asked, `shelf/public/index.php`:

<!-- include: code/68-errors/shelf/public/index.php -->

**The tests,** `shelf/tests/Web/ResponseTest.php`:

<!-- include: code/68-errors/shelf/tests/Web/ResponseTest.php -->

`shelf/tests/Web/AppTest.php`:

<!-- include: code/68-errors/shelf/tests/Web/AppTest.php -->

`shelf/web.http` asks for a book, a book that doesn't exist, a wrong method and a wrong address:

<!-- include: code/68-errors/shelf/web.http -->

## Exercises

The exercises are in `exercises/68-errors/`. Check your work with:

```
composer exercise 68 beginner
```

1. **Beginner, `statuses.php`:** the status code for each situation, from `201` to `429`.
2. **Intermediate, `member.php`:** turn an API's own error shapes into problem details.
3. **Advanced, `members.php`:** a new member from JSON: `405`, `415`, `400`, `422` with every wrong field, `409`, and `201`.

## Summary

- The status code tells a program what happened: `2xx` it worked, `4xx` the request must change, `5xx` try later.
- `400` unreadable, `415` wrong format, `422` wrong data, `409` a clash, `401` who are you, `403` you may not.
- Answer every error as problem details: `type`, `title`, `status`, `detail`, and more members as needed.
- Name every wrong field at once.
- Under `/api/`, even an error that nothing caught is JSON, with a reference and no details.

## Further reading

- RFC 9457, Problem Details for HTTP APIs: https://www.rfc-editor.org/rfc/rfc9457
- MDN, HTTP response status codes: https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status
- RFC 9110, HTTP Semantics, status codes: https://www.rfc-editor.org/rfc/rfc9110#name-status-codes
