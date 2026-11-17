# 57. Transactions

## Goals

- You will be able to group changes that belong together, so they are saved together or not at all.
- You will be able to write the transaction pattern once: begin, commit, and roll back on any error.
- You will be able to explain a lost update, and prevent it with a check and a change in one statement.
- You will be able to make lending in *Shelf* all-or-nothing.

## Before you start

- Chapter 56: *Shelf*'s loans, which change two tables.
- Chapter 18: exceptions, `try`, `catch` and `finally`.

## The idea

Lending a book changes two things: one copy less on the shelf, and one loan more. If the program stops between the two (an error, a full disk, a server restart), the library has one copy less and no record of who has it.

A **transaction** groups statements into one unit:

- `beginTransaction()` starts it. The changes that follow are not yet final, and other connections don't see them.
- `commit()` makes them all final, at once.
- `rollBack()` undoes them all, as if they never happened.

If the program dies before `commit()`, the database rolls back by itself. So the data is always in a state from before or after the transaction, never in between. Databases promise four things about transactions, often called **ACID**: a transaction is **atomic** (all or nothing), keeps the data **consistent** (every rule holds after it), runs **isolated** from others, and is **durable** once committed.

Transactions also help with the second danger: two requests at the same moment. If both read "1 copy left" and then both lend it, the library has lent one copy twice. This is a **lost update**: the second write is based on a value that was already out of date. The cure is to check and change in *one* statement, `UPDATE ... SET copies = copies - 1 WHERE ... AND copies > 0`, and to look at `rowCount()`. The database runs one statement at a time on a row, so only one of the two requests can take the last copy.

## In code

The examples use a small library and a helper that prints its state, `code/57-transactions/state.php`:

<!-- include: code/57-transactions/state.php -->

### Half done

<!-- include: code/57-transactions/half-done.php -->

The `UPDATE` worked, the `INSERT` failed: there is no book 99. *Sula* has one copy less, and there is no loan. This is the state a transaction prevents.

### All or nothing

<!-- include: code/57-transactions/transaction.php -->

- Book 2 exists: both statements run, and `commit()` saves them.
- Book 99 doesn't: the `INSERT` throws, `rollBack()` undoes the `UPDATE` too, and the state is as before.
- The `catch` must end the transaction with `rollBack()`, every time. Common mistakes shows what happens otherwise.

### Two requests at once

<!-- include: code/57-transactions/race.php -->

- `race.php` opens two connections to one database file, like two visitors' requests, and lets them take turns, so the timing that is rare on a real server happens every time.
- **Read, then write:** both read 1 copy, and both write "1 − 1 = 0". *Kindred* is lent to Amira and to Jonas, and the shelf has no copy for either. No error, no warning.
- **Check and take in one statement:** `copies > 0` is part of the `UPDATE`. For Jonas, it matches no row, `rowCount()` is 0, and the lending stops before the loan is stored.
- A transaction alone doesn't fix the first version: both transactions could still read 1 before either writes. The single statement is what makes the check and the change one step.

## Common mistakes

### A transaction that is never finished

<!-- include: code/57-transactions/nested.error.php -->

⚠ An early `return`, or an exception that is caught and forgotten, leaves the transaction open. The next `beginTransaction()` fails: `There is already an active transaction`. In a long-running program, everything after it would also be part of the unfinished transaction, and be lost when the connection closes.

**Fix:** one place that always ends it: `commit()` at the end of `try`, `rollBack()` in `catch (Throwable $e)`, then `throw $e` again. *Shelf* writes that once, as a function, below.

### Catching inside, committing anyway

<!-- include: code/57-transactions/caught-inside.bad.php -->

✗ The exception is caught *inside* the transaction, so the program goes on, and `commit()` saves the `UPDATE` without its loan. All-or-nothing only works if a failure leaves the transaction.

**Fix:** catch outside the transaction, roll back there, and let the exception go on.

### Long transactions

A transaction holds locks: in SQLite, while one connection writes, others wait. A transaction that waits for a visitor, a file upload or an email to be sent makes everyone else wait.

