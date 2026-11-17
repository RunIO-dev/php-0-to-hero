# 64. Security in one place

## Goals

- You will be able to explain each of the OWASP Top 10 in plain words, with the defence this book uses for it.
- You will be able to add response headers that make the browser a second line of defence.
- You will be able to make a check fail closed: when it can't decide, it refuses.
- You will be able to review a page for the mistakes of Parts 7 to 9.

## Before you start

- Chapters 46 (escaping), 51 (CSRF), 52 (uploads), 54 (prepared statements), 59 (least privilege), 61 to 63 (passwords, login, roles).

## The idea

Every chapter since Part 7 had a "Security" section, because security is not a feature you add at the end. It is a set of habits, each small, used every time: escape output, prepare statements, check the token, check the role. One forgotten habit on one page is enough for an attacker. This chapter puts the habits side by side, so you can see the whole picture, and check a page against it.

The **OWASP Top 10** is a list of the most important kinds of security risk in web applications, published by the Open Worldwide Application Security Project, a non-profit community. It is based on data from many thousands of real applications, and updated every few years. Here is the 2025 edition, in plain words, with where this book handles each one:

| | Risk | In plain words | In this book |
|---|---|---|---|
| A01 | Broken Access Control | Someone does or sees what they shouldn't. | Roles, checks on every request, the member from the session (63); no changes on `GET` (44). |
| A02 | Security Misconfiguration | Unsafe settings: errors shown to visitors, a database open to the network, default passwords. | Errors in production (66); the database without ports (59); safe session settings (50). |
| A03 | Software Supply Chain Failures | A library you use, or the way it reaches you, is unsafe. | Composer and `composer.lock` (38); `composer audit`, below. |
| A04 | Cryptographic Failures | Secrets stored or sent without the right protection. | Password hashes (61); random tokens (51); HTTPS (Part 12). |
| A05 | Injection | Input is run as code: SQL, HTML and JavaScript (XSS), shell commands. | Prepared statements (54); escaping (46). |
| A06 | Insecure Design | The plan itself has a hole, whatever the code. | Lending checked in one statement (57); every login gets one answer (62). |
| A07 | Authentication Failures | Weak passwords, guessing, sessions that can be taken over. | Long passwords (61); a new session ID at login, slow guessing (62). |
| A08 | Software or Data Integrity Failures | Trusting data or code that nobody checked. | Uploads checked by their content (52); data from files and databases checked (32, 55). |
| A09 | Security Logging and Alerting Failures | An attack happens, and nobody notices. | Logging failed logins and errors (66). |
| A10 | Mishandling of Exceptional Conditions | Errors that leak details, or that let a request through. | Exceptions (19); checks that fail closed, below; errors in production (66). |

Two more ideas tie the list together:

- **Defence in depth.** Each layer can fail, so use several. A CSRF token and `samesite`; escaping and a Content Security Policy; prepared statements and a database user that can't drop tables.
- **Fail closed.** When a check can't decide, because the database is down or the data is strange, the answer is no. An error must never turn into "allowed".

## In code

### Headers that make the browser stricter

<!-- include: code/64-security/headers.php -->

<!-- include: code/64-security/headers.http -->

- **`Content-Security-Policy`** (CSP) tells the browser where a page may load things from. `default-src 'self'` allows only this site; `script-src 'self'` allows only script files from this site, so a `<script>` that an attacker managed to put *inside* the page doesn't run. If escaping fails somewhere, the policy is the second wall.
- `frame-ancestors 'none'`: no other site may show this page in a frame. That stops **clickjacking**, where a stranger's page lays your page, invisible, over its own buttons.
- `form-action 'self'` and `base-uri 'none'`: forms can only be sent to this site, and an injected `<base>` tag can't redirect the page's links.
- **`X-Content-Type-Options: nosniff`**: the browser takes the `Content-Type` as it is, and never guesses (chapter 52).
- **`Referrer-Policy: same-origin`**: the address of this page, which may hold a search or an ID, is not sent to other sites when a visitor follows a link.

These headers don't replace escaping: they limit the damage when something slips through.

### Fail open, fail closed

<!-- include: code/64-security/fail-open.php -->

