# Writing and code standards

These standards apply to the book, the code and the exercises. Where a rule can be checked by a machine, `tools/verify` checks it and CI runs it on every push (§6). See also [curriculum.md](curriculum.md).

## 1. Repo layout: `RunIO-dev/php-0-to-hero`

```
README.md  LICENSE (CC BY 4.0 for text)  LICENSE-CODE (MIT)  ATTRIBUTION.md  CONTRIBUTING.md
Dockerfile, compose.yaml  php:8.6 and php:8.5 images (8.6-rc-cli until the 8.6 release), plus web (Part 7) and db, MariaDB (chapter 59)
docker/db/users.sh      the database users for chapter 59
composer.json           "php": ">=8.5"; dev: phpunit 13, phpstan 2, php-cs-fixer 3
tools/check.php         setup check: PHP version, extensions, Composer; prints the exact fix
tools/verify            the one script CI and we run: every check in §6
tools/book, tools/pdf   the HTML book into site/, then the PDF with Chrome
book/en/NN-slug.md      one chapter = one file (later book/de/, book/ar/)
code/NN-slug/           the chapter's examples: example.php + example.out (expected output)
exercises/NN-slug/      starter files + tests (tests fail on the starter)
solutions/NN-slug/      solutions (tests pass)
shelf/                  the project, newest state. A chapter that changes it keeps a copy in code/NN-slug/shelf/
glossary/ar.json        Arabic terms (source of truth; the book's glossary page is built from it)
site/                   generated: the HTML book and the PDF (not in git)
```

- Chapter numbers are two-digit (`06-variables`), as in curriculum.md.
- The book builds itself in PHP, the language it teaches:
  - Markdown → HTML with `league/commonmark` (BSD-3);
  - code highlighting with `tempest/highlight` (MIT; active, 2.30.0 from Oct 2026). `scrivo/highlight.php` was last released in 2022;
  - HTML → PDF with headless Chrome.
  - No site framework.

## 2. Chapter template

Every `book/en/NN-slug.md` has these headings, in this order. A heading may say "none" (for example, Security), but it may not be missing:

1. **Goals:** 2–4 "you will be able to …" lines.
2. **Before you start:** the chapters it builds on.
3. **The idea:** the concept in plain words. One analogy at most, and only if it really helps.
4. **In code:** examples, one new concept each, at most about 30 lines. After each one, a walk through the lines that matter (not every line).
5. **Common mistakes:** the real error message, what it means, and the fix.
6. **Principle check:** which of secure, KISS, DRY or YAGNI applies here, and how (1–3 lines).
7. **Security:** what could go wrong with this feature, or "nothing new here".
8. **Shelf:** what the project gains in this chapter, with the commands to follow along. The text includes the chapter's snapshot (`code/NN-slug/shelf/…`), never `shelf/` itself, so an early chapter never shows code from a later one.
9. **Exercises:** beginner, intermediate and advanced, each a folder in `exercises/` with tests.
10. **Summary:** five bullets at most.
11. **Further reading:** the PHP manual and other official docs only.

- **Length:** about 1,500–3,000 words.
- **Code in the text:** always included from a file in `code/`, never pasted: `<!-- include: code/06-variables/names.php -->`. The build inserts the file and its `.out`, so the text and the tested code can't drift.

## 3. Writing style

- Plain English, short sentences, "you". No hype words (amazing, powerful, simply, just, obviously), and no promotion.
- **Define every term at its first use**, and only then use it. A term from a later chapter gets a one-line explanation, or it waits.
- **One idea per paragraph.** Explain why before how, but keep the why short.
- **Neutral:**
  - no product as a bad example;
  - no option framed as "the one to buy";
  - tools named only for what they are, with free options first.
- **Example data:** names from many cultures (Amira, Jonas, Ana, Kenji). Real-looking books. Money in cents. Dates in ISO format.
- **Mistakes are normal:** error messages are shown and read calmly, never hidden.

