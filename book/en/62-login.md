# 62. Login and logout

## Goals

- You will be able to log a member in: check the password, then remember them in the session.
- You will be able to give the session a new ID whenever who the visitor is changes.
- You will be able to log a member out safely, with a form, never a link.
- You will be able to answer a failed login without telling a stranger which part was wrong.

## Before you start

- Chapter 61: password hashes, and *Shelf*'s `Accounts`.
- Chapter 50: sessions, and session fixation.
- Chapter 51: CSRF tokens.

## The idea

HTTP forgets every visitor between two requests (chapter 50). A password proves who someone is for one request, the one that sends it. Nobody wants to type it on every page, so after a good password the server writes into the session who this visitor is: `$_SESSION['member'] = 'Amira'`. Every later request carries the session cookie, and the server reads the name from its own session data. That is being **logged in**.

The session ID is now as good as the password: whoever sends it is Amira, until she logs out or the session ends. Three rules follow from that:

- **A new ID at login.** If the ID stayed the same, someone who knew it before the login (session fixation, chapter 50) would be logged in too. `session_regenerate_id(true)` gives the session a new ID and deletes the old one; the data stays.
- **A new ID at logout too.** Logging out removes the member from the session, and the session gets another ID, so an ID copied while logged in leads nowhere.
- **Logging out is a `POST`, with the CSRF token.** It changes something, so it is never a link: a link can be followed by an image tag on another site, or by a browser that loads links ahead of time.

A failed login gets one answer, whatever went wrong: "Wrong name or password." Saying "There is no member named Jonas" would let anyone test which names have an account, and then try passwords only on those.

## In code

The example is a small site of three pages. They share `code/62-login/account.php`: the session, the members' hashes as a database would hold them, the CSRF token, and who is logged in:

<!-- include: code/62-login/account.php -->

### Logging in

<!-- include: code/62-login/login.php -->

- The password is read without `trim()`: spaces at the start or the end belong to the password.
- Only when the name exists *and* `password_verify()` says yes, the session gets a new ID, and then the member's name.
- Otherwise the page answers `422`, with one message for every mistake, and the form again with the name that was typed. The password never comes back in the page.
- `autocomplete="username"` and `"current-password"` tell password managers what the fields are, so they can fill them in.

### A page for members, and logging out

`code/62-login/home.php`:

<!-- include: code/62-login/home.php -->

`code/62-login/logout.php`:

<!-- include: code/62-login/logout.php -->

Here are the requests, in order:

<!-- include: code/62-login/login.http -->

- A wrong password and an unknown name get the same answer, `422`, with the same message.
- The good login answers `303` with a new `Set-Cookie`: the session's new ID. The browser sends that one from now on.
- After the logout, the session has yet another ID, and no member.

## Common mistakes

### Telling which part was wrong

<!-- include: code/62-login/messages.bad.php -->

✗ Two messages: whoever tries names learns which ones exist. They can then send Amira an email that looks like it comes from the library, or try common passwords on her account alone.

**Fix:** one message for both, "Wrong name or password.", as in `login.php`. A "forgot your password" page should answer the same way: "If the name exists, we sent an email."

### Logging out with a link

`<a href="/logout.php">Log out</a>` changes the session on a `GET`. Any page on any site can log your members out with `<img src="https://shelf.example/logout.php">`, and browsers that load links ahead of time can do it by accident.

**Fix:** a small form with the token and a button, as in `home.php`. The button can look like a link; *Shelf*'s does.

### Keeping the old session ID

Without `session_regenerate_id(true)`, the ID from before the login keeps working after it. On a shared computer, or with an ID planted in a link (chapter 50), someone else is now logged in as your member.

**Fix:** a new ID at login and at logout. `true` matters: without it, the old ID stays valid for a while.

## Principle check

**Secure by default:** the session ID changes whenever the member does, failed logins say nothing about who exists, and logging out needs the token. **KISS:** being logged in is one value in the session. **DRY:** *Shelf* checks the password in `Accounts` (chapter 61), and the web pages only call it.

## Security