✗ The members table is missing: a typo, or a migration that didn't run. The check throws, the `catch` says "allowed", and Amira, a reader, may remove books. The person who wrote it meant well: "don't block the librarians when the database has a problem."

<!-- include: code/64-security/fail-closed.php -->

The check has no `catch`: an error can't become a yes. The caller refuses, and the error goes to the log (chapter 66). In *Shelf*, an exception that nothing catches ends the request with an error page, which is a refusal too.

## Common mistakes

### Security as the last step

"We'll make it secure before the launch." By then, every page has its own habits, and nobody has time to check them all.

**Fix:** the safe way from the first line, in one place: one escaping function, one place that checks tokens and roles, one way to run SQL. *Shelf* has had them since they first appeared.

### Trusting what the browser sends

Hidden fields, `select` options, cookies, headers, the `Referer`, the file name of an upload, a price in a form: the visitor controls all of them.

**Fix:** treat every part of a request as input, and check it on the server (chapters 47 and 63).

### Telling attackers how the site works

An error page with a stack trace shows file paths, library versions and SQL. A login that says "no such member" shows who has an account.

**Fix:** visitors get a short message; the details go to the log (chapter 66). Failed logins get one answer (62).

## Principle check

**Secure by default:** the headers are sent with every page, by `Response::html()`, so no page can forget them; checks fail closed. **KISS:** a short list of habits, used everywhere, beats a long list of special cases. **DRY:** each habit lives in one place in *Shelf*: `e()`, `Csrf`, `App::refused()`, `Response::html()`.

## Security

This chapter is the security section. Two habits for every project:

- **Check your dependencies.** `composer audit` compares the versions in `composer.lock` with a public list of known vulnerabilities, and lists the ones to update. Run it before each release, and in CI.
- **Keep a list of what could go wrong.** For each new page, ask: who may use it (A01)? What comes in, and where does it go (A05)? What if it fails (A10)? The answers become tests.

## Shelf

*Shelf* sends the security headers with every page, and its pages are checked against the list.

**The headers,** in `Response::html()`, so that every page gets them, `shelf/src/Web/Response.php`:

<!-- include: code/64-security/shelf/src/Web/Response.php -->

*Shelf* uses no JavaScript, so its policy is strict: `script-src 'none'`. Its stylesheet and covers come from the site itself (`'self'`).

**The test,** `shelf/tests/Web/AppTest.php`, checks that a page carries them:

<!-- include: code/64-security/shelf/tests/Web/AppTest.php -->

**The review.** Each of *Shelf*'s pages, against the list:

| Page | Who may use it (A01) | Input, and where it goes (A05) | When it fails (A10) |
|---|---|---|---|
| `GET /`, `/book`, `/cover` | everyone | a title, in a prepared statement; escaped in HTML | `404`, the same page as any unknown path |
| `POST /add`, `/copies`, `/remove` | staff, checked in `refused()`; the token | fields, checked by `Library`; covers by their content | `422` with the reason; nothing half done (57) |
| `/login`, `/logout` | everyone; the token | the name in a prepared statement; the password only to `password_verify()` | one message for every mistake |
| `/loans` | members, their own | the name from the session only | no loans: an empty list |

`shelf/web.out` shows the headers on every page now.

## Exercises

The exercises are in `exercises/64-security/`. Check your work with:

```
composer exercise 64 beginner
```

1. **Beginner, `page.php`:** send the security headers with a page.
2. **Intermediate, `guestbook.php`:** two holes from the list, in a guest book and in the page a login returns to; close them.
3. **Advanced, `access.php`:** a check that fails open, and lets strangers in; make it fail closed.

## Summary

- Security is a set of small habits, used on every page, from the first line.
- The OWASP Top 10 names the most common risks; access control and injection are the classics.
- Defence in depth: escaping and a Content Security Policy, tokens and `samesite`.
- A check that can't decide says no: fail closed.
- `composer audit` checks your libraries for known vulnerabilities.

## Further reading

- OWASP Top 10:2025: https://top10.owasp.org/2025/
- MDN, Content Security Policy: https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CSP
- OWASP, HTTP Security Response Headers Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Headers_Cheat_Sheet.html
- Composer documentation, `audit`: https://getcomposer.org/doc/03-cli.md#audit
