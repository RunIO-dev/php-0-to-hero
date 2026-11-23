# 60. Testing code that uses a database

## Goals

- You will be able to test classes that use PDO against a real database, made new for each test.
- You will be able to keep tests independent, so their order never matters.
- You will be able to test a transaction: what happens when it works, and when it refuses.
- You will be able to make sure a test double behaves like the real class, with contract tests.

## Before you start

- Chapters 41 and 42: PHPUnit, fakes and test doubles.
- Chapters 54 to 58: repositories, transactions and migrations.

## The idea

*Shelf*'s rules are tested with repositories in memory (chapter 42): fast, simple, no database. But the SQL itself was never tested. A typo in a column name, a missing `WHERE`, a transaction that doesn't roll back: none of the tests so far would notice. Those mistakes only show against a real database.

SQLite makes that cheap. `new PDO('sqlite::memory:')` is a whole database that lives in memory, takes about a millisecond to make, and is gone when the test ends. So each test can get its own, new database, with the tables made by the real migrations:

- **Independent tests.** No test sees what another wrote. They pass in any order, alone or together.
- **The real schema.** The migrations of chapter 58 build the tables, so the tests check the same `UNIQUE` and `CHECK` rules as the application.
- **Speed.** Hundreds of such tests run in well under a second.

Two more ideas help:

- **Test what the database promises, not how.** Add a book, then find it; lend, then look at the copies. Don't compare SQL text.
- **Contract tests.** The repository in memory is a stand-in for the real one, and every test of `Library` trusts it. If the two ever behave differently, those tests pass while the application fails. A **contract test** is one set of tests that both must pass: an abstract test class, and one small subclass per implementation.

A database server, such as MariaDB, can't be made new in a millisecond. There, each test runs inside a transaction that is rolled back at the end, so it leaves nothing behind.

## In code

The tests in this chapter test a small class that keeps notes about members, `code/60-testing-databases/notes.php`:

<!-- include: code/60-testing-databases/notes.php -->

### A new database for each test

<!-- include: code/60-testing-databases/NotesTest.php -->

- `setUp()` runs before every test, so each test gets a new database, with the table made, and a new `Notes`.
- The first test adds notes for two members, and checks that each member gets only theirs.
- The third test checks a rule that only the database knows, the `CHECK`. `expectExceptionMessage()` takes a part of the message, so the test still passes if SQLite adds details.

### Rolling back on a database server

<!-- include: code/60-testing-databases/RollbackTest.php -->

- `setUpBeforeClass()` runs once for the class: it connects, and makes the table. A `CREATE` or `DROP` in MariaDB ends any open transaction, so it can't be inside one.
- `setUp()` begins a transaction and `tearDown()` rolls it back, whatever the test did. The second test sees no note from the first.
- It connects as `shelf_admin`, because it makes a table. The application itself never would.

## Common mistakes

### One database for every test

<!-- include: code/60-testing-databases/SharedDatabaseTest.php -->

⚠ The database is made once and kept in a `static` property, to save time. The second test then finds the first test's note too, and fails. Worse, it would pass if run alone, so the failure seems to come and go, depending on which tests run.

**Fix:** a new database in `setUp()`, as `NotesTest` does, or a transaction rolled back in `tearDown()`.

### Testing only the fake

All of `Library`'s tests pass with `InMemoryBookRepository`, while `PdoBookRepository` has a typo in its `UPDATE`. The application is broken, and the tests are green.

**Fix:** test the real repository against a real database, and run the same contract tests on the fake, as *Shelf* does below.

### Tests that need the developer's database

A test that connects to `var/shelf.sqlite`, or to a database with data typed in by hand, passes on one computer and fails on all others, and may change data someone needs.

**Fix:** every test builds the data it needs, in a database made for it.

## Principle check

**DRY:** a contract test is written once and run on every implementation. **KISS:** SQLite in memory needs no server, no clean-up and no setup script. **Secure by default:** a rule that only the database enforces, such as `UNIQUE`, is tested where it lives, so removing it from a migration by accident fails a test.

