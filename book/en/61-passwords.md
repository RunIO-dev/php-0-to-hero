# 61. Passwords

## Goals

- You will be able to store passwords so that a stolen database doesn't give them away.
- You will be able to hash a password with `password_hash`, and check one with `password_verify`.
- You will be able to update old hashes with `password_needs_rehash`.
- You will be able to give *Shelf*'s members passwords.

## Before you start

- Chapter 54: PDO and prepared statements.
- Chapter 58: migrations.
- Chapter 24: reading input on the command line.

## The idea

Part 9 is about users: who someone is (this chapter and the next), and what they may do (chapter 63). It starts with the oldest way to prove who you are: a password.

A website has to check passwords, but it must never *keep* them. Databases get copied: through a SQL injection, a lost backup, a server that was set up wrong, or a person who shouldn't have access. If the table holds the passwords themselves, every account is open at once. Worse, people use the same password on many sites, so the damage spreads to their email and their bank.

The answer is a **hash**: a function that turns a password into a fixed-length string, and can't be run backwards. To check a login, the site hashes the typed password and compares. The site never needs the password itself again.

Not any hash will do. A hash for passwords needs two more things:

- **A salt.** A random value, different for every password, mixed in before hashing and stored with the hash. Two members with the same password then get different hashes, and an attacker can't hash a list of common passwords once and look them all up.
- **Slowness.** Hashes such as SHA-256 are made to be fast: a graphics card computes billions per second. A password hash is made slow on purpose, about a quarter of a second, with a **cost** you can raise. A quarter of a second doesn't matter at a login, but it makes guessing millions of passwords very expensive.

PHP has all of this in two functions: `password_hash()` and `password_verify()`. Its default algorithm, **bcrypt**, adds the salt by itself and writes the algorithm, the cost and the salt into the hash. You never handle the salt.

## In code

### Hashing

<!-- include: code/61-passwords/hash.php -->

- `PASSWORD_DEFAULT` lets PHP choose the algorithm. Today it is bcrypt; a later PHP version may choose a stronger one, and your code stays the same.
- The hash is 60 characters, and the same password gives a different hash each time: a new salt.
- `$2y$12$` at the start says bcrypt (`2y`), cost 12. The cost is a power of two: cost 13 takes twice as long as 12. The salt and the hash itself follow.
- Store the whole string, in a text column. Other algorithms make longer hashes, so don't limit the column to 60.

### Checking

<!-- include: code/61-passwords/verify.php -->

`password_verify()` reads the algorithm, cost and salt from the stored hash, hashes the typed password the same way, and compares the two in constant time (chapter 51). Case matters, and so do spaces: don't `trim()` a password, someone may have chosen spaces on purpose.

### Updating old hashes

<!-- include: code/61-passwords/rehash.php -->

Computers get faster, so a good cost today is too low in a few years, and PHP raises its default from time to time (8.4 went from 10 to 12). `password_needs_rehash()` says whether a stored hash was made with other settings than the ones you ask for now. The only moment you can make a new hash is right after a successful login, because only then do you have the password. So: verify, then rehash if needed, then store.

## Common mistakes

### A fast hash without a salt

<!-- include: code/61-passwords/fast-hash.php -->

✗ Amira and Jonas have the same hash, so whoever cracks one has both. A list of common passwords, hashed once, finds every member who used one of them. With SHA-256, that takes moments. `md5()` and `sha1()`, still common in old tutorials, are faster still; `tools/verify` refuses them near the word "password".

**Fix:** `password_hash()` and `password_verify()`. If you take over a site with old hashes, convert each one at its member's next login, as in `rehash.php`.

### Passwords longer than bcrypt reads

<!-- include: code/61-passwords/too-long.php -->

⚠ bcrypt reads only the first 72 bytes of a password. Two long passwords that start the same are the same password. 72 bytes are 72 English letters, but only 36 Arabic ones, since each Arabic letter takes two bytes in UTF-8.

**Fix:** refuse a password over 72 bytes, with a message, as *Shelf* does below. Where PHP has it, `PASSWORD_ARGON2ID`, another algorithm, has no such limit; `password_verify()` checks both kinds, so a site can switch later and rehash at login.

### Passwords in logs and errors

A password is sent in a form, so it lands wherever form data goes: a log of requests, an error report that prints the `POST` fields, a stack trace. PDO's connection parameters are hidden in stack traces (chapter 59), but your own code isn't.

**Fix:** never log form fields wholesale. Chapter 66 logs failed logins with the name, and never the password. PHP's `#[\SensitiveParameter]` attribute hides a function's parameter in stack traces.

## Principle check

