# 42. Testing well

## Goals

- You will be able to write a test in three clear steps: arrange, act, assert.
- You will be able to test that code refuses bad input, with `expectException`.
- You will be able to run one test with many cases, with a data provider.
- You will be able to replace a file, a clock or a mailer in a test with a **fake**, and decide what not to test.

## Before you start

- Chapter 41: PHPUnit, test cases and `assertSame`.
- Chapter 33: interfaces and the repository; chapter 19: exceptions.

## The idea

Chapter 41 showed how to write a test. This chapter is about writing tests that are worth keeping: tests that fail for one clear reason, that say what the rule is, and that still pass after you improve the code without changing what it does.

Four habits make the difference:

- **One rule per test, in three steps.** Arrange the world, act once, assert what must be true. When such a test fails, its name tells you which rule broke.
- **Test the refusals too.** Much of a program's value is in what it won't do: lend a book that isn't there, accept a year 3000. A rule that refuses is tested by expecting the exception.
- **Many cases, one test.** When the only difference between tests is the data, write the test once and give it a table of cases.
- **Fakes for the outside world.** Files, the clock, email and databases are slow, change, or have effects you don't want in a test. Code that depends on an interface (chapter 33) can get a small fake instead: an in-memory repository, a fixed clock, a mailer that only collects.

And one decision: what not to test. Tests are code too, and every test is code that must change when the rules change.

## In code

### Arrange, act, assert

<!-- include: code/42-testing-well/stock.php -->

<!-- include: code/42-testing-well/StockTest.php -->

The first test shows the three steps with comments; usually a blank line between them is enough, as in the second. **Arrange** creates the stock with two copies. **Act** is one call, the thing the test is about. **Assert** checks the result. A test with two acts tests two things, and should be two tests.

### Testing an exception

The second test checks a refusal. `$this->expectException(NoCopyLeft::class)` tells PHPUnit: from here on, this exception must be thrown, or the test fails. `expectExceptionMessage` also checks the message, or the start of it, because the message is what the user reads.

Write the expectations just before the act that should throw. Everything above them must work; if `new Stock` or the first `lend()` threw, the test would fail, as it should.

### A data provider

<!-- include: code/42-testing-well/FeeTableTest.php -->

- `#[DataProvider('fees')]` is an attribute (chapter 34) on the test: run this test once for each case that the method `fees` returns.
- `fees()` is `public static` and returns an array of cases. Each case is a list of the test's arguments: here, the days late and the expected fee.
- The keys are names. PHPUnit shows them, as in `with data set "early"`, so a failure names its case.

Four tests for the price of one, and a new case is one line. Keep a data provider for cases of one rule; when the cases need different assertions, they are different tests.

### Fakes

To test `Library`, *Shelf*'s tests below don't use `books.json`. They use `InMemoryBookRepository`, a class of 20 lines in `tests/` that implements `BookRepository` with an array. The library can't tell the difference, because it only knows the interface; that was the point of chapter 33.

A fake is only for tests, and as simple as it can be. Two rules keep it honest:

- It behaves like the real thing where it matters. `InMemoryBookRepository::all()` returns copies of the books, as reading a file does, so a test notices when the library forgets to `save`.
- It is not tested itself. If a fake needs tests, it is too clever: write a simpler one.

The same goes for time: a `FixedClock` that always returns one date replaces "today", and a mailer that collects messages in an array replaces real email. The advanced exercise builds both.

## Common mistakes

### Too much in one test, and tests of PHP

<!-- include: code/42-testing-well/TooMuchTest.bad.php -->

✗ The first test checks that PHP's constructor promotion works; it does, and the test protects no rule of yours. The second tests three rules in a row: when it fails, you have to read the whole test to know which one. And the `catch` swallows the exception, so the test passes even if `lend()` stops throwing.

**Fix:** one rule per test, named after the rule, and `expectException` for refusals. Test your rules, not the language.

### Testing private methods

