# 44. HTTP in plain words

## Goals

- You will be able to describe what happens between a browser and PHP: a request goes in, a response comes out.
- You will be able to read a request (method, path, query, headers, body) and a response (status code, headers, body).
- You will be able to set a status code and a header from PHP, and redirect.
- You will be able to run a page with PHP's built-in web server, and explain "headers already sent".

## Before you start

- Chapter 24: input on the command line, and why input is never trusted.
- Chapter 23: JSON.

## The idea

Until now, you started each program yourself, in a terminal, and it printed text. On the web, a **browser** asks for a page, and a **server** answers. Both sides follow **HTTP**, the Hypertext Transfer Protocol: a set of rules for what a question and an answer look like. They are plain text, and you can read them.

A **request** says:

- a **method**: `GET` to read something, `POST` to send something that changes data, such as a form;
- a **path**: `/books.php`, optionally followed by a **query string** after `?`: `/books.php?title=Kindred`;
- **headers**: extra lines of `Name: value`, such as `Accept: application/json`, "I would like JSON";
- a **body**, for `POST`: the data of a form, such as `name=Jonas`.

A **response** says:

- a **status code**: `200 OK`, `404 Not Found`, `301 Moved Permanently`, and a few dozen more;
- headers, such as `Content-Type: text/html`, "what follows is HTML", or `Location: /new`, "look there instead";
- a body: the page itself.

For each request, the server runs your PHP script. PHP fills `$_SERVER`, `$_GET` and `$_POST` from the request, and everything the script prints becomes the body of the response. Then the script ends. The next request starts from zero: the server keeps nothing between two requests, unless you store it somewhere. HTTP is **stateless**.

| Status | Means | When |
|---|---|---|
| 200 OK | here it is | almost always |
| 301 Moved Permanently | it lives elsewhere now, for good | a page moved |
| 303 See Other | done; now look there | after a form is saved (chapter 47) |
| 400 Bad Request | the request makes no sense | a required value is missing |
| 403 Forbidden | not for you | a missing token (chapter 51) |
| 404 Not Found | there is nothing here | a wrong address |
| 422 Unprocessable Content | understood, but the data is invalid | a form with mistakes (chapter 47) |
| 500 Internal Server Error | the server failed | an uncaught exception |

The first digit is the family: 2 success, 3 look elsewhere, 4 the client's mistake, 5 the server's.

## In code

### Running a page

PHP has a small web server built in, for development. In the folder of this chapter's examples:

```
php -S localhost:8000
```

`-S` starts the server at the address `localhost` (your own computer) and port 8000. Open `http://localhost:8000/hello.php` in a browser, and the server runs `hello.php`. Stop it with Ctrl+C. The built-in server handles one request at a time and is not made for real visitors; Part 12 puts *Shelf* on a real one.

Each example below shows the request, after `>`, and the response, after `<`, as the tool `curl -v` would. The book's checks send these requests to the real script, so this is what your browser gets too.

### The smallest page

<!-- include: code/44-http/hello.php -->

`header('Content-Type: text/plain; charset=UTF-8')` adds a header to the response: the body is plain text, in UTF-8. Without it, PHP says `text/html`, and the browser reads the body as HTML. The status, `200 OK`, is PHP's default.

### What PHP knows about the request

<!-- include: code/44-http/request.php -->

- `$_SERVER['REQUEST_METHOD']` is `GET` or `POST`; `$_SERVER['REQUEST_URI']` is the path with the query string. `parse_url(..., PHP_URL_PATH)` takes the path alone.
- `$_GET` holds the query string as an array: `?name=Amira` becomes `$_GET['name']`.
- `$_POST` holds the body of a form that was sent with `POST`.

All three come from whoever sent the request, and anybody can send anything: a browser, a script, a person typing in a terminal. They are input, as in chapter 24. This example answers in plain text, so nothing a visitor sends can become part of an HTML page; chapter 46 shows how to put input into HTML safely.

### A status code

<!-- include: code/44-http/status.php -->

`http_response_code(404)` sets the status. The body still explains the problem for a person; the status explains it for programs, such as a search engine that shouldn't list a missing page.

### A redirect

<!-- include: code/44-http/redirect.php -->

`header('Location: /hello.php', true, 301)` sets the `Location` header and the status in one call; `true` replaces an earlier header of the same name. The browser receives an empty answer with a new address, and asks again there, without the visitor noticing.

### JSON for programs

<!-- include: code/44-http/json.php -->

Not every request comes from a person. A program that asks with `Accept: application/json` wants data, not a page. The body is JSON (chapter 23), and the `Content-Type` says so. Part 10 builds a whole API this way.

## Common mistakes

### A header after the body

<!-- include: code/44-http/late-header.error.php -->

⚠ "Cannot modify header information - headers already sent by (output started at ... :7)". The headers travel before the body. Once the script has printed anything, even a space or an empty line before `<?php`, PHP has sent the headers, and a later `header()` comes too late. The status stays `200`, and the redirect never happens.

**Fix:** set status and headers first, then print. The message names the line where the output started: remove what is printed there, or move the `header()` call above it.

### Trusting the method or the query string

A link is a `GET`, and a search engine follows every link it finds. A `GET` request that deletes or lends a book will run, sooner or later, for someone who never meant it.

**Fix:** `GET` only reads. Everything that changes data is a `POST`, sent by a form (chapter 47).

### A missing value

`$_GET['title']` without a `title` in the query string is a warning and `null`. Visitors type addresses by hand and leave things out.

**Fix:** `$_GET['title'] ?? ''`, then check the value, and answer `400` or `404` when it's missing or wrong.

## Principle check

**Secure by default:** everything in a request is input, and is checked before it is used. **KISS:** a page is a script that reads a request and prints a response; there is nothing more to it, and every framework, Symfony included, is built on this.

## Security

Anyone can send any request: any method, any path, any header, any body. A browser is just one program that sends them. So a hidden field, a disabled button or a check in JavaScript protects nothing; only the server's checks count. Two more habits start here: plain text or JSON when you don't need HTML, and `GET` that never changes anything.

## Shelf

*Shelf* stays a command-line program in this chapter. Chapter 45 gives the course a web server in Docker, and chapter 46 puts *Shelf*'s book list on the web.

## Exercises

The exercises are in `exercises/44-http/`. Check your work with:

```
composer exercise 44 beginner
```

1. **Beginner, `book.php`:** answer `404 Not Found` for a book that doesn't exist, and `200` with the book otherwise.
2. **Intermediate, `moved.php`:** send old addresses to new ones with `301`, and answer `404` for the rest.
3. **Advanced, `formats.php`:** answer in JSON or in plain text, as the request's `Accept` header asks.

## Summary

- A request has a method, a path with a query string, headers and a body; a response has a status code, headers and a body.
- PHP fills `$_SERVER`, `$_GET` and `$_POST` from the request; what the script prints is the body.
- `http_response_code()` sets the status; `header()` sets headers, before any output.
- `GET` reads, `POST` changes; everything in a request is input.
- `php -S localhost:8000` serves a folder, for development only.

## Further reading

- PHP manual, The built-in web server: https://www.php.net/manual/en/features.commandline.webserver.php
- PHP manual, `header`: https://www.php.net/manual/en/function.header.php
- RFC 9110, HTTP Semantics (the standard; methods in section 9, status codes in section 15): https://www.rfc-editor.org/rfc/rfc9110.html
