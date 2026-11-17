# 76. Security in Symfony

## Goals

- You will be able to let Symfony's security log people in: a firewall, a user provider, a password hasher.
- You will be able to give members roles, and roles that include others.
- You will be able to decide who may do what with a voter, and ask it in a controller, an attribute and a template.
- You will be able to log programs in with API tokens, in a firewall of their own.

## Before you start

- Chapter 61: password hashes.
- Chapters 62 and 63: logging in, sessions and roles.
- Chapter 69: tokens for an API.
- Chapters 73 to 75: controllers, Doctrine and forms in Symfony.

## The idea

*Shelf* has written its own security since chapter 61: `Accounts` checks a password, the login page starts the session, `RequireLogin` sends a visitor without one to the login, `Role::mayChangeBooks()` decides who may add books, and the API reads its token from the `Authorization` header by hand. Each piece is small, and each was a lesson. Together they are a lot of code to keep right, and one forgotten check is a hole.

Symfony's security (`symfony/security-bundle`) splits the work into two questions:

- **Who is this?** That is **authentication**. A **firewall** covers a part of the site, chosen by a pattern of paths, and knows how a visitor proves who they are there: a login form and a session for people, a token in each request for programs. A **user provider** finds the user by what they typed, such as a name; a **password hasher** checks the password against the stored hash. A user is any class that implements `UserInterface`: *Shelf*'s `Member`.
- **What may they do?** That is **authorization**. Each user has **roles**, strings that start with `ROLE_`: `ROLE_READER`, `ROLE_STAFF`. A **role hierarchy** says that one role includes others: staff may do whatever a reader may. `access_control` asks for a role by path. For a question about a thing, "may Amira change this review?", a **voter** decides: a small class that Symfony asks whenever code calls `isGranted()`.

Every check goes through `isGranted()`: in a controller, in the attribute `#[IsGranted]`, in a template with `is_granted()`. The rule itself lives in one place, the voter, and the check is one line wherever it is needed.

A visitor who isn't logged in and asks for a page that needs a role is sent to the login. A member who is logged in, but has the wrong role, gets `403 Forbidden`. The firewall checks the CSRF token of the login and the logout, and slows down a visitor who guesses passwords: chapter 62's advanced exercise, built in.

## In code

The examples are a Composer project of their own (`code/76-symfony-security/composer.json`), with two of the security's components:

<!-- include: code/76-symfony-security/composer.json -->

### Hashing passwords

<!-- include: code/76-symfony-security/hash.php -->

<!-- include: code/76-symfony-security/hash.out -->

- `auto` picks the best algorithm PHP has, today bcrypt, and checks the hashes of the others too, as `password_verify()` does.
- `needsRehash()` is true for a hash made with a lower cost, or an older algorithm: the right moment to make it again is the next login, when the password is known. Symfony's security does it on its own, for a user provider that implements `PasswordUpgraderInterface`.

### Roles that include others

<!-- include: code/76-symfony-security/roles.php -->

<!-- include: code/76-symfony-security/roles.out -->

A member with `ROLE_STAFF` passes every check for `ROLE_READER`. Their own roles stay one string: the hierarchy is written once, in the configuration.

### A voter

<!-- include: code/76-symfony-security/voter.php -->

<!-- include: code/76-symfony-security/voter.out -->

- `supports()`: is this question for this voter? A voter that says no **abstains**; another voter may answer.
- `voteOnAttribute()`: the answer, from the user in the token and the subject.
- The `AccessDecisionManager` asks every voter. By default, one yes is enough; in an application it is behind `isGranted()`.

## Common mistakes

### The rules in the wrong order

<!-- include: code/76-symfony-security/order.bad.yaml -->

✗ `access_control` uses the first rule whose path matches, and only that one. `^/` matches every path, `/admin` too, so the admin pages are open to everyone.

**Fix:** the most specific paths first. Better still, check the role in the controller as well, with `#[IsGranted]`: a rule next to the code it protects can't be lost in a list.

### Hidden is not protected

<!-- include: code/76-symfony-security/hidden.bad.php -->

✗ The template shows the button only to staff. The address still answers anyone who sends the request, with `curl` or a form of their own.

