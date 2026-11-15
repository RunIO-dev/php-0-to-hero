# Writing and code standards

These standards apply to the book, the code, the exercises and the videos. Where a rule can be checked by a machine, `tools/verify` checks it and CI runs it on every push (§7). See also [curriculum.md](curriculum.md).

## 1. Repo layout: `RunIO-dev/php-0-to-hero`

```
README.md  LICENSE (CC BY 4.0 for text)  LICENSE-CODE (MIT)  ATTRIBUTION.md  CONTRIBUTING.md
Dockerfile, compose.yaml  php:8.6 and php:8.5 images (8.6-rc-cli until the 8.6 release), plus web and db from Part 7
composer.json           "php": ">=8.5"; dev: phpunit 13, phpstan 2, php-cs-fixer 3
tools/check.php         setup check: PHP version, extensions, Composer; prints the exact fix
tools/verify            the one script CI and we run: every check in §7
tools/book, tools/pdf   the HTML book into site/, then the PDF with Chrome
book/en/NN-slug.md      one chapter = one file (later book/de/, book/ar/)
code/NN-slug/           the chapter's examples: example.php + example.out (expected output)
exercises/NN-slug/      starter files + tests (tests fail on the starter)
solutions/NN-slug/      solutions (tests pass)
shelf/                  the project. Git tag shelf-chNN at every step it changes
glossary/ar.json        Arabic terms (source of truth; the book's glossary page is built from it)
site/                   generated: the HTML book (GitHub Pages) and the PDF
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
2. **Before you start:** the chapters it builds on, and `shelf-chNN` to start from, if the project changes.
3. **The idea:** the concept in plain words. One analogy at most, and only if it really helps.
4. **In code:** examples, one new concept each, at most about 30 lines. After each one, a walk through the lines that matter (not every line).
5. **Common mistakes:** the real error message, what it means, and the fix.
6. **Principle check:** which of secure, KISS, DRY or YAGNI applies here, and how (1–3 lines).
7. **Security:** what could go wrong with this feature, or "nothing new here".
8. **Shelf:** what the project gains in this chapter, with the commands to follow along.
9. **Exercises:** beginner, intermediate and advanced, each a folder in `exercises/` with tests.
10. **Summary:** five bullets at most.
11. **Further reading:** the PHP manual and other official docs only.

- **Length:** about 1,500–3,000 words. One chapter is one video of 6–10 minutes.
- **Code in the text:** always included from a file, never pasted: `<!-- include: code/06-variables/naming.php -->`. The build inserts the file and its `.out`, so the text and the tested code can't drift.

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
- **`declare(strict_types=1);`** in every file from chapter 16 on. Before that, it's left out on purpose and explained in chapter 16.
- **PER Coding Style**, the latest release, through PHP-CS-Fixer. PHPStan levels: 5 before chapter 16 (no types yet), 6 from chapter 16, and 8 for `solutions/` from chapter 40, for `shelf/` and for our own `tools/`. Short examples stay at 6.
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

## 5. Exercises

- Three per chapter: beginner, intermediate and advanced. Each is a real-world task, never "foo/bar".
- `exercises/NN-slug/<level>/` holds a starter file and a PHPUnit test.
- **The test must fail on the starter, and pass on `solutions/NN-slug/<level>/`.** CI checks both, so every exercise is real and every solution works.
- The learner runs one command: `composer exercise 06 beginner`.
- A test loads the file it checks with `require_once subject(__DIR__, 'file.php');`. `tools/verify` runs the same test once against the starter and once against the solution.

## 6. Videos

- **Our own animated editor, not a screen recording.** Code types itself line by line, the line being explained lights up, and the output appears from the `.out` file. It is built with our video engine, and the code on screen is read from the repo file.
- **Narration:** written with the chapter, as a shorter spoken version. It points to the same example files. The book carries the full detail, and the video the core.
- **Pauses:** a "pause and try" card before each exercise, with the command to run.
- **Description:** links to the chapter's web page, its code folder and `shelf-chNN`. The narration never says "link in the description".
- **Languages:** English first, then German, then Arabic. The code is identical in every edition, and only the text and the narration change.

## 7. Checks: `tools/verify`, which CI runs on every push

1. Every `code/**.php` runs on 8.5 and 8.6 (Docker), and its output equals its `.out`.
2. Every exercise test fails on the starter and passes on the solution.
3. `shelf/` tests pass at every `shelf-chNN` tag. (Added with the first tag, in chapter 6.)
4. PHP-CS-Fixer (PER) reports nothing, and PHPStan passes at the levels in §4.
5. **Forbidden patterns,** outside `*.bad.php`:
   - SQL built by joining strings with variables;
   - `md5(` or `sha1(` near "password";
   - `echo $_GET` or `echo $_POST` without escaping;
   - `mysql_`;
   - `extract(`;
   - `eval(`;
   - `@` to silence errors;
   - `global $`.
6. **Chapters:**
   - every heading from §2 is present;
   - every `include:` path exists;
   - every link answers (checked weekly, not on every push).
7. **Arabic edition:** no chapter uses a form that `glossary/ar.json` lists as `rejected`. Only unambiguous forms are listed there; a word that is also ordinary Arabic (أسلوب is also style) is recorded in the term's reason instead, so the check never blocks a correct sentence.

The forbidden-pattern check reads PHP tokens, so it sees `echo $_GET[...]` but not request data passed through a variable first. Review still matters.

**Our side, KISS:** one `tools/verify` script, one Docker setup, and one source per chapter. Nothing runs that isn't needed.

## 8. Arabic edition

- **Teaching words** (exercise, skill, objectives, evaluation) come from ALECSO's unified dictionary No. 44, Curricula and Teaching Methods (2020).
- **IT terms** follow the rule recorded in `glossary/ar.json` (`about`): every term lists its sources, the chosen form and why.
- The first time a chapter uses a term, the English word stands next to it: مصفوفة (array).
- Code, identifiers and output stay in English. Arabic text inside code examples is used only where the chapter is about text (chapter 8, `mb_*`).

## 9. Licensing and credit

- Our text is CC BY 4.0, and our code is MIT.
- Adapted material is credited in ATTRIBUTION.md, with source, licence and what was changed. Only the PHP manual (CC BY 3.0) and PHP-FIG (CC BY / MIT) may be adapted.
- PHP: The Right Way, the Symfony docs and Hsoub are linked only: their licences (non-commercial or share-alike) don't fit ours.
- No third-party images, fonts or sounds without a recorded licence.
