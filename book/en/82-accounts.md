# 82. Accounts

## Goals

- You will be able to let people sign up themselves, with an email address they confirm by a link.
- You will be able to send email with Symfony Mailer, and read every message on your computer with Mailpit.
- You will be able to make links that can't be forged, that expire, and that work only once, without a table of tokens.
- You will be able to write account pages that don't tell strangers who has an account.
- You will be able to let members change their password and address, and delete their account.

## Before you start

- Chapter 61: password hashes, and the rules for a password.
- Chapter 62: logging in; chapter 76: Symfony's security.
- Chapter 77: testing pages with a browser for tests.
- Chapter 81: *Shelf*'s catalogue.

## The idea

Until now, every member of *Shelf* came from `members.json`, and got a password from the console. A real library lets readers sign up on its site. That brings four things every site with accounts needs: **sign-up**, **email confirmation**, **"forgot your password?"**, and a page for **one's own account**, including a way to delete it.

**Email proves an address.** Anyone can type any address into a form. So the account starts unconfirmed, Shelf sends a link to the address, and only whoever can read that inbox can use the link. Until then, the right password isn't enough to log in. A link from the email also proves the address when a member forgets their password, or wants a new address.

**Signed links instead of tokens.** A link like `/confirm?member=7&email=ana%40example.org` must not be something anyone could type. Symfony's **UriSigner** adds two parameters: `_expiration`, a time, and `_hash`, an HMAC of the whole address made with the app's secret (chapter 72). Change one letter, and the hash no longer matches; wait too long, and the time has passed. Shelf only has to check the link, so it stores nothing.

A reset link must also work **once**. It carries a fingerprint of the member's current password hash. Once the new password is saved, the hash is different, and the old link no longer matches, though its signature is still good.

**Say nothing about others.** "There is no account with this address" tells a stranger who isn't a member, and so who is. Shelf's pages answer the same for every address. "If an account has this address, we have sent it a link." Sign-up with an address that already has an account looks like any sign-up. The email then tells the address's owner what happened. Only the inbox learns the truth.

**Mail, on your computer.** Symfony Mailer sends through the transport that `MAILER_DSN` names: `smtp://…` for a mail server, `sendmail://default` for the server's own mail program, `null://null` to send nothing. While you work, **Mailpit** pretends to be a mail server: it keeps every message, and shows them at http://localhost:8025. Nothing reaches a real inbox. In the tests, nothing is sent at all; `assertEmailCount()` and `getMailerMessage()` read what would have been.

**Limits.** A form that sends email lets anyone make Shelf write to anyone. A rate limit (Symfony's RateLimiter, chapter 76's login throttling uses it too) allows five such emails an hour per visitor. After that, the answer is "429 Too Many Requests".

## In code

The examples are a Composer project of their own (`code/82-accounts/composer.json`):

<!-- include: code/82-accounts/composer.json -->

### A signed link

<!-- include: code/82-accounts/signed.php -->

<!-- include: code/82-accounts/signed.out -->

- The signature covers the whole address: another email in it is refused, as is a link without any signature.
- `MockClock` from `symfony/clock` moves time forward; two days and a minute later, the same link has expired.

### A link that works once

<!-- include: code/82-accounts/once.php -->

<!-- include: code/82-accounts/once.out -->

- The signature is still good the second time; the fingerprint is not.
- `hash_equals()` compares secrets in a time that doesn't depend on where they differ.

### An email

<!-- include: code/82-accounts/mail.php -->

<!-- include: code/82-accounts/mail.out -->

- The transport is chosen by the DSN, not by the code: the same code sends to Mailpit, a mail server, or nowhere.
- Every email gets a text part. Some people read mail as text, and spam filters look at it.

## Common mistakes

### A page that tells who is a member

<!-- include: code/82-accounts/forgot.bad.php -->

✗ Anyone can type addresses and learn which have an account: a list for spam, and for guessing passwords.

**Fix:** the same answer for every address. The email goes only to an account that exists.

### A GET that confirms

A link that confirms the address as soon as it is opened: many mail programs open links to check them for danger, so the account gets confirmed though nobody clicked.

**Fix:** the link's page shows a button. The button sends a POST to the same signed address, and only the POST changes anything (chapter 47).

### Reset tokens that never end

A token in a table, without an expiry, that stays valid after use: an old email in a mailbox that is broken into years later still opens the account.

**Fix:** a link that expires (an hour), and that the new password ends.

### A changed password that leaves other sessions open

Someone used the account from another computer, and the member changes the password. If the other session stays logged in, the change was for nothing.

**Fix:** Symfony ends every session whose password hash has changed. *Shelf* logs the member in again, in the session where they changed it.

## Principle check

**KISS:** signed links, no table of tokens. One `confirm` link serves both sign-up and a new address. **Secure by default:** links expire and are signed; reset links work once; the pages say nothing about other people's accounts; every form that sends email is limited; a change to the account asks for the current password. **YAGNI:** no social login and no two-factor login. Both are good ideas for a larger site, and libraries exist for them (see Further reading).

## Security

- **The app secret signs the links.** Whoever has `APP_SECRET` can make any link: it stays out of Git (chapter 65), and a new secret ends every link sent so far.
- **The current password, again.** Changing the password, changing the address and deleting the account each ask for it. A session someone else finds open on a library computer is not enough.
- **Timing.** Sending an email takes time. A page that answers faster for unknown addresses tells as much as one that says so. Chapter 85 sends emails in the background, which also evens this out.
- **Deleting means deleting.** A deleted account's name, address, password hash and API tokens are gone. Under the GDPR, a member has the right to this. *Shelf* refuses while books are on loan, so the library knows where they are.