**Fix:** do slow work before or after, and keep only the database statements inside.

## Principle check

**Secure by default:** lending can't leave the data half changed, and can't lend the last copy twice, however many requests come at once. **DRY:** the begin, commit and roll back pattern is written once, in `inTransaction()`, and used wherever changes belong together. **KISS:** one guarded `UPDATE` instead of locks managed by hand.

## Security

A lost update is a security problem when the value matters: two withdrawals from one account, one coupon used twice, two people booking the last seat. The pattern is always the same: put the check into the statement that changes (`WHERE balance >= :amount`, `WHERE used = 0`), and look at `rowCount()`. Checking in PHP first and writing afterwards leaves a gap between the two that another request can use.

## Shelf

Lending becomes one transaction, and the last copy can only go once.

**The pattern, once.** `shelf/src/functions.php` gets `inTransaction()`:

<!-- include: code/57-transactions/shelf/src/functions.php -->

- `@template T` tells PHPStan that `inTransaction()` returns what `$work` returns. A transaction that computes a value, such as the import's message, keeps its type.
- `catch (Throwable $e)` catches every error and exception, not only `PDOException`: a bug in `$work` must undo the changes too.

**Lending in one step.** Storing a loan now takes the copy too, `shelf/src/LoanRepository.php`:

<!-- include: code/57-transactions/shelf/src/LoanRepository.php -->

`shelf/src/PdoLoanRepository.php`:

<!-- include: code/57-transactions/shelf/src/PdoLoanRepository.php -->

The guarded `UPDATE` comes first. If it changes nothing, `NoCopyLeft` is thrown, `inTransaction()` rolls back, and no loan is stored.

`Library::lend()` no longer updates the book itself. It still checks `isAvailable()`, so the usual case gets its message without a database round trip; the repository's check is the one that can't be beaten. `shelf/src/Library.php`:

<!-- include: code/57-transactions/shelf/src/Library.php -->

In the tests, the loans in memory take the copy from the books in memory, `shelf/tests/InMemoryLoanRepository.php`:

<!-- include: code/57-transactions/shelf/tests/InMemoryLoanRepository.php -->

`LibraryTest` and `AppTest` pass the books to it; the tests themselves don't change, because the rules didn't. `shelf/tests/LibraryTest.php`:

<!-- include: code/57-transactions/shelf/tests/LibraryTest.php -->

`shelf/tests/Web/AppTest.php`:

<!-- include: code/57-transactions/shelf/tests/Web/AppTest.php -->

**The import** runs in one transaction too: a mistake in `members.json` now imports nothing, instead of the books without the members. It is also faster: SQLite writes to the disk once, at the commit, instead of once per row. `shelf/shelf.php`:

<!-- include: code/57-transactions/shelf/shelf.php -->

## Exercises

The exercises are in `exercises/57-transactions/`. Check your work with:

```
composer exercise 57 beginner
```

1. **Beginner, `return.php`:** a book comes back: the loan goes and the copy returns, together.
2. **Intermediate, `transaction.php`:** write `inTransaction()`, and make it undo everything on any error.
3. **Advanced, `lend.php`:** lending with two rules, a copy left and at most three books per member, safe when requests come at once.

## Summary

- A transaction saves a group of changes together, or none of them: `beginTransaction()`, `commit()`, `rollBack()`.
- Always end a transaction: commit at the end of `try`, roll back in `catch (Throwable $e)`, and throw on.
- Reading a value and writing a new one later can lose an update; check and change in one statement, then look at `rowCount()`.
- Keep transactions short: no waiting for people, files or networks inside.
- *Shelf* lends in one transaction, and the last copy goes once.

## Further reading

- PHP manual, transactions and auto-commit: https://www.php.net/manual/en/pdo.transactions.php
- SQLite documentation, transactions: https://www.sqlite.org/lang_transaction.html
- SQLite documentation, atomic commit: https://www.sqlite.org/atomiccommit.html