A test that reaches into a private method, with tricks such as reflection, breaks whenever you improve the inside of a class, even when every rule still holds.

**Fix:** test through the public methods, as a user of the class would. `Library::find` is private; the tests check it through `lend`: an unknown title throws `BookNotFound`. If a private method seems to need tests of its own, it may want to be a class of its own (chapter 37).

### Tests that share state

A static property, a file or a database row that one test changes and the next one reads makes tests pass or fail depending on their order.

**Fix:** arrange everything each test needs, in the test or in `setUp()`, a method PHPUnit runs before each test. Every test gets a new object of the test class, so properties set in `setUp` start fresh.

### A fake that is never wrong

A fake whose `all()` returns the same objects that `save()` stored makes "forgot to save" invisible: the change is already in the array.

**Fix:** make the fake behave like the real thing where it matters: here, return copies.

## Principle check

**KISS:** one rule per test, three steps, and fakes of a few lines. **DRY:** a data provider instead of copied tests; `setUp()` for the arrangement every test needs. **YAGNI:** don't test what PHP or a library guarantees, and don't build a fake for something you can use for real, such as a value object.

## Security

The refusals are where the security rules live: an unknown member can't borrow, a duplicate can't be added, a bad ISBN never becomes an `Isbn`. Test each refusal with `expectException`, so that no refactoring can quietly remove it. Data providers make it cheap to try the strange inputs an attacker would: an empty string, spaces, letters where digits belong.

## Shelf

*Shelf*'s rules get the tests chapter 41 left out. Three new files in `shelf/tests/`; the code in `src/` doesn't change.

The fake repository, `shelf/tests/InMemoryBookRepository.php`:

<!-- include: code/42-testing-well/shelf/tests/InMemoryBookRepository.php -->

The library's rules, `shelf/tests/LibraryTest.php`:

<!-- include: code/42-testing-well/shelf/tests/LibraryTest.php -->

- `setUp()` arranges what every test needs: two books in a fake repository, and one member. Each test then has only its act and its assertions.
- The tests use lowercase titles and names (`'things fall apart'`, `'amira'`), because finding them in any case is a rule too.
- Four of the six tests are refusals: the last copy, an unknown book, an unknown member, a duplicate title. The duplicate test adds `'  KINDRED '` to check that spaces and case don't get around the rule.

The ISBN check, with two data providers, `shelf/tests/IsbnTest.php`:

<!-- include: code/42-testing-well/shelf/tests/IsbnTest.php -->

`new Isbn($input)->digits` uses an object right after `new` without extra parentheses, which PHP allows from 8.4.

Run all of *Shelf*'s tests:

```
vendor/bin/phpunit -c shelf --testdox
```

`OK (18 tests, 22 assertions)`: four for the loan, six for the library, eight for the ISBN.

## Exercises

The exercises are in `exercises/42-testing-well/`. Check your work with:

```
composer exercise 42 beginner
```

1. **Beginner, `MemberTest.php`:** test that a new member is refused for each reason, with the right exception and message, and at the right age.
2. **Intermediate, `ShelfCodeTest.php`:** replace four copied tests with one test and a data provider, and add the case that catches the last bug.
3. **Advanced, `RemindersTest.php`:** write a fixed clock and a collecting mailer, and test reminders with them.

## Summary

- One rule per test, in three steps: arrange, act, assert.
- `expectException` and `expectExceptionMessage`, written just before the act, test a refusal.
- `#[DataProvider('cases')]` runs one test with many named cases.
- Fakes, such as an in-memory repository or a fixed clock, replace the outside world through an interface.
- Test rules through public methods; don't test private methods or PHP itself.

## Further reading

- PHPUnit manual, Writing tests (exceptions, data providers): https://docs.phpunit.de/en/13.4/writing-tests-for-phpunit.html
- PHPUnit manual, Fixtures (`setUp`): https://docs.phpunit.de/en/13.4/fixtures.html
- PHPUnit manual, Test doubles: https://docs.phpunit.de/en/13.4/test-doubles.html