**Secure by default:** only a slow, salted hash is stored; the password is gone the moment it is hashed. **KISS:** two functions do it all, and the hash carries its own settings. **YAGNI:** no home-made scheme of salts and rounds: `PASSWORD_DEFAULT` gets better with PHP.

## Security

- **Length beats rules.** A long passphrase, such as four ordinary words, is harder to guess and easier to remember than `P@ssw0rd!`. Current guidance (NIST SP 800-63B) asks for at least 15 characters when a password is the only proof of who someone is, and advises against forced symbols and regular changes. Checking new passwords against a list of known leaked ones helps more than any rule about characters.
- **Hashing doesn't stop guessing at the login form.** An attacker can still try passwords one by one through your site. Chapter 62 shows how to slow that down.
- **The time a login takes** can tell an attacker whether a name exists: a name without a hash would answer faster. *Shelf* checks against a fixed hash in that case, so every login takes the same time.
- **HTTPS** (Part 12) protects the password on its way to the server. Hashing protects it once it is there.

## Shelf

Members get passwords. *Shelf* stores each one's hash in the members table, and can check a name and password, which the login in chapter 62 will use.

**The column,** a new migration, `shelf/migrations/004-member-passwords.sql`:

<!-- include: code/61-passwords/shelf/migrations/004-member-passwords.sql -->

**The repository** reads and stores the hash. `shelf/src/MemberRepository.php`:

<!-- include: code/61-passwords/shelf/src/MemberRepository.php -->

`shelf/src/PdoMemberRepository.php`:

<!-- include: code/61-passwords/shelf/src/PdoMemberRepository.php -->

**The rules** are in a new class, `shelf/src/Accounts.php`:

<!-- include: code/61-passwords/shelf/src/Accounts.php -->

- `setPassword()` refuses a password under 15 characters, counted with `mb_strlen()`, and one over 72 bytes, counted with `strlen()`.
- `authenticate()` returns the `Member`, or `null` for a wrong name, a wrong password, or a member without a password. The caller can't tell those apart, and shouldn't: "wrong name or password" tells an attacker nothing.
- When the member has no hash, `password_verify()` still runs, against `NO_ACCOUNT`, a hash of a random value nobody knows. That keeps the time the same.
- `$options` is passed to `password_hash()`. The application passes nothing and gets PHP's defaults. The tests pass `['cost' => 4]`, the lowest, so that hashing doesn't slow every test down; `password_needs_rehash()` compares with the same options.

**The tests,** with the members in memory, `shelf/tests/AccountsTest.php`:

<!-- include: code/61-passwords/shelf/tests/AccountsTest.php lines 14-67, 75-87 -->

`shelf/tests/InMemoryMemberRepository.php` keeps the hashes in an array:

<!-- include: code/61-passwords/shelf/tests/InMemoryMemberRepository.php -->

The database version gets two tests, `shelf/tests/Database/PdoMemberRepositoryTest.php`:

<!-- include: code/61-passwords/shelf/tests/Database/PdoMemberRepositoryTest.php -->

**The command line** gets `password <member>`. It reads the new password from the input, never from the arguments: arguments show up in the shell's history and in the list of running programs, where other users of the computer can see them. `shelf/shelf.php`:

<!-- include: code/61-passwords/shelf/shelf.php -->

Typed at a terminal, it asks for the password, and you type it and press Enter (the terminal shows what you type). The session below reads it from a file instead, `< amira-password.txt`, as the checks of this book can't type:

```
php shelf/shelf.php password Amira
```

## Exercises

The exercises are in `exercises/61-passwords/`. Check your work with:

```
composer exercise 61 beginner
```

1. **Beginner, `store.php`:** what to store for a new password, and how to check one.
2. **Intermediate, `rules.php`:** rules for a new password, counted in characters and in bytes.
3. **Advanced, `login.php`:** check a login against a database, and replace old hashes.

## Summary

- Never store passwords; store a slow, salted hash: `password_hash($password, PASSWORD_DEFAULT)`.
- Check a login with `password_verify()`; it reads the settings from the hash and compares in constant time.
- After a successful login, `password_needs_rehash()` says whether to store a new hash.
- bcrypt reads 72 bytes at most: refuse longer passwords, or use Argon2id.
- Ask for long passwords, at least 15 characters, rather than for symbols.

## Further reading

- PHP manual, password hashing: https://www.php.net/manual/en/book.password.php
- PHP manual, `password_hash`: https://www.php.net/manual/en/function.password-hash.php
- OWASP, Password Storage Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html
- NIST SP 800-63B, Digital Identity Guidelines, authentication: https://pages.nist.gov/800-63-4/sp800-63b.html
