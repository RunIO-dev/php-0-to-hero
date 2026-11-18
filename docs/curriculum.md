# Curriculum

The course has **89 chapters in 14 parts**. Each chapter has goals, an explanation, tested examples, common mistakes, three exercises (beginner, intermediate and advanced) and a summary. The chapter template is in [standards.md](standards.md).

The course teaches PHP 8.6 (every example also runs on 8.5), with PER Coding Style, PHPUnit and, at the end, Symfony as its one framework. Secure code, KISS, DRY and YAGNI are taught where they apply, from the first example on.

**The project that grows through the course: "Shelf", a small lending library.** It has books, members and loans. Simple enough for a beginner, and it naturally needs everything the course teaches:
- lists and search (arrays);
- dates (due dates);
- files, then a database with relations (a member has loans);
- a login, roles (librarian vs member), forms, an API and tests.

It starts as a command-line script and ends as a Symfony app in production. Each part adds one step, marked ◆ below.

Dependencies run in order: each part needs the parts before it. Notes are in brackets.

---

## Part 1: Getting started (5)
1. **What programming is, and what PHP is for.** Websites, the command line, and where PHP runs. Client and server, in plain words.
2. **Installing PHP 8.6** on Windows, macOS and Linux, from official sources only. Then `php -v`.
3. **`tools/check.php`, the setup check.** What it checks and how to read its fix messages. (Every later chapter refers to it.)
4. **Your editor and the terminal:** the minimum you need.
5. **The first program:** `echo`, running a file, PHP tags, comments, and reading an error message calmly.

## Part 2: Basics (9)
6. Variables and naming. ◆ *Shelf* starts: one book in variables.
7. Data types: int, float, string, bool, null. `var_dump`.
8. Strings: joining, interpolation, the most useful string functions, and multibyte text (`mb_*`). Arabic and German text are real examples.
9. Numbers and arithmetic. Why `0.1 + 0.2` is not 0.3, and money in cents.
10. Comparison and logic: `===` vs `==` (why strict), `&&`, `||`, `!`.
11. `if`, `else` and `match`. *(match before switch: simpler and strict)*
12. Loops: `while`, `for` and `foreach`. Reading loops step by step.
13. Arrays, lists and maps. ◆ the books become an array of arrays.
14. Working with arrays: sort, filter, map, search. ◆ search the shelf by title. *(DRY: the first "rule of three" moment)*

## Part 3: Functions and errors (6)
15. Functions: parameters, return values, why small functions.
16. Types on functions and `declare(strict_types=1)`. *(from here on, in every example)*
17. Default values, named arguments, nullable types.
18. Scope, and why there are no globals.
19. Errors vs exceptions: `throw`, `try`/`catch`, and failing fast. ◆ a missing book throws `BookNotFound`.
20. Anonymous functions and arrow functions, and `use`. *(the term "closure" introduced here)*

## Part 4: Working with data (6)
21. Dates and times: `DateTimeImmutable` and time zones. ◆ a loan due date of 14 days.
22. Reading and writing files. ◆ the shelf is saved to a file.
23. JSON: `json_encode`/`json_decode`, and `JSON_THROW_ON_ERROR`. ◆ the shelf in JSON.
24. Input on the command line: arguments and reading lines. Never trust input (the first security note). ◆ the `shelf` command: add, list, lend.
25. Regular expressions, the useful minimum: ISBN and email checks. *(KISS: when not to use a regex)*
26. Mini-review: our CLI app, read as a whole. What is repeated, and what is unclear? *(refactoring with DRY and KISS)*

## Part 5: Objects (11)
27. Why objects: from arrays to a `Book` class.
28. Properties, methods, constructors, and constructor property promotion. ◆ `Book`, `Member`, `Loan`.
29. Visibility and encapsulation: why `private` is the default.
30. `readonly` properties and value objects. ◆ `Isbn` as a value object (validated once).
31. Enums. ◆ `Role` sets the loan period. *(`LoanStatus` as an example only, until loans are stored in Part 8)*
32. Static members and constants, and when not to use static.
33. Interfaces. ◆ `BookRepository` interface (file now, database later).
34. Inheritance vs composition. *(prefer composition, KISS)* ◆ a `Library` that has a repository and members.
35. Abstract classes and traits: what they are for, and their limits. *(Shelf needs neither, and the chapter says why)*
36. Namespaces and autoloading with Composer (PSR-4). The first `composer.json`. ◆ the project becomes `src/`.
37. SOLID in plain words, one example per letter, and only where it simplifies.

