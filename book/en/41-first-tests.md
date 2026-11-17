# 41. First tests with PHPUnit

## Goals

- You will be able to write a test class with PHPUnit, with test methods and assertions.
- You will be able to run tests, and read what PHPUnit says when one fails.
- You will be able to choose `assertSame` over `assertEquals`, and explain why.
- You will be able to give a project its own tests, with a `phpunit.xml.dist` and a `tests/` folder.

## Before you start

- Chapter 40: PHPStan, which checks types; tests check what the code does.
- Chapter 33: interfaces, and a clock that can be fixed in a test.
- Chapter 21: `DateTimeImmutable`.

## The idea

Since chapter 5, you have checked your code by running it and reading what it prints. That works once. After the next change, you would have to run every command again and remember what each one should print. Nobody does that for long.

An **automated test** is code that runs your code with a known input and compares the result with the expected one. It says only "pass" or "fail", and it can run a thousand times a day. You write it once; after that, every change is checked against everything you ever decided the code must do.

**PHPUnit** is the testing tool for PHP. You have used it in every chapter: `composer exercise 06 beginner` runs a PHPUnit test against your exercise. Now you write the tests yourself.

Three words:

- A **test case** is a class of tests for one thing, such as `LateFeeTest` for `lateFee`.
- A **test** is one method in it, one fact the code must keep: "nothing when early".
- An **assertion** is the check inside: "this result is the same as that expected value".

PHPStan and tests answer different questions. PHPStan asks: can this value have the wrong type? A test asks: does the code give the right answer? `lateFee` returning `-40` has the right type and the wrong answer, and only a test finds that.

## In code

### A test case

<!-- include: code/41-first-tests/late-fee.php -->

<!-- include: code/41-first-tests/LateFeeTest.php -->

- `final class LateFeeTest extends TestCase`: a test case extends PHPUnit's `TestCase`, which brings the assertions. Its name ends in `Test`, and so does its file.
- Each public method whose name starts with `test` is one test. PHPUnit creates a new object of the class for each test, so one test can't change what another sees.
- `self::assertSame(60, lateFee(3))`: the expected value first, then the real one. It passes only if both are the same value of the same type, like `===`.
- The names say what the code must do. With `--testdox`, PHPUnit turns `testNothingWhenEarly` into "Nothing when early", which reads like a list of rules.

The output starts with a line of dots, one per test; it is how PHPUnit shows progress while the tests run.

### A failing test

Here is the same test against a version of `lateFee` without `max(0, ...)`:

<!-- include: code/41-first-tests/late-fee-bug.php -->

<!-- include: code/41-first-tests/LateFeeBugTest.php -->

The dot for the third test became an `F`, for "failure". The report says which test failed (✘), what it expected and what it got (`-40` is not `0`), and the file and line of the assertion. The command ends with an exit code that is not 0, so a script or CI stops (chapter 24).

Read a failure in that order: which rule, which values, which line. Then decide which is wrong, the code or the test. Usually it is the code. Sometimes the rule changed and the test is out of date.

### Running tests

From the course folder, you can run one file, one folder, or one test:

```
vendor/bin/phpunit code/41-first-tests/LateFeeTest.php
vendor/bin/phpunit --testdox code/41-first-tests
vendor/bin/phpunit --filter testNothingWhenEarly code/41-first-tests/LateFeeTest.php
```

`--filter` runs only the tests whose names match: useful while you work on one rule. Before you finish, run them all.

### A project's tests

A project keeps its tests in a folder `tests/`, one test case per class, and its settings in `phpunit.xml.dist` next to `composer.json`. *Shelf*'s file, below, is typical:

- `bootstrap="vendor/autoload.php"`: before the tests, load Composer's autoloader, so the tests can use the classes in `src/`.
- `cacheDirectory=".phpunit.cache"`: where PHPUnit keeps what it remembers between runs, such as which tests failed last. It is not part of the project, so git ignores it (chapter 43).
- `<testsuite>` with `<directory>tests</directory>`: run every `*Test.php` in `tests/`.
- `failOnWarning`, `failOnRisky` and `failOnDeprecation`: treat anything suspicious as a failure, such as a test that asserts nothing.
- `<source>`: the code under test, which PHPUnit's reports use.

The test classes are autoloaded too, from their own namespace: `autoload-dev` in `composer.json` maps `Shelf\Tests\` to `tests/`. "dev", because a production server doesn't need the tests (chapter 38).

## Common mistakes

### `assertEquals` instead of `assertSame`

<!-- include: code/41-first-tests/LooseTest.php -->

`assertEquals` compares like `==` (chapter 10): the text `"60"` and the number `60` count as equal, so the first test passes although `lateFee` returns an `int`, not the text. `assertSame` compares like `===` and catches it.

**Fix:** use `assertSame` by default. `assertEquals` has its uses, such as two objects with the same values, and Chapter 42 shows them.

### A test method without `test`

<!-- include: code/41-first-tests/ForgottenPrefixTest.php -->

"No tests found in class": PHPUnit runs only methods whose names start with `test`. Without the warning, you could believe that the rule is tested.

**Fix:** start every test method with `test`. (PHPUnit also accepts the attribute `#[Test]` on a method; this course uses the prefix.)