## Security

Tests are where the security rules of this part are kept from breaking: that lending can't take the last copy twice, that a failed loan gives the copy back, that the database refuses a duplicate. A test never connects to a production database, and never needs its passwords: the database a test uses is made by the test.

## Shelf

*Shelf*'s database code gets tests of its own, and the repositories in memory are held to the same promises as the real ones.

**The contract for books.** An abstract test class, `shelf/tests/BookRepositoryContract.php`:

<!-- include: code/60-testing-databases/shelf/tests/BookRepositoryContract.php -->

- `repository()` is the one thing each subclass gives: a new repository with some books.
- `testAChangeCountsOnlyOnceItIsStored()` checks a promise that is easy to forget: a book that `find()` returns is a copy, so changing it changes nothing until `update()`. A fake that handed out its own objects would break that, and `Library`'s tests would no longer mean anything.
- The class name doesn't end in `Test`, so PHPUnit doesn't run it on its own; its subclasses do.

The repository in memory, `shelf/tests/InMemoryBookRepositoryTest.php`:

<!-- include: code/60-testing-databases/shelf/tests/InMemoryBookRepositoryTest.php -->

The one in the database, with a new SQLite database in memory for each test, made by `openDatabase(':memory:')` and the real migrations. It adds two tests that only the database can pass: every column comes back as it went in, and the `UNIQUE` on titles holds. `shelf/tests/Database/PdoBookRepositoryTest.php`:

<!-- include: code/60-testing-databases/shelf/tests/Database/PdoBookRepositoryTest.php -->

**The contract for loans.** Lending takes a copy, the last copy goes once, and a member's loans come back in order. `shelf/tests/LoanRepositoryContract.php`:

<!-- include: code/60-testing-databases/shelf/tests/LoanRepositoryContract.php lines 18-81, 89 -->

`shelf/tests/InMemoryLoanRepositoryTest.php`:

<!-- include: code/60-testing-databases/shelf/tests/InMemoryLoanRepositoryTest.php -->

The database version also checks the transaction of chapter 57: a loan that fails halfway gives the copy back, and leaves no transaction open. `shelf/tests/Database/PdoLoanRepositoryTest.php`:

<!-- include: code/60-testing-databases/shelf/tests/Database/PdoLoanRepositoryTest.php -->

**Members** have no fake with promises to keep in step, so their database test stands alone, `shelf/tests/Database/PdoMemberRepositoryTest.php`:

<!-- include: code/60-testing-databases/shelf/tests/Database/PdoMemberRepositoryTest.php -->

Run all of *Shelf*'s tests, from the course folder:

```
vendor/bin/phpunit -c shelf
```

*Shelf* now has 91 tests, and they still run in well under a second.

## Exercises

The exercises are in `exercises/60-testing-databases/`. In each, you write the tests, and a check makes sure they pass on the right code and catch every bug in `bugs/`. Check your work with:

```
composer exercise 60 beginner
```

1. **Beginner, `MembersTest.php`:** tests for a class with the four operations, each on a new database.
2. **Intermediate, `LendTest.php`:** tests for a transaction: what a loan changes, and what a refusal doesn't.
3. **Advanced, `MigrateTest.php`:** tests for a migration runner, with a folder of migrations made for each test.

## Summary

- Test SQL against a real database: with SQLite, `sqlite::memory:` is new and empty in a millisecond.
- Give every test its own database, made by the real migrations, so tests never depend on each other.
- On a database server, wrap each test in a transaction and roll it back in `tearDown()`.
- Test what the database promises, including the rules only it checks.
- Contract tests keep a test double honest: one set of tests, run on the fake and on the real class.

## Further reading

- PHPUnit manual, fixtures (setUp and tearDown): https://docs.phpunit.de/en/13.4/fixtures.html
- PHPUnit manual, test doubles: https://docs.phpunit.de/en/13.4/test-doubles.html
- SQLite documentation, in-memory databases: https://www.sqlite.org/inmemorydb.html
