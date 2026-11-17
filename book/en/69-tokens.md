# 69. API authentication with tokens

## Goals

- You will be able to explain why an API uses tokens, not the session cookie of the website.
- You will be able to make a token, give it out once, and keep only its hash.
- You will be able to read the token from the `Authorization` header, and answer `401` or `403`.
- You will be able to let *Shelf*'s members read their own loans, and staff add books, through the API.

## Before you start

- Chapter 61: password hashes, and why they are slow.
- Chapters 62 and 63: login, roles, and the member from the session.
- Chapter 68: `401`, `403`, and problem details.

## The idea

The website remembers a member with a session cookie (chapter 62): the browser sends it with every request, on its own. For an API, that is the wrong tool:

- **Programs aren't browsers.** A phone app or a script has no cookie jar, and no login form to fill in.
- **Cookies bring CSRF with them** (chapter 51). Because the browser sends them on its own, every change needs a CSRF token. An API that accepted the session cookie would need them too.

Instead, a program sends a **token** with every request, in a header:

```
Authorization: Bearer 8a5770ac735337001a23e4459d91f5375e5921bfc3d5761e81a03fe652ce6622
```

"Bearer" means: whoever bears this token is let in, as with a ticket. A browser never adds this header on its own, so a page on another site can't make a request with it: no CSRF.

How a token works:

1. **The program logs in once,** with a name and password: `POST /api/tokens`. The answer is a new token, shown that one time.
2. **Every request carries it.** The server finds the member the token belongs to, and checks their role, as in chapter 63.
3. **It ends:** after a set time, or when the program logs out, `DELETE /api/tokens`.

A token is as good as the password, so the database keeps only its **hash**, as for passwords. But a different hash: a token is 32 random bytes, a number with 78 digits. Nobody can guess it, so the slow `password_hash()` (chapter 61), which protects passwords that *can* be guessed, isn't needed. A fast hash, SHA-256, is enough, and it finds the token again in one step: hash what came, look it up.

Without a valid token, the answer is `401 Unauthorized`, with a `WWW-Authenticate: Bearer` header that says what kind of credentials the API expects. With a valid token, and the wrong role: `403`.

## In code

### Making a token

<!-- include: code/69-tokens/token.php -->

- `random_bytes(32)` is the same safe randomness as the CSRF token of chapter 51; `bin2hex()` writes it as 64 characters that fit in a header.
- The database keeps `hash('sha256', $token)`. When a request comes, the server hashes what it sent, and looks for that hash.

### Checking it

The example's pages share `code/69-tokens/account.php`. Its tokens are stored as a database would hold them, by their hashes; the tokens themselves are in `books.http` below, as a program would keep them.

<!-- include: code/69-tokens/account.php -->

- `member()` reads the header from `$_SERVER['HTTP_AUTHORIZATION']`, checks its shape, and looks for the token's hash.
- `allow()` is chapter 63's, for an API: `401` with `WWW-Authenticate` for no token or an unknown one, `403` for the wrong role.

A member's own loans, `code/69-tokens/loans.php`:

<!-- include: code/69-tokens/loans.php -->

Adding a book, staff only, `code/69-tokens/books.php`:

<!-- include: code/69-tokens/books.php -->

<!-- include: code/69-tokens/books.http -->

## Common mistakes

### The token in the address

<!-- include: code/69-tokens/query.bad.php -->

✗ Addresses are kept in many places: the server's access log, the browser's history, a proxy's log, the `Referer` header of the next link. Each one now holds a key to the account.

**Fix:** the token goes in the `Authorization` header, which none of them keep.

### The tokens themselves in the database

<!-- include: code/69-tokens/plain.bad.php -->

✗ A copy of the database, from a backup or an injection, opens every account at once, without anyone cracking anything.

**Fix:** keep `hash('sha256', $token)`, as `account.php` does.

### The session cookie for the API

An API that also accepts the website's session cookie can be called by any page the member visits, with the member's cookie: CSRF, on every address that changes something.

**Fix:** the API reads only the `Authorization` header, and ignores the session. *Shelf* does, below.

## Principle check

**Secure by default:** tokens are random, stored only as hashes, end after 30 days, and come only in a header that browsers never send on their own. **KISS:** a token is one random string and one table row; no extra library. **DRY:** the API checks roles with the same `Role::mayChangeBooks()` as the website.

## Security

