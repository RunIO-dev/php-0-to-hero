# 50. Cookies and sessions, with secure settings

## Goals

- You will be able to set a cookie and read it back, with settings that protect it.
- You will be able to keep data for one visitor between requests in a session.
- You will be able to explain why a cookie is input, and a session ID a secret.
- You will be able to give a session a new ID when a visitor logs in.
- You will be able to show *Shelf*'s visitors a message after a redirect, once.

## Before you start

- Chapter 44: HTTP is stateless; headers.
- Chapter 47: Post/Redirect/Get.
- Chapter 33: interfaces, for the session in tests.

## The idea

HTTP forgets: every request starts from zero (chapter 44). Yet a site has to remember things between two pages: the language a visitor chose, the books on their list, that they logged in a minute ago.

A **cookie** is how. The server sends a header, `Set-Cookie: theme=dark`, and the browser keeps that small value and sends it back with every request to the same site: `Cookie: theme=dark`. In PHP, `setcookie()` sends it, and `$_COOKIE` holds what came back.

A cookie is stored by the visitor and sent by the visitor, so it is **input**, like `$_GET`: anyone can send any cookie with any value. A cookie fits a preference, never a decision such as "this visitor is an admin".

A **session** keeps the data on the server instead. The browser gets one cookie with a long random **session ID**; PHP keeps a file per ID with the data, and `$_SESSION` reads and writes it. The visitor can't change the data, only the ID they send. That makes the ID a secret: whoever has it *is* that visitor for the site. The settings below protect it.

| Setting | What it does | Protects against |
|---|---|---|
| `httponly` | JavaScript in the page can't read the cookie | a script that got in through XSS stealing the ID |
| `secure` | sent only over HTTPS (and to `localhost`) | the ID being read on an open network |
| `samesite=Lax` | not sent when another site sends a form to yours | other sites acting in your visitor's name (chapter 51) |
| `use_strict_mode` | PHP refuses IDs it didn't create | an attacker giving a visitor an ID they already know |

## In code

### A cookie

<!-- include: code/50-cookies-and-sessions/theme.php -->

<!-- include: code/50-cookies-and-sessions/theme.http -->

- `setcookie()` takes the name, the value and an array of settings. It sends a header, so it has to come before any output (chapter 44).
- `expires` is a time in seconds; PHP also writes `Max-Age`. A cookie without it is deleted when the browser closes.
- Reading it, the value is checked like any input: only `light` and `dark` count.
- The checks of this book replace dates that change with every run with `…`.

### A session

<!-- include: code/50-cookies-and-sessions/visits.php -->

<!-- include: code/50-cookies-and-sessions/visits.http -->

`session_start()` with an array of settings starts the session, or continues it when the browser sent an ID. The first answer sets the cookie `PHPSESSID`; the checks show its value as `…`, because it is random. After that, the browser sends it back, and `$_SESSION` has the data of the last request. Like the headers, `session_start()` comes before any output.

### A message shown once

<!-- include: code/50-cookies-and-sessions/flash.php -->

<!-- include: code/50-cookies-and-sessions/flash.http -->

After a `POST`, the page redirects (chapter 47), and the page that follows should say what happened. The message goes into the session before the redirect; the next page shows it and removes it, so it doesn't come back when the page is reloaded. It is called a **flash message**.

### A new ID at login

<!-- include: code/50-cookies-and-sessions/fixation.php -->

<!-- include: code/50-cookies-and-sessions/fixation.http -->

When a visitor logs in, their session gets new powers. If the ID stayed the same, someone who knew it before, because they gave it to the visitor in a link or saw it on a shared computer, would now be logged in too. That attack is called **session fixation**. `session_regenerate_id(true)` gives the session a new ID and deletes the old one; the data stays. Part 9 builds the login; the rule starts here: **every change of who a visitor is gets a new ID**, at login and at logout.

## Common mistakes

### Trusting a cookie

<!-- include: code/50-cookies-and-sessions/trust.bad.php -->

✗ The second request sends `Cookie: role=admin`, which takes one line in any tool, and the page believes it.

**Fix:** keep who a visitor is, and what they may do, in the session: `$_SESSION['member']`, set by the server after a login. A cookie holds only what the visitor may choose freely, and is checked when it is read.

