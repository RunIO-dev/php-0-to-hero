# 63. Authorization: roles and checks

## Goals

- You will be able to tell authentication (who someone is) from authorization (what they may do).
- You will be able to check on the server, on every request, whether the member may do what they ask.
- You will be able to keep members to their own data, with the member taken from the session, never from the request.
- You will be able to give *Shelf* two roles: readers borrow, staff change books.

## Before you start

- Chapter 62: login, and who is logged in.
- Chapter 31: enums, such as *Shelf*'s `Role`.
- Chapter 44: status codes.

## The idea

Two questions decide every request:

1. **Authentication: who is this?** Chapters 61 and 62 answered it: the member in the session, or a visitor.
2. **Authorization: may they do this?** A reader may borrow books; only the library's staff may add or remove them. Amira may see her loans, and not Jonas's.

A **role** groups members by what they may do: *Shelf* has had `Role::Reader` and `Role::Staff` since chapter 31, for the length of a loan. Now the role also decides what the member may do on the website.

The rules for checks:

- **On the server, on every request.** Hiding the "Remove" button from readers is good for them: they don't see what they can't use. It protects nothing: a form can be sent without the page, with a few lines of code or the browser's developer tools. The page that removes must check, every time.
- **Deny by default.** Write down what each role may do; everything else is refused. A new page that someone forgot to protect should be closed, not open.
- **The member comes from the session.** A page that shows "my loans" takes the name from the session. A name in the address, `/loans?member=Jonas`, can be changed by whoever types it. This mistake, an **insecure direct object reference** (IDOR), is one of the most common on the web: an ID in a request leads to someone else's data, because nobody checked whose it is.
- **Two answers.** A visitor who isn't logged in is sent to the login: maybe they may, once we know who they are. A member who is logged in and may not gets `403 Forbidden`.

Broken access control is the first item of the OWASP Top 10 (chapter 64): the most common serious problem in web applications.

## In code

The example has a few pages that share `code/63-roles/account.php`: the login of chapter 62, now with a role for each member, and `allow()`, the check:

<!-- include: code/63-roles/account.php -->

- `role()` reads the member from the session, and the role from the members' data, not from the session or the request. When a role changes, the next request sees it.
- `allow('staff')` stops the page: a visitor goes to the login with `303`, another role gets `403`. A page that calls it does nothing before it.

The pages: everyone may see the books, `code/63-roles/books.php`:

<!-- include: code/63-roles/books.php -->

Removing a book checks first, `code/63-roles/remove.php`:

<!-- include: code/63-roles/remove.php -->

Each member's loans come from the session's name, `code/63-roles/loans.php`:

<!-- include: code/63-roles/loans.php -->

`code/63-roles/login.php` and `code/63-roles/logout.php` are those of chapter 62. The requests:

<!-- include: code/63-roles/books.http -->

- The visitor is sent to the login when they try to remove a book.
- Amira sees no button, and sends the form anyway: `403`, nothing removed.
- Jonas, staff, sees the buttons, and may remove.

## Common mistakes

### Hiding the button, and nothing else

<!-- include: code/63-roles/remove.bad.php -->

<!-- include: code/63-roles/remove.bad.http -->

✗ Amira never saw a button, but the page didn't ask who she is. The CSRF token doesn't help: it proves the form came from our site, not that she may use it.

**Fix:** `allow('staff')` at the top of the page that does the work, as in `remove.php`.

### Whose data, from the request

<!-- include: code/63-roles/loans.bad.php -->

<!-- include: code/63-roles/loans.bad.http -->

✗ Amira is logged in, so `allow()` lets her through, and the page shows whoever's loans the address names. The same happens with numbers: `/note?id=7` shows note 7, whoever wrote it.

**Fix:** take the member from the session. Where an ID must come from the request, check that the row belongs to the member, in the query itself: `DELETE FROM notes WHERE id = :id AND member = :member`. The advanced exercise does it.

### Checking the role in the browser

JavaScript that hides parts of the page for readers, or a role stored in a cookie, is in the visitor's hands. They can change both.

**Fix:** the role lives on the server, in the database, and the server checks it on each request.

## Principle check

