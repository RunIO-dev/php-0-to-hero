# 51. CSRF tokens

## Goals

- You will be able to explain cross-site request forgery (CSRF): another site acting in your visitor's name.
- You will be able to protect every form with a token, and check it with `hash_equals`.
- You will be able to answer a forged request with `403`, and change nothing.
- You will be able to protect all of *Shelf*'s forms in one place.

## Before you start

- Chapter 50: sessions and `samesite`.
- Chapter 48: the front controller, where every request passes.
- Chapter 47: forms and `POST`.

## The idea

A visitor is logged in to your site: their browser holds the session cookie. In another tab, they open a stranger's page. That page contains a form that points at *your* site, `action="https://shelf.example/delete"`, and a line of JavaScript that sends it at once. The browser sends the request, and with it, as always, the cookie for your site. For your server, the request looks like any other from that visitor: same cookie, same session. It deletes.

That is **cross-site request forgery**, **CSRF**: another site makes the visitor's browser send a request they never meant to send. The stranger can't read your pages or your cookie; they don't need to. They only need the request to happen.

Two defences work together:

1. **`samesite=Lax`** on the session cookie (chapter 50): modern browsers don't send the cookie with a `POST` from another site. That stops most attacks, but not all: older browsers ignore it, and pages on other subdomains of your domain count as the same site.
2. **A CSRF token**: a random secret, kept in the session and written into every form as a hidden field. A `POST` counts only if it sends the token back. The stranger's page can make the browser send a form, but it can't read your page, so it doesn't know the token.

The token is checked on every request that changes data. That's every `POST`, because `GET` never changes anything (chapter 44); otherwise a link or an image could do what a form does.

## In code

### A form without a token

<!-- include: code/51-csrf/delete.bad.php -->

<!-- include: code/51-csrf/delete.bad.http -->

✗ The second request comes from a stranger's page: the `Origin` header says so, but the page doesn't look. The session cookie is there, so the loans are gone.

### A form with a token

<!-- include: code/51-csrf/delete.php -->

<!-- include: code/51-csrf/delete.http -->

- `bin2hex(random_bytes(32))` makes the token: 32 bytes from the operating system's secure random source, written as 64 hexadecimal characters. Nobody can guess it.
- It is made once per session, `??=`, and written into the form as `<input type="hidden" name="_token">`.
- A `POST` without the token, or with a wrong one, gets `403 Forbidden` and changes nothing. The checks of this book show the random token as `{{token}}`.
- **`hash_equals`** compares the two strings. `===` stops at the first character that differs, so it takes a tiny bit longer the more characters are right; with enough tries, an attacker could measure that and find the token character by character. `hash_equals` always takes the same time. Use it for every secret.

### Where tokens are needed

Every form that sends a `POST`, and every other request that changes data. A search form with `GET` needs none: reading is harmless. A login form needs one too: otherwise a stranger could log your visitor in to the stranger's account, and see everything they then type.

## Common mistakes

### A token that isn't checked

The hidden field is in every form, and the code that should compare it was forgotten in one page. Nothing fails, nothing warns.

**Fix:** check the token in one place that every request passes, the front controller, for every `POST`, as *Shelf* does below. A page can't forget a check it never had to write.

### Checking only that a token was sent

`if (isset($_POST['_token']))` accepts any value, including one the attacker made up.

**Fix:** compare it with the session's token, with `hash_equals`.

### Changing data with `GET`

`<a href="/delete?id=7">` with a token in the link puts the token into the address bar, the history and the logs of every server on the way.

**Fix:** `GET` reads; everything that changes data is a `POST` form with the token in a hidden field.

## Principle check

**Secure by default:** every `POST` needs the token, without a single exception to remember; a forged request changes nothing. **DRY:** the check is written once, in `App`; the token is made in one class. **KISS:** one token per session, a hidden field and `hash_equals`.

## Security

A CSRF token protects against other sites. It doesn't protect against XSS: a script that runs inside your own page can read the token like any other part of the page. That is why escaping (chapter 46) comes first, and why tokens and `samesite` come on top. Part 11 shows Symfony's forms, which add and check the token on their own.

## Shelf

Every `POST` to *Shelf* now needs the session's token.

**The token,** `shelf/src/Web/Csrf.php`:

<!-- include: code/51-csrf/shelf/src/Web/Csrf.php -->

It keeps the token in the session (chapter 50), makes it the first time it is needed, and checks a sent token with `hash_equals`. Before a token was made, nothing is valid, not even an empty string.

**The check,** in `App::handle`, after the route is found and before it runs: a `POST` without the right `_token` gets `403` and the page "This form has expired". `shelf/src/Web/App.php`:

<!-- include: code/51-csrf/shelf/src/Web/App.php -->

The form gets the token as a hidden field. `shelf/templates/add.php`:

<!-- include: code/51-csrf/shelf/templates/add.php -->

The page for a refused form, `shelf/templates/expired.php`, explains what to do. A visitor sees it when the session ended while the form was open, too:

<!-- include: code/51-csrf/shelf/templates/expired.php -->

**The tests.** `shelf/tests/Web/CsrfTest.php`:

<!-- include: code/51-csrf/shelf/tests/Web/CsrfTest.php -->

`AppTest` starts each test with a known token in the session, and sends it with the forms; two new tests check the hidden field and the `403`:

<!-- include: code/51-csrf/shelf/tests/Web/AppTest.php -->

`TemplatesTest` passes the new `token` parameter:

<!-- include: code/51-csrf/shelf/tests/Web/TemplatesTest.php -->

`shelf/web.http` takes the token from the form with `# capture`, sends it with every `POST`, and sends one `POST` from another site without it:

<!-- include: code/51-csrf/shelf/web.http -->

## Exercises

The exercises are in `exercises/51-csrf/`. Check your work with:

```
composer exercise 51 beginner
```

1. **Beginner, `note.php`:** protect a form with a token in the session and a hidden field.
2. **Intermediate, `tokens.php`:** one token per form, used up when it is used.
3. **Advanced, `origin.php`:** a second check, from the headers that say which site a request came from.

## Summary

- CSRF: another site makes your visitor's browser send a request to your site, cookie included.
- `samesite=Lax` stops most of it; a token in the session and in every form stops the rest.
- Check the token on every `POST`, in one place, with `hash_equals`; a wrong or missing one gets `403` and changes nothing.
- `GET` never changes data, so it needs no token.
- Tokens don't protect against XSS: escaping comes first.

## Further reading

- OWASP, Cross-Site Request Forgery Prevention Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
- PHP manual, `hash_equals`: https://www.php.net/manual/en/function.hash-equals.php
- PHP manual, `random_bytes`: https://www.php.net/manual/en/function.random-bytes.php