## Part 6: Professional tools (6)
38. Composer in depth: packages, versions, the lock file, and security advisories (`composer audit`). ◆ `composer.json` declares `ext-mbstring` and a licence. *(Shelf has no packages; the tools come from the course folder)*
39. Code style: PER Coding Style with PHP-CS-Fixer (and PSR-12 as the older form). *(Shelf already follows it: checked since chapter 6)*
40. Static analysis with PHPStan. ◆ the project at the highest level, 10, with its own `phpstan.neon.dist`.
41. First tests with PHPUnit. ◆ tests for `Loan` and the due date (`Loan::start`), `phpunit.xml.dist`.
42. Testing well: the arrange-act-assert pattern, testing exceptions, data providers, fakes, and what not to test. ◆ `Library` tested with an in-memory repository; `Isbn` with data providers.
43. Git basics for one developer: commits, `.gitignore`, and never committing secrets. ◆ *Shelf* becomes a repository, with a `.gitignore`.

## Part 7: The web (9)
44. HTTP in plain words: request, response, status codes, headers; PHP's built-in server.
45. Docker for our setup: `compose.yaml`, PHP plus a web server, the same images that test the examples. *(Docker Desktop's licence, and free alternatives)* ◆ `shelf/public/`, served by `docker compose up web`; `shelf/web.http`.
46. The first web page and escaping output (`htmlspecialchars`), from day one. ◆ the book list as a web page; `e()`.
47. Forms, GET and POST, and validating input on the server; 422 and Post/Redirect/Get. ◆ add a book (`public/add.php`, checked by `Library::add`).
48. A tiny front controller and routing, written by hand to understand it; 404 and 405. *(KISS; replaced by Symfony later)* ◆ `Shelf\Web\Request`, `Response`, `App`; `AppTest` without a server.
49. Templates: keeping HTML apart from logic; template files return typed functions, no `extract`. ◆ `templates/` with a layout; `Templates`.
50. Cookies and sessions, with secure settings; session fixation; flash messages. ◆ `Session`, `PhpSession`, a message after adding a book.
51. CSRF tokens, checked with `hash_equals`. ◆ forms protected: every POST checked in `App`.
52. File uploads, done safely: content type by `finfo`, own names, stored outside `public/`, `nosniff`. ◆ book covers.

## Part 8: Databases (8)
53. What a database is, and SQL basics with SQLite (no server needed): SELECT, WHERE, INSERT, UPDATE, DELETE, NULL, constraints. ◆ `schema.sql`, the books table with its rules.
54. PDO and prepared statements, the only way we write queries; SQL injection. ◆ `PdoBookRepository` (the interface from chapter 33 pays off), `openDatabase()`, `shelf.php import`.
55. CRUD: create, read, update, delete; rows to objects; `rowCount()`; GET reads, POST changes. ◆ one method per operation; a page per book: change copies, remove.
56. Relations and JOINs, foreign keys, LEFT JOIN. ◆ members and loans in the database; a book on loan can't be removed.
57. Transactions, and lost updates (check and change in one statement). ◆ lending is all-or-nothing, and the last copy goes once; `inTransaction()`.
58. Migrations: changing the schema safely; `ALTER TABLE`, indexes, `EXPLAIN QUERY PLAN`. ◆ `migrations/`, `Migrations` with hashes, the loan indexes.
59. MariaDB with Docker, the same PDO code, and database users with least privilege. ◆ none: *Shelf* stays on SQLite, and the chapter says why. *(compose service `db`)*
60. Testing code that uses a database: a new database per test, rollback on a server, contract tests. ◆ contract tests for the repositories; the PDO classes tested on SQLite in memory.

## Part 9: Users and security (6)
61. Passwords: `password_hash` and `password_verify`, `password_needs_rehash`, bcrypt's 72 bytes, and never storing plain text. ◆ member accounts: `Accounts`, migration 004, `shelf.php password`.
62. Login, logout, and regenerating the session; one answer for every failed login. ◆ `/login` and `/logout`, who is logged in, in the bar.
63. Authorization: roles and checks on every request, deny by default, IDOR. ◆ librarian vs member: staff change books, members see their own loans.
64. The security part in one place: the OWASP Top 10:2025 in plain words, mapped to the chapters; security headers; fail closed. ◆ the security headers on every page.
65. Configuration and secrets: environment variables, `.env` and `.env.local`, and what never goes into git. ◆ `Config`, read and checked at the start.
66. Logging (PSR-3) and errors in production: show little, log much; log injection. ◆ `psr/log`, the first package; `FileLogger`; an error page with a reference.

## Part 10: APIs (5)
67. REST in plain words, and JSON responses: resources, methods, `201` with `Location`, reading a JSON body; never JSON by hand. ◆ `Response::json()`, `GET /api/books`.
68. Status codes and error responses: problem details (RFC 9457), `400`/`415`/`422`/`409`, every mistake at once, errors under `/api/` never HTML. ◆ `Response::problem()`, `GET /api/book`, a `500` with a reference.
69. API authentication with tokens: `Authorization: Bearer`, random tokens stored as SHA-256, `401` vs `403`, no cookies so no CSRF. ◆ migration 005, `ApiTokens`, `POST`/`DELETE /api/tokens`, a member's own loans, staff add books.
70. Pagination and filters: `LIMIT`/`OFFSET`, the `Link` header, filters through placeholders, sorts from a list; add, don't break. ◆ `BookQuery`, `?page`, `?per_page`, `?author`, `?available`.
71. Testing an API: the contract (shapes, statuses, headers, refusals), without a server; few real HTTP checks. ◆ `ApiContractTest`; a duplicate book is now `409`.

## Part 11: Symfony (6)
72. Why a framework, and what Symfony gives us. Installing it, and its structure. *(Symfony 7.4 LTS, chosen 2026-11-17)* ◆ *Shelf* becomes a Symfony app, with the old App behind a bridge controller.
73. Routing, controllers and Twig. ◆ *Shelf* moves to Symfony, mapped to what we built by hand.
74. Doctrine ORM vs the PDO we wrote. ◆ entities for book, member, loan.
75. Forms and validation in Symfony. ◆ `BookData` and `ValidIsbn`; the add form and the API name every mistake.
76. Security in Symfony: login and voters for roles. ◆ firewalls for pages and the API, `BookVoter`, throttled logins.
77. Testing a Symfony app. ◆ `PagesTest`, `ApiContractTest` and command tests; a transaction per test. Found: lending a book with an ISBN failed.

## Part 12: Putting it online (2)
78. Production setup: configuration, a production image, the cache, and HTTPS. ◆ a Dockerfile in stages, PHP-FPM and Caddy, `compose.prod.yaml`; logs to stderr. Found: the security headers were missing since chapter 73.
79. CI with every check (tests, PHPStan, style), and a simple deployment. ◆ *Shelf* online: a test stage, GitHub Actions, images per commit on ghcr.io, `deploy.sh` behind a restricted SSH key, a smoke test. Found: the test bootstrap ended with status 0.

## Part 13: Shelf, complete (9)
*Shelf* becomes the library app a real library could run: every chapter adds what its readers and its librarian expect, with what the course taught.
80. A design that looks finished: a layout, Twig components, covers on the list, mobile first, accessible, and still no JavaScript under the strict CSP. ◆ *Shelf*'s new look.
81. Finding books: search, filters, sorting and pages on the pages, with availability. ◆ the catalogue.
82. Accounts: sign-up with email confirmation, password reset, a profile, deleting your account; Symfony Mailer. ◆ readers register themselves.
83. The lending desk: lend, return and extend in the browser; members, roles and blocking. ◆ the librarian works without the console.
84. Reservations: a queue per book, "ready for pickup", expiry; readers extend their own loans, under rules. ◆ holds.
85. Reminders: due-soon, overdue and pickup emails, sent by Messenger and Scheduler in a worker. ◆ the worker.
86. The librarian's dashboard: overdue loans, holds to prepare, statistics with queries, the loan history. ◆ the dashboard.
87. Three languages: English, German and Arabic, right to left; Symfony's translations. ◆ *Shelf* in three languages.
88. Launch: a demo mode that resets every night, backups, a health check, error pages, and a launch checklist. ◆ *Shelf* online, for everyone to try.

## Part 14: What next (1)
89. Where to go from here: reading the PHP manual, contributing to open source, and the next topics (queues, events, performance); a short look at Fibers. ◆ No new code: where each next topic would fit *Shelf*, and its README with every command.

---

**Not included, on purpose:**
- a second framework;
- Nginx and Apache in depth, beyond the one Docker setup;
- Kubernetes;
- Fibers, which are rarely needed in application code. A short mention goes in 89.
- design patterns as a catalogue. The few we use (repository, value object, front controller) are taught where they appear.
