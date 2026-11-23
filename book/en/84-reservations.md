# 84. Reservations

## Goals

- You will be able to keep a queue in a table: first come, first served, with each member's place in it.
- You will be able to move a thing through states (waiting, ready, collected, cancelled, expired) with rules for each step.
- You will be able to claim a row with one `UPDATE` that checks its state, so two requests never take the same one.
- You will be able to let something that waits run out after a fixed number of days, and pass it on.
- You will be able to make one rule hold everywhere a copy becomes free.

## Before you start

- Chapter 57: transactions, and taking a copy with one `UPDATE`.
- Chapter 74: Doctrine entities and DQL.
- Chapter 83: the lending desk, returned loans, and `copies = copies + 1`.

## The idea

Kindred's copies are all out. Until now, a reader could only come back another day and hope. This chapter gives *Shelf* **reservations**: a reader reserves a book whose copies are all out, and joins its queue. When a copy comes back, it doesn't go on the shelf: the desk sets it aside for the first in the queue, who has a week to collect it. A copy that nobody collects goes to the next in line, or back on the shelf.

**A queue is an order.** The reservations of one book, sorted by their number, are the queue: the database gives each new one a higher number. A member's place is the number of waiting reservations up to and including theirs. Nobody renumbers anything when someone leaves the queue; the count simply comes out one lower.

**States, and the steps between them.** A reservation is *waiting*, then *ready* when a copy is set aside for it, then *collected* when the member borrows it. It can be *cancelled* while it is open, and it *expires* when the week is over. `HoldStatus` names the states, and `Hold` allows only the steps that make sense: only a waiting reservation can get a copy, and nothing happens to one that is over. Like loans, reservations that are over stay, as history.

**A copy set aside is off the shelf.** `copies` keeps meaning what it has meant since chapter 55: the copies on the shelf, free for anyone. A copy set aside is not counted there, so the list, the search's "on the shelf only", the API and the desk all stay right without a change. Lending it to the member who reserved it takes no copy from the shelf; it collects the reservation instead.

**One rule, wherever a copy becomes free.** A copy becomes free when a loan comes back, when a reservation with a copy is cancelled or expires, and when the staff add copies. All four go through one method, `HoldRepository::passOn()`: the first in the queue gets the copy, or it goes on the shelf.

**Claimed once.** Two copies of Kindred come back at the same moment, at two desks. Each desk looks for the first in the queue and finds Amira. If each simply sets her reservation to ready, Amira gets two copies and Ben, second in line, gets none. So the `UPDATE` changes the row only `WHERE status = 'waiting'`, and the desk looks at how many rows changed. One row: the copy is Amira's. No row: the other desk was quicker, so this copy goes to the next in line.

**No extending while someone waits.** A loan of a book that someone has reserved can't be extended: the member brings it back by its due date, and the queue moves on. "Extend" isn't shown then.

## In code

### A queue

<!-- include: code/84-reservations/queue.php -->

<!-- include: code/84-reservations/queue.out -->

- The place is counted, not stored: `COUNT(*)` of the waiting reservations up to this one.
- `ORDER BY id LIMIT 1` finds the first in line.

### Claimed once

<!-- include: code/84-reservations/claim.php -->

<!-- include: code/84-reservations/claim.out -->

- Both desks read the queue before either writes, as two requests can.
- `AND status = 'waiting'` makes the second `UPDATE` change nothing, and `rowCount()` says so.

## Common mistakes

### Jumping the queue

<!-- include: code/84-reservations/jump.bad.php -->

✗ The copy goes on the shelf, and whoever walks in next borrows it. The queue waits for ever.

**Fix:** every copy that becomes free goes through one method that asks the queue first, as `passOn()` does.

### A copy set aside for ever

A reservation becomes ready, and the member never comes. Without an end date, the copy sits at the desk for good.

**Fix:** a last day (`readyUntil`), and a job that passes on what expired: `bin/console shelf:holds`, once a day. Chapter 85 runs it on a timer.

### Reserving what is on the shelf

A reservation for a book with a copy on the shelf jumps ahead of whoever is standing at the desk.

**Fix:** a book can be reserved only while every copy is out. `Library::reserve()` checks it, and the page shows "Reserve" only then.

## Principle check

**DRY:** "a copy is free" is one method, `passOn()`, used by a return, a cancel, an expiry and new copies. **KISS:** the queue is the order of the row numbers, and a place is a count: nothing to keep in step. **Secure by default:** a reader cancels only their own reservations, and anyone else's is a 404, as for loans in chapter 83.

## Security

- **Someone else's reservation is a 404.** Cancelling takes a number from a form, so `Library::cancelHold()` checks whose reservation it is, as `renew()` does for loans.
- **The queue tells no one who else waits.** A member sees their own place, never the names before them. Only the staff see who a copy is set aside for.
- **A blocked member can't reserve**, just as they can't borrow (chapter 83).

