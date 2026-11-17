# 67. REST in plain words, and JSON responses

## Goals

- You will be able to explain what an API is, and how a REST API names things and uses HTTP's methods.
- You will be able to answer a request with JSON, with the right `Content-Type`.
- You will be able to read a JSON body that a program sent, and refuse one that is broken.
- You will be able to give *Shelf* its first API address: `GET /api/books`.

## Before you start

- Chapter 23: JSON, `json_encode()` and `json_decode()`.
- Chapter 44: HTTP, methods and status codes.
- Chapter 48: *Shelf*'s front controller, `App`, `Request` and `Response`.

## The idea

So far, *Shelf* answers people: every answer is an HTML page for a browser. Other programs want the same data: the library's phone app, a display at the entrance, another library's catalogue. They don't want a page with a menu and a stylesheet; they want the books, as data. An **API** (application programming interface) is a set of addresses that answer programs instead of people.

Most web APIs today follow a style called **REST**. In plain words:

- **Resources, named by addresses.** A resource is a thing the API knows about: the list of books is `/api/books`, one book is `/api/books/12` or `/api/book?id=12`. Addresses are nouns. A verb in the address, `/getBooks` or `/deleteBook?id=12`, is a sign of an API that is not REST.
- **HTTP's methods say what to do with it.** `GET` reads, and changes nothing; `POST` adds to a list; `PUT` or `PATCH` change a resource; `DELETE` removes it. Chapter 44's rule holds here too: `GET` never changes anything.
- **Status codes say how it went.** `200 OK`, `201 Created` with a `Location` header that says where the new resource is, `404 Not Found`, `405 Method Not Allowed`. Chapter 68 looks at them, and at errors, closely.
- **Every request stands alone.** The server doesn't remember the program between two requests; whatever it needs, such as who is asking (chapter 69), comes with each request.

The data travels as **JSON** (chapter 23), the format nearly every language reads. The `Content-Type: application/json` header tells the program what it got, as `text/html` tells a browser.

## In code

### JSON for an API

<!-- include: code/67-rest-and-json/json.php -->

- The first line is what `json_encode()` gives by default: short, but slashes are written as `\/`, and every letter outside English as a code such as `ñ`. Both are valid JSON, and every program reads them correctly.
- With `JSON_PRETTY_PRINT`, `JSON_UNESCAPED_SLASHES` and `JSON_UNESCAPED_UNICODE`, a person can read it too. That helps whoever writes a program against your API, and costs a few spaces.
- `JSON_THROW_ON_ERROR` stays, as in chapter 23: a value that can't be encoded throws, instead of sending `false`.

### A small API

The example has three books and two addresses. Its pages share `code/67-rest-and-json/catalog.php`:

<!-- include: code/67-rest-and-json/catalog.php -->

`json()` sets the status and the `Content-Type`, sends the JSON, and ends the request. Its return type, `never`, tells PHP and PHPStan that it never returns.

The list of books, `code/67-rest-and-json/books.php`:

<!-- include: code/67-rest-and-json/books.php -->

- `GET` answers the list. `array_values()` makes it a JSON list, `[...]`, and not an object with keys `"1"`, `"2"`, `"3"`.
- `POST` adds a book. A program doesn't send a form, it sends JSON in the body, which PHP doesn't put into `$_POST`: `file_get_contents('php://input')` reads the body as it came.
- The body is input, like a form (chapter 47): broken JSON gets `400 Bad Request`; JSON without the right fields gets `422`, as a wrong form did. `is_int()` refuses a year sent as text, `"1988"`: in JSON, a number and a text are different types.
- A new book gets `201 Created`, and a `Location` header with its address.
- Any other method gets `405`, with an `Allow` header.

One book, `code/67-rest-and-json/book.php`:

<!-- include: code/67-rest-and-json/book.php -->

The requests, as a program sends them:

<!-- include: code/67-rest-and-json/books.http -->

## Common mistakes

### JSON put together by hand

<!-- include: code/67-rest-and-json/handmade.bad.php -->

✗ With `?title=Kitchen", "copies": 99, "x": "` in the address, the page answers `{"title": "Kitchen", "copies": 99, "x": "", "copies": 1}`: whoever typed the title wrote part of the answer. That is injection again (chapter 64, A05), this time into JSON. A title with a line break breaks the JSON altogether.