## 4. Code standards

- **PHP 8.6 target, and must also run on 8.5.** A feature only in 8.6 is marked in the text (`// PHP 8.6+`), and CI skips that file on 8.5 by that marker.
- **`declare(strict_types=1);`** in every file from chapter 16 on. Before that, it's left out on purpose and explained in chapter 16 `tools/verify` checks it, also in `shelf/`.
- **PER Coding Style**, the latest release, through PHP-CS-Fixer. PHPStan levels: 5 before chapter 16 (no types yet), 6 from chapter 16, and 8 for `solutions/` from chapter 40, for `shelf/` and for our own `tools/`. From chapter 40, *Shelf* has its own `phpstan.neon.dist` at level 10, and the checks use it. Short examples stay at 6.
- **Names:**
  - English identifiers that say what a thing is (`$dueDate`, not `$d`);
  - classes are nouns, methods are verbs;
  - booleans read as questions (`isOverdue`).
- **Small:** functions under about 20 lines, one job each.
- **Errors:**
  - exceptions, not `false` or `-1` return codes;
  - fail early, at the boundary (input, files, database);
  - `JSON_THROW_ON_ERROR`, and PDO in exception mode.
- **Secure by default,** from the first time a feature appears:
  - escape output (`htmlspecialchars` with `ENT_QUOTES`);
  - prepared statements only;
  - `password_hash` and `password_verify`;
  - input validated on the server;
  - secrets in environment variables.
- **KISS, DRY and YAGNI:**
  - the simplest code that passes the tests;
  - extract a function or class the third time something repeats, not before;
  - nothing for "later".