## Shelf

### Reservations

The states, `shelf/src/HoldStatus.php`:

<!-- include: code/84-reservations/shelf/src/HoldStatus.php -->

The reservation and its rules, `shelf/src/Hold.php`:

<!-- include: code/84-reservations/shelf/src/Hold.php lines 20-36, 56-103 -->

- `placedOn` isn't `readonly`: `refresh()` sets every column again, with a new date object, as for the ISBN in chapter 77.
- `readyUntil` stays after the reservation ends, so `hadACopy()` knows whether a copy must go on.

Where they are kept, `shelf/src/HoldRepository.php`:

<!-- include: code/84-reservations/shelf/src/HoldRepository.php -->

`shelf/src/Doctrine/DoctrineHoldRepository.php`:

<!-- include: code/84-reservations/shelf/src/Doctrine/DoctrineHoldRepository.php lines 68-120, 137-146 -->

- `passOn()` claims the first in the queue with a DQL `UPDATE ... WHERE h.status = :waiting`, and tries the next if no row changed.
- `release()` stores a cancelled or expired reservation and passes its copy on, in one transaction.

A returned copy now goes to `passOn()`, and a loan collects the reservation it was set aside for, `shelf/src/Doctrine/DoctrineLoanRepository.php`:

<!-- include: code/84-reservations/shelf/src/Doctrine/DoctrineLoanRepository.php -->

The migration creates the table with Doctrine's own SQL: a new table changes nothing that exists, `shelf/migrations/Version20261119090000.php`:

<!-- include: code/84-reservations/shelf/migrations/Version20261119090000.php -->

### The library's rules

`reserve()`, `cancelHold()` and `expireHolds()`; `lend()` takes a copy set aside, `renew()` refuses while someone waits, and `changeCopies()` gives new copies to the queue first, `shelf/src/Library.php`:

<!-- include: code/84-reservations/shelf/src/Library.php lines 122-139, 152-178, 199-210, 222-233 -->

The daily job, `shelf/src/Command/HoldsCommand.php`:

<!-- include: code/84-reservations/shelf/src/Command/HoldsCommand.php -->

### The pages

A book's page offers "Reserve" while every copy is out, and shows the member's place in the queue, `shelf/src/Controller/BookController.php` and `shelf/templates/book.html.twig` (in the snapshot). "My loans" lists the reservations, each with "Cancel", `shelf/src/Controller/AccountController.php` and `shelf/templates/loans.html.twig`. The desk lists the copies set aside, each lent with one button, `shelf/templates/desk.html.twig`:

<!-- include: code/84-reservations/shelf/templates/desk.html.twig -->

### Tests

The repository contract, for both repositories, `shelf/tests/HoldRepositoryContract.php`:

<!-- include: code/84-reservations/shelf/tests/HoldRepositoryContract.php lines 40-112 -->

Reservations in the browser, `shelf/tests/Web/HoldsTest.php`:

<!-- include: code/84-reservations/shelf/tests/Web/HoldsTest.php lines 26-92 -->

`HoldTest` tests the steps between states, `LibraryTest` the rules, `DoctrineHoldRepositoryTest` that the queue is read from the database, and `CommandsTest` the daily job.

## Exercises

The exercises are in `exercises/84-reservations/`. Check your work with:

```
composer exercise 84 beginner
```

1. **Beginner, `queue.php`:** a member's place in a book's queue.
2. **Intermediate, `Pickup.php`:** the last day to collect a copy, never on a Sunday, and the days left.
3. **Advanced, `Holds.php`:** reservations on a database, with a claim that checks the state, and a cancel that passes the copy on in one transaction.

## Summary

- A reader reserves a book whose copies are all out; the queue is the order of the reservations.
- A copy that becomes free goes to the first in the queue, set aside for a week, or on the shelf: one method, `passOn()`.
- An `UPDATE ... WHERE status = 'waiting'` claims a reservation once, however many desks try at once.
- A copy nobody collects goes on: `bin/console shelf:holds`, once a day.
- A loan someone waits for can't be extended.

## Further reading

- SQLite documentation, the `changes()` function (what `rowCount()` reads): https://www.sqlite.org/lang_corefunc.html#changes
- Doctrine ORM documentation, DQL `UPDATE` and `DELETE`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/dql-doctrine-query-language.html#update-queries
- PHP manual, `PDOStatement::rowCount`: https://www.php.net/manual/en/pdostatement.rowcount.php
- Martin Fowler, optimistic offline lock (checking that a row is unchanged before writing): https://martinfowler.com/eaaCatalog/optimisticOfflineLock.html