**Secure by default:** every path that changes books needs a role, checked in one place before the route runs; a path nobody listed is open only for reading. **DRY:** *Shelf* writes its rules once, in `Role` and in two lists in `App`. **KISS:** two roles, one method; no permission system until *Shelf* needs one.

## Security

- **Least privilege** (chapter 59) applies to people too: give each member the smallest role they need.
- **Read the role on every request.** *Shelf* keeps only the name in the session, and reads the member from the database each time. A member who is removed, or loses the staff role, loses its rights at the next request, not when their session ends.
- **IDs that can be guessed** make IDOR easy to find, but random IDs don't fix it: the check does.
- **Test the refusals.** A test for "a reader may not remove a book" fails the day someone removes the check. *Shelf*'s tests below do that.

## Shelf

Readers borrow; staff change books. Visitors may look; for anything else, they log in.

**The rule** is in the role, `shelf/src/Role.php`:

<!-- include: code/63-roles/shelf/src/Role.php -->

`Library` gets `findMember()`, so that the website can read the member from the database on each request, `shelf/src/Library.php`:

<!-- include: code/63-roles/shelf/src/Library.php -->

**The check,** in `App`. `MEMBERS_ONLY` lists the paths a visitor can't use, and `STAFF_ONLY` the ones that change books. `refused()` runs for every request, after the route is found and before it runs: a visitor goes to the login, with the message "Log in first."; a reader gets the page "Not allowed", with `403`. The new route `/loans` shows the member's own loans. `shelf/src/Web/App.php`:

<!-- include: code/63-roles/shelf/src/Web/App.php -->

**The templates.** The bar shows "Add a book" to staff only, and "My loans" to every member, `shelf/templates/layout.php`:

<!-- include: code/63-roles/shelf/templates/layout.php -->

A book's page shows its forms only to staff, `shelf/templates/book.php`:

<!-- include: code/63-roles/shelf/templates/book.php -->

The login form shows why the visitor was sent there, `shelf/templates/login.php`:

<!-- include: code/63-roles/shelf/templates/login.php -->

Two new templates, `shelf/templates/forbidden.php`:

<!-- include: code/63-roles/shelf/templates/forbidden.php -->

`shelf/templates/loans.php`:

<!-- include: code/63-roles/shelf/templates/loans.php -->

**The tests.** Each `AppTest` starts with Jonas, staff, logged in, so the tests of earlier chapters keep working; the new ones log in as Amira, or as nobody, and check the refusals, the hidden forms and the loans page. `shelf/tests/Web/AppTest.php`:

<!-- include: code/63-roles/shelf/tests/Web/AppTest.php -->

`TemplatesTest` passes `mayChange`, `shelf/tests/Web/TemplatesTest.php`:

<!-- include: code/63-roles/shelf/tests/Web/TemplatesTest.php -->

**The web check** now needs members with passwords. A `# run` line at the top of `shelf/web.http` runs a command before the server starts, as you would: it imports the books and members, and sets Amira's and Jonas's passwords from two files, `amira-password.txt` and the new `jonas-password.txt`. The requests then try every page as a visitor, as Amira and as Jonas:

<!-- include: code/63-roles/shelf/web.http -->

To try it yourself, give Jonas a password too, then log in as each of them:

```
php shelf/shelf.php password Jonas
docker compose up web
```

## Exercises

The exercises are in `exercises/63-roles/`. Check your work with:

```
composer exercise 63 beginner
```

1. **Beginner, `permissions.php`:** who may do what, in one table, with everything else refused.
2. **Intermediate, `account.php`:** write `allow()`: the login for visitors, `403` for the wrong role.
3. **Advanced, `notes.php`:** each member's notes, deleted only by their owner or by staff, checked in the query.

## Summary

- Authentication says who someone is; authorization says what they may do.
- Check on the server, on every request, before the work; a hidden button protects nothing.
- Deny by default: what no rule allows is refused.
- Take the member from the session; check that an ID from the request belongs to them.
- A visitor goes to the login; a member without the right role gets `403`.

## Further reading

- OWASP Top 10:2025, A01 Broken Access Control: https://top10.owasp.org/2025/A01_2025-Broken_Access_Control/
- OWASP, Authorization Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html
- OWASP, Insecure Direct Object Reference Prevention Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Insecure_Direct_Object_Reference_Prevention_Cheat_Sheet.html