- **Comments** say why, not what. They are in English and kept short, and the code is the same in every language edition.
- **"Don't do this" examples** go in files named `*.bad.php`, always shown next to the right version. They are run only for `php -l`, and the text marks them clearly (✗).
- **Examples that show an error on purpose** (for "Common mistakes") go in files named `*.error.php`. They run like any example, and their `.out` holds the real error message. PHPStan and the style check skip them, and the text marks them (⚠). An exercise starter with a mistake on purpose uses the same name.
- **`.out` files** are written with `composer run-script out code/NN-slug/*.php`, which records exactly what the check expects. Read each one before you commit it.
- **Arguments and input:** an example that needs them has a `.run` file next to it with the command as a learner types it: `php greet.php Amira "Things Fall Apart" < names.txt`. Only `php <file>`, arguments, and `< file` from the same folder are allowed; no shell runs it. The book shows the command above the output.
- **Sessions** (chapter 54 on): a `.run` file with several lines, each `php <file> [arguments]`, optionally ending in `< file` with a file of the folder (chapter 61), is a session. Its commands change files (a database), so they run in order in a copy of the folder (of *Shelf* as it was after the snapshot's chapter), and the `.out` holds each command with `$ ` before it, followed by what it printed.
- **Databases:** examples use SQLite in memory (`sqlite::memory:`) or in a temporary file they delete, never a file in the repo. Examples and exercises for MariaDB (chapter 59 on) connect to the `db` service with `host=db`, and need it running: `docker compose up -d --wait db`. Without it, the checks name each such file and skip it (`tools/src/Database.php`); CI starts it before `verify`.
- **Files of functions** that other examples load with `require_once __DIR__ . '/name.php'` need no `.out`: they are checked through the files that load them.
- **Tests as examples** (chapter 41 on): a `code/**/*Test.php` file runs with PHPUnit from the course folder (`vendor/bin/phpunit --testdox <file>`), and its `.out` holds what PHPUnit prints, without the version, runtime, time and memory lines. The file it tests is loaded with `require_once __DIR__ . '/name.php'`, so it needs no `.out`.
- **PHPStan output as examples** (chapter 40): an example `x.php` may have an `x.phpstan` file. Its first line is the command (`$ vendor/bin/phpstan analyse --level 8 code/40-static-analysis/x.php`), the rest is what PHPStan prints. `tools/verify` runs the command and compares, and the book shows it below the output; `php tools/out` writes it again. Such an example has problems on purpose, so the normal PHPStan runs leave it out (`tools/src/Analysis.php`).
- **Composer projects** (*Shelf* from chapter 36): a folder below `code/` or `shelf/` with its own `composer.json`. A project with a `composer.lock` (*Shelf* from chapter 66, and `code/66-logging/`) has packages: the checks run `composer install` there, which needs the network or Composer's cache. Only the scripts next to `composer.json` have a `.out`; the classes below it, in `src/` and `tests/`, are loaded by the autoloader and checked through the scripts. The checks run `composer dump-autoload --dev` there first; `vendor/` is generated and ignored by git (`tools/src/Projects.php`). A *Shelf* snapshot holds only what its chapter changes, so PHPStan and the tests run on *Shelf* as it is after that chapter, put together from every snapshot up to it (`Shelf::state`).

- **Web examples** (Part 7 on): a page `x.php` with an `x.http` next to it, or a standalone `site.http` for a folder (*Shelf*'s `web.http`, or a small site with `# docroot public`). The checks serve a copy of the folder with PHP's built-in server, as a learner runs it, send the requests, and compare the transcript with the `.out` (`tools/src/Web.php`). The `.http` format:
  - requests are separated by `###`; a request is `METHOD /path`, header lines, a blank line and the body;
  - `# docroot public` and `# router front.php` start the server as `php -S localhost:8000 -t public front.php` would; `# env NAME=value` sets an environment variable;
  - `# run php shelf.php import` (chapter 63) runs a command in the copy before the server starts, with the same rules as a session line; the `.out` starts with each command and what it printed, and a command that fails stops the check;
  - `# capture name /regex/` remembers group 1 of a response (headers and body), and `{{name}}` in a later request sends it; the `.out` shows `{{name}}`, so a random token gives the same `.out` every time;
  - in a `multipart/form-data` body, a line `< samples/cover.png` sends that file's bytes (an upload);
  - cookies are kept between the requests of one file; long cookie values and dates are shown as `…`, and a binary answer as its size.
  A page that only an `.http` file requests (in its folder, or one folder up) is checked through the requests, not run on its own. Exercise tests send requests with `responses_of($dir, $http)` (`tools/bootstrap.php`).
- **Symfony** (Part 11, Symfony 7.4 LTS): *Shelf* is a Symfony app from chapter 72.
  - Generated files never count: `var/` (cache, logs, the SQLite databases), `vendor/`, and `config/reference.php` (made in debug mode) are skipped by the checks, PHPStan and PHP-CS-Fixer.
  - Flex writes `config/bundles.php` again on every `composer require`, without our comment and `declare(strict_types=1);`: put them back each time. A bundle whose recipe isn't one of Symfony's own (chapter 77's `dama/doctrine-test-bundle`) is added by hand.
  - Web transcripts: Symfony's CSRF tokens change on every page, so the `value` of a `_token` field shows as `…`; the test server's address in whole URLs (redirects) shows as `http://localhost:8000`. A `# capture` reads the token again after each login and logout, which change it.
  - *Shelf*'s tests run with `APP_ENV=test` (`.env.test`), against their own SQLite database in `var/`, migrated once by `tests/bootstrap.php`; each test runs in a transaction that is rolled back.
- **Removed files:** a *Shelf* file that a chapter deletes gets an empty `X.removed` in that chapter's snapshot; from then on, `Shelf::state` leaves it out, and the check refuses it in `shelf/`.
- **Course files in the text:** `compose.yaml`, `Dockerfile` and `docker/db/users.sh` may be included like code (`Chapters::COURSE_FILES`); they always show their current state.

## 5. Exercises

- Three per chapter: beginner, intermediate and advanced. Each is a real-world task, never "foo/bar".
- `exercises/NN-slug/<level>/` holds a starter file and a PHPUnit test.
- **The test must fail on the starter, and pass on `solutions/NN-slug/<level>/`.** CI checks both, so every exercise is real and every solution works.
- The learner runs one command: `composer exercise 06 beginner`.
- **Exercises where the learner writes the tests** (chapter 41 on): the starter test file is the learner's, and a `bugs/` folder holds versions of the code with one mistake each, described in a `// Bug:` line or their first PHPDoc. A checking test calls `missed_bugs(__DIR__, 'XTest.php', 'x.php')`: the learner's tests must pass on the right code and fail on every bug. PHPStan exercises use `analysis_of($file, $level)`.
- A test finds the file it checks with `subject(__DIR__, 'file.php')`. For scripts, `output_of($file)` runs it and returns what it prints, and `variables_in()` and `strings_in()` let a test check how it was written. `tools/verify` runs the same test once against the starter and once against the solution.

## 6. Checks: `tools/verify`, which CI runs on every push

1. Every `code/**.php` runs on 8.5 and 8.6 (Docker), and its output equals its `.out`.
2. Every exercise test fails on the starter and passes on the solution.
3. Shelf: every file in `shelf/` equals its newest snapshot in `code/NN-slug/shelf/`, every file of the newest state is in `shelf/`, and no chapter includes `shelf/` directly. Each script with a `.out` file is checked like an example. From chapter 41, its tests pass, in `shelf/` and in the state after every chapter that has them.
4. PHP-CS-Fixer (PER) reports nothing, and PHPStan passes at the levels in §4. Examples often define the same function (`lateFee`) in several files, so PHPStan runs on batches of files in which no function, class or global constant name repeats (`tools/src/Batches.php`). A folder whose files `require` each other, and a Composer project, stay in one batch.
5. **Forbidden patterns,** outside `*.bad.php`:
   - SQL built by joining strings with variables;
   - `md5(` or `sha1(` near "password";
   - `echo $_GET` or `echo $_POST` without escaping, unless the page answers in plain text or JSON (`header('Content-Type: text/plain ...')`);
   - `mysql_`;
   - `extract(`;
   - `eval(`;
   - `@` to silence errors;
   - `global $`.
6. **Chapters:**
   - every heading from §2 is present;
   - every `include:` path exists;
   - every link answers (checked weekly, not on every push); addresses that stand for one in the text, such as `localhost` and reserved example domains (`shelf.example`), are skipped.
7. **Arabic edition:** no chapter uses a form that `glossary/ar.json` lists as `rejected`. Only unambiguous forms are listed there; a word that is also ordinary Arabic (أسلوب is also style) is recorded in the term's reason instead, so the check never blocks a correct sentence.

The forbidden-pattern check reads PHP tokens, so it sees `echo $_GET[...]` but not request data passed through a variable first. Review still matters.


## 7. Arabic edition

- **Teaching words** (exercise, skill, objectives, evaluation) come from ALECSO's unified dictionary No. 44, Curricula and Teaching Methods (2020).
- **IT terms** follow the rule recorded in `glossary/ar.json` (`about`): every term lists its sources, the chosen form and why.
- The first time a chapter uses a term, the English word stands next to it: مصفوفة (array).
- Code, identifiers and output stay in English. Arabic text inside code examples is used only where the chapter is about text (chapter 8, `mb_*`).

## 8. Licensing and credit

- Our text is CC BY 4.0, and our code is MIT.
- Adapted material is credited in ATTRIBUTION.md, with source, licence and what was changed. Only the PHP manual (CC BY 3.0) and PHP-FIG (CC BY / MIT) may be adapted.
- PHP: The Right Way, the Symfony docs and Hsoub are linked only: their licences (non-commercial or share-alike) don't fit ours.
- No third-party images, fonts or sounds without a recorded licence.