## Shelf

```
cd shelf
composer require symfony/mailer:7.4.*
```

The recipe adds `MAILER_DSN` to `.env` and `config/packages/mailer.yaml`. *Shelf* adds a sender, and Mailpit for your computer. `shelf/.env`:

<!-- include: code/82-accounts/shelf/.env -->

`shelf/config/packages/mailer.yaml`:

<!-- include: code/82-accounts/shelf/config/packages/mailer.yaml -->

`compose.yaml` starts Mailpit with the web server:

<!-- include: compose.yaml -->

### Members with an address

`shelf/src/Member.php`:

<!-- include: code/82-accounts/shelf/src/Member.php -->

The repository can find a member by address and by number, save a change, and remove one, `shelf/src/MemberRepository.php`:

<!-- include: code/82-accounts/shelf/src/MemberRepository.php -->

`DoctrineMemberRepository` does it with Doctrine. After its `UPDATE` of a password hash, it refreshes the member that is already loaded, which would otherwise keep the old hash, `shelf/src/Doctrine/DoctrineMemberRepository.php`:

<!-- include: code/82-accounts/shelf/src/Doctrine/DoctrineMemberRepository.php -->

`doctrine:migrations:diff` again wrote a copy of the whole `members` table. Dropping that table would delete every API token with it (`ON DELETE CASCADE`). SQLite can add columns in place, so the migration does that, `shelf/migrations/Version20261118090952.php`:

<!-- include: code/82-accounts/shelf/migrations/Version20261118090952.php -->

### The rules

`Accounts` checks a sign-up and a change of password, `shelf/src/Accounts.php`:

<!-- include: code/82-accounts/shelf/src/Accounts.php -->

`Library` refuses to delete a member who has books, `shelf/src/Library.php`:

<!-- include: code/82-accounts/shelf/src/Library.php -->

### Links and emails

`shelf/src/Web/AccountLinks.php`:

<!-- include: code/82-accounts/shelf/src/Web/AccountLinks.php -->

`shelf/src/Web/AccountMail.php`:

<!-- include: code/82-accounts/shelf/src/Web/AccountMail.php -->

Each email is two templates, text and HTML. The HTML one extends a frame with its styles inside, because mail programs load no stylesheets, `shelf/templates/email/confirm.txt.twig`:

<!-- include: code/82-accounts/shelf/templates/email/confirm.txt.twig -->

`already` and `reset` are alike, in the chapter's snapshot.

### Who may log in

`shelf/src/Security/ConfirmedMembers.php`:

<!-- include: code/82-accounts/shelf/src/Security/ConfirmedMembers.php -->

The firewall asks it, and `/account` is for members, `shelf/config/packages/security.yaml`:

<!-- include: code/82-accounts/shelf/config/packages/security.yaml -->

The limit, `shelf/config/packages/rate_limiter.yaml`:

<!-- include: code/82-accounts/shelf/config/packages/rate_limiter.yaml -->

### The pages

`shelf/src/Controller/AccountController.php`:

<!-- include: code/82-accounts/shelf/src/Controller/AccountController.php -->

- The pages of a link (`confirm`, `reset`) post their form to the same signed address, `app.request.requestUri`, so the POST is checked like the GET.
- After a new password, Symfony ends the member's sessions. `$security->login()` starts this one again.

The account page, `shelf/templates/account.html.twig`:

<!-- include: code/82-accounts/shelf/templates/account.html.twig -->

`signup`, `confirm`, `forgot`, `reset`, `link` (410, a link that doesn't work) and `slow-down` (429) are in the chapter's snapshot. The menu gets "Sign up" and a link to the account.

### Tests

`shelf/tests/Web/AccountsTest.php`:

<!-- include: code/82-accounts/shelf/tests/Web/AccountsTest.php -->

The tests' rate limit has its own pool, which `ShelfWebTestCase` empties before each test. `AccountsTest` and `LibraryTest` test the rules without a browser, and `AccessibilityTest` checks the new pages.

### Try it

```
docker compose up web
```

Sign up at http://localhost:8000/signup, then open http://localhost:8025: the email is there, with its link. `shelf/web.http` signs up too, and shows that the right password isn't enough before the link is used.

## Exercises

The exercises are in `exercises/82-accounts/`. Check your work with:

```
composer exercise 82 beginner
```

1. **Beginner, `name.php`:** whether a name may be used to sign up, in any script.
2. **Intermediate, `ResetLink.php`:** a reset link signed by hand with `hash_hmac()`, which expires and works once.
3. **Advanced, `MailLimit.php`:** a sliding window that limits emails per visitor, and says when to try again.

## Summary

- Members sign up themselves. The account waits until the link in the email is used.
- Symfony Mailer sends through `MAILER_DSN`: Mailpit while you work, nothing in tests.
- Signed links expire, and can't be changed. A fingerprint of the password hash makes a reset link work once.
- Pages answer the same for every address. Only the inbox learns whether it has an account.
- Changes to an account ask for the current password, and a member can delete theirs.

## Further reading

- Symfony documentation, Mailer: https://symfony.com/doc/7.4/mailer.html
- Symfony documentation, signing URIs: https://symfony.com/doc/7.4/routing.html#signing-uris
- Symfony documentation, rate limiter: https://symfony.com/doc/7.4/rate_limiter.html
- Mailpit: https://mailpit.axllent.org/
- OWASP Cheat Sheet, forgot password: https://cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html
- OWASP Cheat Sheet, authentication (account enumeration): https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html
- SymfonyCasts bundles for verifying email and resetting passwords, an alternative with tokens in a table: https://github.com/SymfonyCasts/verify-email-bundle