**Fix:** build a PHP array, and let `json_encode()` write it. It escapes every quote, backslash and control character.

### No Content-Type

Without `header('Content-Type: application/json')`, PHP sends `text/html`. A program that checks the type refuses the answer, and a browser that opens the address shows the JSON as a page: if a title holds `<script>`, that script may run.

**Fix:** set `Content-Type: application/json` on every JSON answer, as `json()` does, and, as *Shelf* does, `X-Content-Type-Options: nosniff`.

### Verbs in the address

`GET /deleteBook.php?id=2` removes a book on a `GET`. A link, a browser that loads links ahead of time, or a search engine's robot can call it. And every action needs its own address: `/getBook`, `/getBooks`, `/updateBook`.

**Fix:** one address per resource, and the method for the action: `DELETE /api/books/2`.

## Principle check

**KISS:** an API is the same request and response as a page, with JSON instead of HTML. **DRY:** one function sends every JSON answer, with the same flags and headers. **Secure by default:** the body is checked like a form, and `json_encode()` writes the JSON, so input can't write the answer.

## Security

- **An API is a door too.** Everything from Parts 7 to 9 applies: check every input, check who may do what (chapter 69), log refusals (chapter 66).
- **Answer only what is meant to be public.** An API makes data easy to collect: every member, every loan, in one request. Leave out what a program doesn't need, and never send a password hash, even a hashed one.
- **Limit the size.** PHP refuses bodies over `post_max_size` (8 MB by default). An API that accepts only small JSON can refuse bigger bodies earlier, in the web server.

## Shelf

Programs can read *Shelf*'s books as JSON, at `/api/books`.

**The answer.** `Response` gets `json()`, next to `html()`, `shelf/src/Web/Response.php`:

<!-- include: code/67-rest-and-json/shelf/src/Web/Response.php -->

**The address.** `App` gets the route `/api/books`, for `GET` only, and `apiBooks()`. Each book is the same data as in `books.json` (`toArray()`, chapter 32), with the address of its cover, or `null`. `shelf/src/Web/App.php`:

<!-- include: code/67-rest-and-json/shelf/src/Web/App.php -->

Reading the books needs no login, like the list page. The API sends no cookie, so a program that only reads gets no session.

**The tests,** `shelf/tests/Web/ResponseTest.php`:

<!-- include: code/67-rest-and-json/shelf/tests/Web/ResponseTest.php -->

`shelf/tests/Web/AppTest.php`:

<!-- include: code/67-rest-and-json/shelf/tests/Web/AppTest.php -->

`shelf/web.http` asks for `/api/books` after a book with a cover was added:

<!-- include: code/67-rest-and-json/shelf/web.http -->

Start the web server, and open `http://localhost:8000/api/books` in a browser: most browsers show JSON readably.

```
docker compose up web
```

## Exercises

The exercises are in `exercises/67-rest-and-json/`. Check your work with:

```
composer exercise 67 beginner
```

1. **Beginner, `new.php`:** answer a list of new books as JSON, readable, with the right `Content-Type`.
2. **Intermediate, `member.php`:** one member as JSON, `404` for an unknown name, `405` for any method but `GET`.
3. **Advanced, `loans.php`:** a new loan from a JSON body: `201` with `Location`, `400` for broken JSON, `422` for unknown books and members.

## Summary

- An API answers programs. REST names resources with addresses, and uses HTTP's methods and status codes.
- Answer with `json_encode()` and `Content-Type: application/json`; never build JSON from strings.
- A program sends JSON in the body: read it with `php://input`, and check it like a form.
- `201 Created` with `Location` for something new; `400` for a body that isn't JSON; `422` for JSON with the wrong data.

## Further reading

- MDN, an overview of HTTP: https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Overview
- PHP manual, `json_encode`: https://www.php.net/manual/en/function.json-encode.php
- PHP manual, `php://input`: https://www.php.net/manual/en/wrappers.php.php
- RFC 9110, HTTP Semantics, methods: https://www.rfc-editor.org/rfc/rfc9110#name-methods