### A test that has never failed

A test written after the code, which passes at once, may pass for the wrong reason: the wrong function, an assertion that compares a value with itself, a `require` of an old file.

**Fix:** see each new test fail once. Break the code on purpose, for one run: return `0`, or change the `20` to `21`. The test must turn red. That is what the exercises in this chapter check, with a folder of broken versions.

### A test that depends on today

`new DateTimeImmutable('today')` inside the code under test makes the result change every day. The test that checks "due in 14 days" can only check it against another calculation of today, and a test run just before midnight fails sometimes.

**Fix:** give the code the date, as a parameter or a clock (chapter 33), and give the test a fixed date. That is why *Shelf*'s `run` and `lend` take `$today`.

## Principle check

**KISS:** a test is the simplest code there is: known input, expected output, one assertion. **DRY:** a rule written once as a test is checked on every change, instead of by hand, again and again. **YAGNI:** test what the code must do now, not what it might do one day.

## Security

A security rule without a test can disappear in a refactoring, and nobody notices: the duplicate check in `add`, the year range in `Book`, the validation of a member's role. Write a test for each rule that keeps bad data out; Chapter 42 tests the exceptions. And keep real data out of tests: no real names with real addresses, no passwords, no keys. Test data is code, and code is shared.

## Shelf

*Shelf* gets its first tests, for `Loan` and the due date. Two things first:

- **The due-date rule moves into `Loan`.** `Library::lend` calculated it (today plus the role's loan days) in the middle of lending. It is now a named constructor, `Loan::start`, which can be tested on its own, without a repository or a file. `lend` calls it.
- **The date comes from outside.** `start` takes `$today`, so the tests use fixed dates. The test of 24 December checks that the due date runs into the next month and year.

Change `shelf/src/Loan.php` to:

<!-- include: code/41-first-tests/shelf/src/Loan.php -->

In `shelf/src/Library.php`, `lend` now returns `Loan::start($book, $member, $today)`:

<!-- include: code/41-first-tests/shelf/src/Library.php -->

Create `shelf/tests/LoanTest.php`:

<!-- include: code/41-first-tests/shelf/tests/LoanTest.php -->

`book()` is a private helper that creates the same book for each test: the tests are about the loan, not the book.

Create `shelf/phpunit.xml.dist`:

<!-- include: code/41-first-tests/shelf/phpunit.xml.dist -->

Add `autoload-dev` to `shelf/composer.json`:

<!-- include: code/41-first-tests/shelf/composer.json -->

And add `tests` to the paths in `shelf/phpstan.neon.dist`, so PHPStan checks the tests at level 10 too:

<!-- include: code/41-first-tests/shelf/phpstan.neon.dist -->

Write the autoloader again, because `composer.json` changed, then run the tests:

```
composer dump-autoload --dev --working-dir=shelf
vendor/bin/phpunit -c shelf --testdox
```

`--dev` adds the `autoload-dev` rules. Composer usually includes them on its own, after `composer install` has installed a project's development packages; *Shelf* has none, so you say it. From now on, use this command whenever the README or a chapter asks you to write *Shelf*'s autoloader.

`-c shelf` tells PHPUnit to read `shelf/phpunit.xml.dist`. Four ticks, and `OK (4 tests, 4 assertions)`. `composer verify` now runs them too, and the commands of `shelf.php` print the same as before.

## Exercises

The exercises are in `exercises/41-first-tests/`. Check your work with:

```
composer exercise 41 beginner
```

1. **Beginner, `FineTest.php`:** write the tests for a fine. Your tests must pass on the right code and fail on each broken version in `bugs/`.
2. **Intermediate, `due-date.php`:** the tests exist; write the code that makes them pass.
3. **Advanced, `CardTest.php`:** write the tests for a library card, including its last valid day, so that they catch every bug in `bugs/`.

## Summary

- A test case extends `TestCase`; each `test...` method is one rule, checked by assertions.
- `assertSame(expected, actual)` compares value and type, like `===`; prefer it to `assertEquals`.
- A failure names the test, the expected and real values, and the line; the exit code tells scripts.
- See every new test fail once, and give code its date from outside, so tests are stable.
- A project has `tests/`, `phpunit.xml.dist` and `autoload-dev`; `vendor/bin/phpunit -c shelf` runs *Shelf*'s tests.

## Further reading

- PHPUnit manual, Writing tests: https://docs.phpunit.de/en/13.4/writing-tests-for-phpunit.html
- PHPUnit manual, Assertions: https://docs.phpunit.de/en/13.4/assertions.html
- PHPUnit manual, The XML configuration file: https://docs.phpunit.de/en/13.4/configuration.html