- **Guessing.** A login form invites attackers to try passwords: thousands of common ones on one name, or one common password on thousands of names. Slow them down by counting failed logins per name and refusing for a while after five, as the advanced exercise does. Count per name, not per IP address alone: attackers spread their tries over many addresses. Don't lock an account for good, or an attacker can lock out any member at will.
- **How long a login lasts.** A session ends when the browser closes, or after PHP's `session.gc_maxlifetime` (24 minutes without a request by default). A "remember me" option needs a separate, long-lived token of its own, stored hashed like a password; *Shelf* doesn't offer one.
- **HTTPS.** The session cookie is marked `secure` (chapter 50), so browsers send it only over HTTPS, and on `localhost` while you develop. Part 12 puts *Shelf* behind HTTPS.
- **Before risky changes**, such as a new password or a new email address, ask for the current password again. Someone at an unlocked computer can then use the session, but not take the account over.

## Shelf

Members can log in, with the password from chapter 61, and log out. The bar at the top of every page shows who is logged in.

**The session** gets a new method, `shelf/src/Web/Session.php`:

<!-- include: code/62-login/shelf/src/Web/Session.php -->

`shelf/src/Web/PhpSession.php`:

<!-- include: code/62-login/shelf/src/Web/PhpSession.php -->

The test session counts how often it got a new ID, so the tests can check it, `shelf/tests/Web/ArraySession.php`:

<!-- include: code/62-login/shelf/tests/Web/ArraySession.php -->

**The request** gets `secret()`, a form field as it was sent, without `trim()`: `shelf/src/Web/Request.php`:

<!-- include: code/62-login/shelf/src/Web/Request.php -->

**The pages.** `App` gets the `Accounts` of chapter 61, and three routes: `GET /login` shows the form, `POST /login` logs in, `POST /logout` logs out. Its new `page()` method gives the layout the member's name, and the token for the logout form. A visitor who isn't logged in gets no token there, so a visitor who only reads still gets no session cookie. `shelf/src/Web/App.php`:

<!-- include: code/62-login/shelf/src/Web/App.php -->

`Templates::page()` passes those extra values to the layout, `shelf/src/Web/Templates.php`:

<!-- include: code/62-login/shelf/src/Web/Templates.php -->

The layout shows "Log in", or the member's name and a button to log out, `shelf/templates/layout.php`:

<!-- include: code/62-login/shelf/templates/layout.php -->

The login form, `shelf/templates/login.php`:

<!-- include: code/62-login/shelf/templates/login.php -->

The front controller makes the `Accounts`, `shelf/public/index.php`:

<!-- include: code/62-login/shelf/public/index.php -->

`shelf/public/style.css` gets three rules at the end, for the name and the button in the bar, and for the narrow login form. Copy the file, as in chapter 49.

**The tests.** `AppTest` gives Amira a password, with the low cost of chapter 61, and checks the login, the new session ID, the one message, the untrimmed password, the bar and the logout. `shelf/tests/Web/AppTest.php`:

<!-- include: code/62-login/shelf/tests/Web/AppTest.php -->

`shelf/web.http` opens the login form and sends a login. Its database is new, without passwords, so the login fails, with the usual message; then a `GET /logout`, which is refused:

<!-- include: code/62-login/shelf/web.http -->

To log in yourself, give a member a password (chapter 61), then start the web server, and open `http://localhost:8000/login`:

```
php shelf/shelf.php password Amira
docker compose up web
```

## Exercises

The exercises are in `exercises/62-login/`. Check your work with:

```
composer exercise 62 beginner
```

1. **Beginner, `logout.php`:** a logout page that takes only a `POST` with the token, and gives the session a new ID.
2. **Intermediate, `login.php`:** a login that checks the password, and answers every mistake the same way.
3. **Advanced, `throttle.php`:** count failed logins per name, and refuse for 15 minutes after five.

## Summary

- Logged in means: the server wrote the member's name into the session, after a good password.
- Give the session a new ID at login and at logout: `session_regenerate_id(true)`.
- Log out with a `POST` form and the CSRF token, never with a link.
- Answer every failed login with one message, so nobody learns which names exist.
- Slow down guessing by counting failed logins per name.

## Further reading

- PHP manual, `session_regenerate_id`: https://www.php.net/manual/en/function.session-regenerate-id.php
- PHP manual, session security: https://www.php.net/manual/en/features.session.security.management.php
- OWASP, Authentication Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html
- OWASP, Session Management Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html