- **Tokens end.** *Shelf*'s last 30 days. A program that needs longer asks for a new one; a lost token works for 30 days at most, unless revoked.
- **One token per program.** A member with a phone app and a script gets two tokens, and can end one without the other.
- **Guessing passwords through the API.** `POST /api/tokens` checks passwords like the login form, and needs the same slowing down (chapter 62's advanced exercise); without it, an API is the easier door. Answer it with `429 Too Many Requests`.
- **HTTPS only.** A token sent over plain HTTP can be read on the way (Part 12).
- **Standards for larger systems.** When other companies' programs act on behalf of your members, use OAuth 2.0, the standard for that, instead of passwords; Symfony can do it (Part 11). *Shelf*'s own tokens are enough for its own apps.

## Shelf

Programs log in with a token. Members read their own loans; staff add books.

**The table,** `shelf/migrations/005-api-tokens.sql`:

<!-- include: code/69-tokens/shelf/migrations/005-api-tokens.sql -->

`ON DELETE CASCADE`: when a member is removed, their tokens go with them.

**Where tokens are kept,** `shelf/src/TokenRepository.php`:

<!-- include: code/69-tokens/shelf/src/TokenRepository.php -->

`shelf/src/PdoTokenRepository.php`:

<!-- include: code/69-tokens/shelf/src/PdoTokenRepository.php -->

`add()` finds the member's ID in the same statement, so an unknown name adds nothing.

**Making and checking them,** `shelf/src/ApiTokens.php`:

<!-- include: code/69-tokens/shelf/src/ApiTokens.php -->

**The request** gets its headers and its body, so the API can read the token and the JSON, `shelf/src/Web/Request.php`:

<!-- include: code/69-tokens/shelf/src/Web/Request.php -->

**The routes.** `App` gets the `ApiTokens`, and four routes: `POST /api/tokens` logs in and answers the token, with `expires_in`, its lifetime in seconds, as OAuth names it; `DELETE /api/tokens` ends it; `GET /api/loans` answers the member's loans; `POST /api/books` adds a book, for staff. `caller()` reads the member from the token, and only from the token. The CSRF check now skips `/api/`, where no cookie counts. `shelf/src/Web/App.php`:

<!-- include: code/69-tokens/shelf/src/Web/App.php -->

The front controller makes the `ApiTokens`, `shelf/public/index.php`:

<!-- include: code/69-tokens/shelf/public/index.php -->

**The tests.** A repository in memory, `shelf/tests/InMemoryTokenRepository.php`:

<!-- include: code/69-tokens/shelf/tests/InMemoryTokenRepository.php -->

`shelf/tests/ApiTokensTest.php`:

<!-- include: code/69-tokens/shelf/tests/ApiTokensTest.php -->

`shelf/tests/Database/PdoTokenRepositoryTest.php`:

<!-- include: code/69-tokens/shelf/tests/Database/PdoTokenRepositoryTest.php -->

`shelf/tests/Web/RequestTest.php`:

<!-- include: code/69-tokens/shelf/tests/Web/RequestTest.php -->

`AppTest` checks the login, the loans, the refusals, that the session doesn't count, and the logout, `shelf/tests/Web/AppTest.php`:

<!-- include: code/69-tokens/shelf/tests/Web/AppTest.php -->

`shelf/web.http` logs in as Amira and as Jonas through the API. Each token is new every time, so the check captures it, and the `.out` shows `{{amiratoken}}`:

<!-- include: code/69-tokens/shelf/web.http -->

## Exercises

The exercises are in `exercises/69-tokens/`. Check your work with:

```
composer exercise 69 beginner
```

1. **Beginner, `bearer.php`:** read the token from an `Authorization` header, and nothing else.
2. **Intermediate, `tokens.php`:** make a token and its hash, and find a member by it.
3. **Advanced, `notes.php`:** each member's notes by token: `401`, their own notes only, and someone else's note as `404`.

## Summary

- An API takes a token in `Authorization: Bearer <token>`, never the session cookie, so it needs no CSRF token.
- A token is 32 random bytes, shown once; the database keeps its SHA-256.
- No token or an unknown one: `401` with `WWW-Authenticate: Bearer`; the wrong role: `403`.
- Tokens end: after a set time, or when the program logs out.
- Never put a token in the address.

## Further reading

- RFC 6750, Bearer Token Usage: https://www.rfc-editor.org/rfc/rfc6750
- MDN, `Authorization` header: https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Authorization
- PHP manual, `random_bytes`: https://www.php.net/manual/en/function.random-bytes.php
- OWASP, REST Security Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/REST_Security_Cheat_Sheet.html