### A session without settings

`session_start()` with no settings uses PHP's defaults, which depend on the server's configuration: the cookie may be readable by JavaScript, may be sent over plain HTTP, and PHP may accept IDs it never made.

**Fix:** pass the settings every time, as above, in one place: *Shelf*'s `PhpSession` below.

### Storing objects or too much in the session

The session file is read and written with every request. A whole list of books, or an object whose class changes later, makes every request slower, and can break old sessions.

**Fix:** store small, simple values: an ID, a name, a message. Load the rest from where it lives.

## Principle check

**Secure by default:** the safe settings are written down once and used every time; cookies are checked as input; a login gets a new ID. **KISS:** `Session` has three methods; `PhpSession` uses PHP's own sessions, with nothing extra. **DRY:** the session's settings exist in one class.

## Security

Treat the session ID like a password: never put it in a URL (`use_only_cookies`, PHP's default, keeps it out), never print it, and never log it. A session that lives forever is a risk too: PHP deletes old session files on its own after `session.gc_maxlifetime`, 24 minutes by default without a request. `samesite=Lax` already stops most attacks from other sites; chapter 51 adds a token that stops the rest.

## Shelf

After adding a book, *Shelf*'s list now says "Added "Sula"." once.

**An interface for the session,** so that `App` can be tested with a fake. `shelf/src/Web/Session.php`:

<!-- include: code/50-cookies-and-sessions/shelf/src/Web/Session.php -->

**PHP's session, with the safe settings.** `shelf/src/Web/PhpSession.php`:

<!-- include: code/50-cookies-and-sessions/shelf/src/Web/PhpSession.php -->

- `SETTINGS` holds every setting from the table above, and a name of its own, `shelf`, instead of `PHPSESSID`.
- The session starts only when it is needed. A visitor who only reads the list, and has no session cookie, gets none: `get()` returns `null` without starting a session.

**The app** saves the message after a book is added, and the list takes it once. `shelf/src/Web/App.php`:

<!-- include: code/50-cookies-and-sessions/shelf/src/Web/App.php -->

The list template gets the message, or `null`, before the books. `shelf/templates/list.php`:

<!-- include: code/50-cookies-and-sessions/shelf/templates/list.php -->

The front controller passes a `PhpSession`. `shelf/public/index.php`:

<!-- include: code/50-cookies-and-sessions/shelf/public/index.php -->

**The tests** use a session made of an array, `shelf/tests/Web/ArraySession.php`:

<!-- include: code/50-cookies-and-sessions/shelf/tests/Web/ArraySession.php -->

`AppTest` checks that the message is saved, escaped and shown once:

<!-- include: code/50-cookies-and-sessions/shelf/tests/Web/AppTest.php -->

The list template has a new first parameter, so `TemplatesTest` passes `null` before the book:

<!-- include: code/50-cookies-and-sessions/shelf/tests/Web/TemplatesTest.php -->

`shelf/web.http` reloads the list once more after the message. The session cookie appears only with the `303`, when there is something to remember:

<!-- include: code/50-cookies-and-sessions/shelf/web.http -->

## Exercises

The exercises are in `exercises/50-cookies-and-sessions/`. Check your work with:

```
composer exercise 50 beginner
```

1. **Beginner, `language.php`:** remember a visitor's language in a cookie, safely set and checked when read.
2. **Intermediate, `list.php`:** a reading list of up to three titles, kept in the session.
3. **Advanced, `flash.php`:** flash messages of two kinds, several at once, stored as JSON, and robust against a broken session.

## Summary

- A cookie is a small value the browser keeps and sends back; it is input, and is checked when read.
- A session keeps the data on the server; the browser holds only a random ID, which is a secret.
- Cookies get `httponly`, `secure` and `samesite=Lax`; sessions also `use_strict_mode`.
- Every change of who a visitor is gets a new session ID: `session_regenerate_id(true)`.
- A flash message is saved before a redirect and shown once.

## Further reading

- PHP manual, `setcookie`: https://www.php.net/manual/en/function.setcookie.php
- PHP manual, Sessions and security: https://www.php.net/manual/en/features.session.security.management.php
- PHP manual, Session runtime configuration: https://www.php.net/manual/en/session.configuration.php
- OWASP, Session Management Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html