**Fix:** the check in the controller, `#[IsGranted('ROLE_STAFF')]`. The template may hide the button too, for the visitor's convenience.

### Comparing roles by hand

`if (in_array('ROLE_STAFF', $member->getRoles()))` misses the hierarchy: an admin, who includes staff, is refused. And the rule is copied to every place that checks it.

**Fix:** `isGranted()`, which knows the hierarchy and asks the voters; and a voter, when the rule is more than a role.

## Principle check

**DRY:** who may change books is decided in one voter, which the controller, the API and the templates all ask. **Secure by default:** every page under a firewall knows its user; the login and the logout check their CSRF token; wrong passwords are slowed down. **KISS:** *Shelf* keeps `Role::mayChangeBooks()`; the voter is three lines around it.

## Security

- **One firewall per kind of visitor.** The API's firewall is `stateless`: it never starts a session, so a program's token can't turn into a cookie, and a cookie never logs a program in.
- **Throttled logins.** `login_throttling` allows five wrong passwords per name and address in fifteen minutes, then refuses until the time is up (`symfony/rate-limiter` counts them).
- **No password in the user.** `Member` holds only the hash; `getPassword()` returns it so the firewall can check a typed password against it. The log names who tried to log in, never what they typed.
- **A 403 is logged.** A member who asks for a page they may not see is either lost or probing: *Shelf* writes it into the log (chapter 66).

## Shelf

*Shelf*'s own login, `RequireLogin` and the token check are replaced by Symfony's security. Every page and every API answer stays as it was, so `web.http` checks the same transcript.

```
cd shelf
composer require symfony/security-bundle symfony/rate-limiter
```

`shelf/composer.json`:

<!-- include: code/76-symfony-security/shelf/composer.json -->

Flex adds the bundle to `shelf/config/bundles.php`:

<!-- include: code/76-symfony-security/shelf/config/bundles.php -->

### The configuration

`shelf/config/packages/security.yaml`:

<!-- include: code/76-symfony-security/shelf/config/packages/security.yaml -->

- `password_hashers`: `auto` checks the hashes `Accounts` made with `password_hash()`, so every member keeps their password.
- `providers`: the members come from Doctrine, by their name.
- `firewalls`: `api` for `/api/`, with a token and no session; `main` for everything else, with the login form, the logout and the throttling. The first firewall whose pattern matches is the one used.
- `form_login` reads the fields `name`, `password` and `_token`, as chapter 62's form sends them: the form stays the same.
- `access_control`: `/loans` needs a reader. The pages for staff ask the voter themselves.
- In the test environment, a cost of 4, as chapter 61's tests used.

The logout address is a route of the security's own, `shelf/config/routes/security.yaml`:

<!-- include: code/76-symfony-security/shelf/config/routes/security.yaml -->

### The user

`Member` becomes a user, `shelf/src/Member.php`:

<!-- include: code/76-symfony-security/shelf/src/Member.php -->

- `getUserIdentifier()`: the name, as the provider looks it up.
- `getRoles()`: `ROLE_READER` or `ROLE_STAFF`, from the `Role` it already has.
- `eraseCredentials()` is still part of the interface in Symfony 7, and marked as old: Symfony 8 removes it. A `Member` holds no typed password, so it does nothing.
- A name may no longer be empty: an empty name couldn't log in.

### Who may change books

`shelf/src/Security/BookVoter.php`:

<!-- include: code/76-symfony-security/shelf/src/Security/BookVoter.php -->

The controller asks it with an attribute on each action that changes books, `shelf/src/Controller/BookController.php`:

<!-- include: code/76-symfony-security/shelf/src/Controller/BookController.php -->

`#[IsGranted]` runs before the action: a visitor without a session is sent to the login, a reader gets `403`.

### The login

The firewall reads the form's `POST` itself, so the controller only shows the form, `shelf/src/Controller/AccountController.php`:

<!-- include: code/76-symfony-security/shelf/src/Controller/AccountController.php -->

- `AuthenticationUtils` knows the name that was typed and whether the last try failed.
- `#[CurrentUser]` gives the action the logged-in `Member`. `/loans` needs a reader, so there always is one.

The form says only that the name or the password was wrong, never which, `shelf/templates/login.html.twig`:

<!-- include: code/76-symfony-security/shelf/templates/login.html.twig -->

The welcome, the goodbye and the log lines of chapter 62 follow the security's events, `shelf/src/EventSubscriber/LoginMessages.php`:

<!-- include: code/76-symfony-security/shelf/src/EventSubscriber/LoginMessages.php -->

`RequireLogin` (chapter 63) and `CurrentMember` (73) are removed: the firewall does their work. `CheckCsrf` stays for the other forms, `shelf/src/EventSubscriber/CheckCsrf.php`:

<!-- include: code/76-symfony-security/shelf/src/EventSubscriber/CheckCsrf.php -->

### The pages

Templates know the member as `app.user`, and ask the voter with `is_granted()`, `shelf/templates/base.html.twig`:

<!-- include: code/76-symfony-security/shelf/templates/base.html.twig -->

`shelf/templates/book.html.twig`:

<!-- include: code/76-symfony-security/shelf/templates/book.html.twig -->

The Twig global `current` is gone, `shelf/config/packages/twig.yaml`:

<!-- include: code/76-symfony-security/shelf/config/packages/twig.yaml -->

A `403` shows *Shelf*'s "Not allowed" page and goes into the log; a logout without the right token, a link or another site's form, shows the "expired" page, `shelf/src/EventSubscriber/ErrorPages.php`:

<!-- include: code/76-symfony-security/shelf/src/EventSubscriber/ErrorPages.php -->

### The API

The `access_token` firewall reads `Authorization: Bearer <token>`, and asks a handler whose token it is, `shelf/src/Security/ApiTokenHandler.php`:

<!-- include: code/76-symfony-security/shelf/src/Security/ApiTokenHandler.php -->

A wrong token gets the same `401` problem as before, `shelf/src/Security/ApiTokenFailure.php`:

<!-- include: code/76-symfony-security/shelf/src/Security/ApiTokenFailure.php -->

The API's controller takes the member with `#[CurrentUser]`, `null` without a token, and asks the same voter, `shelf/src/Controller/ApiController.php`:

<!-- include: code/76-symfony-security/shelf/src/Controller/ApiController.php -->

### The tests

The voter, `shelf/tests/Security/BookVoterTest.php`:

<!-- include: code/76-symfony-security/shelf/tests/Security/BookVoterTest.php -->

The token handler, `shelf/tests/Security/ApiTokenHandlerTest.php`:

<!-- include: code/76-symfony-security/shelf/tests/Security/ApiTokenHandlerTest.php -->

### The check

`shelf/web.http` sends the same requests as before. Symfony's CSRF tokens change at every login and logout, so it reads the token again after each:

<!-- include: code/76-symfony-security/shelf/web.http -->

`/add` without a session goes to the login; a wrong password comes back to the form with the name kept; Amira, a reader, gets `403` for `/add`; a `GET /logout`, without a token, logs nobody out.

## Exercises

The exercises are in `exercises/76-symfony-security/`. Check your work with:

```
composer exercise 76 beginner
```

1. **Beginner, `login.php`:** check a password with Symfony's hasher, and make its hash again when it is weaker than today's settings.
2. **Intermediate, `ReviewVoter.php`:** a voter for reviews: only the author edits, the author or staff delete.
3. **Advanced, `ApiKeyHandler.php`:** a token handler for API keys that are stored hashed, expire, and give one role per scope.

## Summary

- A firewall decides who the visitor is: a login form and a session for people, a token for programs.
- A user provider finds the user, a password hasher checks the password; the user class implements `UserInterface`.
- Roles say what a user may do; a role hierarchy lets one role include others.
- A voter decides a question about a thing; `isGranted()` asks it everywhere: in controllers, attributes and templates.
- Check in the controller; hiding a link is only a convenience.

## Further reading

- Symfony documentation, Security: https://symfony.com/doc/7.4/security.html
- Symfony documentation, How to use voters to check user permissions: https://symfony.com/doc/7.4/security/voters.html
- Symfony documentation, How to authenticate with access tokens: https://symfony.com/doc/7.4/security/access_token.html
- Symfony documentation, Password hashing and verification: https://symfony.com/doc/7.4/security/passwords.html
- OWASP, Authorization Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html
