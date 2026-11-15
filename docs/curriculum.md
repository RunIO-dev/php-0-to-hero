# Curriculum

**Decided.**
- **Languages:** English first, then German and Arabic.
- **Repo:** public, RunIO-dev/php-0-to-hero.
- **Licences:** text CC BY 4.0, code MIT.
- **Versions:** PHP 8.6, with examples also tested on 8.5. PER Coding Style. PHPUnit 13.
- **Framework:** Symfony, the one framework of the course.
- **Principles:** secure by default, KISS, DRY (rule of three) and YAGNI, enforced by CI.
- **Setup:** `tools/check.php` from chapter 1. Docker from Part 7 (and available from day one).

**Shape.**
- **One chapter = one book section = one video** of about 6–10 minutes.
- **80 chapters in 13 parts.**
- Each chapter follows the template in [standards.md](standards.md): goals, explanation, tested examples, common mistakes, exercises at three levels with solutions kept apart, and a summary.
- At 3 videos a week, one language takes about 6 months.

**The project that grows through the course: "Shelf", a small lending library.** It has books, members and loans. Simple enough for a beginner, and it naturally needs everything the course teaches:
- lists and search (arrays);
- dates (due dates);
- files, then a database with relations (a member has loans);
- a login, roles (librarian vs member), forms, an API and tests.

It starts as a command-line script and ends as a Symfony app in production. Each part adds one step, marked ◆ below.

Dependencies run in order: each part needs the parts before it. Extra notes are in brackets.

---

## Part 1: Getting started (5)
1. **What programming is, and what PHP is for.** Websites, the command line, and where PHP runs. Client and server, in plain words.
2. **Installing PHP 8.6** on Windows, macOS and Linux, from official sources only. Then `php -v`.
3. **`tools/check.php`, the setup check.** What it checks and how to read its fix messages. (Every later chapter refers to it.)
4. **Your editor and the terminal:** the minimum you need, with no tool promotion.
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
31. Enums. ◆ `LoanStatus` and `Role`.
32. Static members and constants, and when not to use static.
33. Interfaces. ◆ `BookRepository` interface (file now, database later).
34. Inheritance vs composition. *(prefer composition, KISS)*
35. Abstract classes and traits: what they are for, and their limits.
36. Namespaces and autoloading with Composer (PSR-4). The first `composer.json`. ◆ the project becomes `src/`.
37. SOLID in plain words, one example per letter, and only where it simplifies.

## Part 6: Professional tools (6)
38. Composer in depth: packages, versions, the lock file, and security advisories (`composer audit`).
39. Code style: PER Coding Style with PHP-CS-Fixer (and PSR-12 as the older form).
40. Static analysis with PHPStan. ◆ the project at a strict level.
41. First tests with PHPUnit. ◆ tests for `Loan` and the due date.
42. Testing well: the arrange-act-assert pattern, testing exceptions, data providers, and what not to test.
43. Git basics for one developer: commits, `.gitignore`, and never committing secrets.

## Part 7: The web (9)
44. HTTP in plain words: request, response, status codes, headers.
45. Docker for our setup: `compose.yaml`, PHP plus a web server, the same images that test the examples. *(Docker Desktop's licence and free alternatives, stated neutrally)*
46. The first web page and escaping output (`htmlspecialchars`), from day one. ◆ the book list as a web page.
47. Forms, GET and POST, and validating input on the server. ◆ add a book.
48. A tiny front controller and routing, written by hand to understand it. *(KISS; replaced by Symfony later)*
49. Templates: keeping HTML apart from logic.
50. Cookies and sessions, with secure settings.
51. CSRF tokens. ◆ forms protected.
52. File uploads, done safely. ◆ book covers.

## Part 8: Databases (8)
53. What a database is, and SQL basics with SQLite (no server needed).
54. PDO and prepared statements, the only way we write queries. ◆ `PdoBookRepository` (the interface from chapter 33 pays off).
55. CRUD: create, read, update, delete. ◆ books in the database.
56. Relations and JOINs. ◆ members and loans.
57. Transactions. ◆ lending a book is all-or-nothing.
58. Migrations: changing the schema safely.
59. MySQL/MariaDB with Docker, the same PDO code, and a database user with least privilege.
60. Testing code that uses a database.

## Part 9: Users and security (6)
61. Passwords: `password_hash` and `password_verify`, and never storing plain text. ◆ member accounts.
62. Login, logout, and regenerating the session.
63. Authorization: roles and checks. ◆ librarian vs member.
64. The security part in one place: why everything we did so far matters (injection, XSS, CSRF, sessions, uploads), with the OWASP Top 10 in plain words.
65. Configuration and secrets: environment variables, `.env`, and what never goes into git.
66. Logging (PSR-3) and errors in production: show little, log much.

## Part 10: APIs (5)
67. REST in plain words, and JSON responses. ◆ `GET /api/books`.
68. Status codes and error responses (problem details).
69. API authentication with tokens. ◆ a member's own loans.
70. Pagination and filters.
71. Testing an API.

## Part 11: Symfony (6)
72. Why a framework, and what Symfony gives us. Installing it, and its structure. *(the stable version of the day)*
73. Routing, controllers and Twig. ◆ *Shelf* moves to Symfony, mapped to what we built by hand.
74. Doctrine ORM vs the PDO we wrote. ◆ entities for book, member, loan.
75. Forms and validation in Symfony.
76. Security in Symfony: login and voters for roles.
77. Testing a Symfony app.

## Part 12: Putting it online (2)
78. Production setup: configuration, a production image, the cache, and HTTPS.
79. CI with every check (tests, PHPStan, style), and a simple deployment. ◆ *Shelf* online.

## Part 13: What next (1)
80. Where to go from here: reading the PHP manual, contributing to open source, and the next topics (queues, events, performance), neutrally, without promoting any product.

---

**Count:** 80 chapters, with Part 11 slightly over the plan. If that's too many, the first candidates to merge are 9 into 7, 32 into 29, 66 into 65, and 70 into 67.

**Not included, on purpose:**
- a second framework;
- Nginx and Apache in depth, beyond the one Docker setup;
- Kubernetes;
- Fibers, which are rarely needed in application code. A short mention goes in 80.
- design patterns as a catalogue. The few we use (repository, value object, front controller) are taught where they appear.

**Every video:** the code shown is the tested file from the repo, and the chapter's README links the video and the book section.
