# 56. Relations and JOINs

## Goals

- You will be able to connect tables with foreign keys, so the database keeps them consistent.
- You will be able to read from several tables at once with `JOIN` and `LEFT JOIN`.
- You will be able to count per group across tables.
- You will be able to store *Shelf*'s members and loans in the database.

## Before you start

- Chapter 53: tables, `NULL` and `GROUP BY`.
- Chapter 55: CRUD and repositories.

## The idea

A loan connects a book and a member. One way to store it would be a `loans` table with the book's title, author and year, and the member's name and role, copied into every row. Then a renamed member would have to be renamed in every loan, and one row forgotten is a member with two names.

Relational databases store each thing once, and connect rows by their ids. A loan row holds only `book_id`, `member_id` and the due date. `book_id` is a **foreign key**: a column that holds the primary key of a row in another table. The tables are **related**, which is where "relational" comes from.

Two things follow:

- **The database keeps the references valid.** With `REFERENCES books (id)`, it refuses a loan for a book that doesn't exist, and refuses to delete a book that a loan points to.
- **Reading needs a JOIN.** To show "Kindred, lent to Amira", a query brings the rows together again: each loan with the book and the member it points to.

This is the one-to-many shape: one member has many loans, and one book has many loans over time. It is the most common relation between tables.

## In code

The examples use the helpers of chapter 53, and three tables, `code/56-relations-and-joins/library.sql`:

<!-- include: code/56-relations-and-joins/library.sql -->

- `book_id INTEGER NOT NULL REFERENCES books (id)` makes `book_id` a foreign key to `books`.
- `PRAGMA foreign_keys = ON` tells SQLite to check foreign keys. It is off by default in SQLite, for old programs' sake, and must be set on every connection. Other databases always check them.

### JOIN

<!-- include: code/56-relations-and-joins/join.php -->

- `SELECT * FROM loans` shows what is stored: numbers.
- `JOIN books ON books.id = loans.book_id` pairs each loan with the book row whose `id` matches. The second `JOIN` does the same for members.
- With more than one table, write `table.column`: `books.title`, `loans.due`. It says where each column comes from.
- A plain `JOIN` keeps only pairs that match. A loan always has its book, so all four loans come back.

### LEFT JOIN

<!-- include: code/56-relations-and-joins/left-join.php -->

- `members LEFT JOIN loans` keeps *every* member, even one without loans. Where no loan matches, the loan's columns are `NULL`: Ana has no loans.
- `COUNT(loans.id)` counts the values that aren't `NULL`, so Ana gets 0. `COUNT(*)` would count her row and say 1.
- `WHERE loans.id IS NULL` after a `LEFT JOIN` finds the rows on the left with no match: the books that nobody has borrowed.

### Foreign keys at work

<!-- include: code/56-relations-and-joins/foreign-keys.php -->

- There is no book 99, so the loan is refused.
- *Kindred* has loans, so it can't be deleted: the loans would point to nothing.
- *Kitchen* has none, and goes.

## Common mistakes

### Foreign keys that SQLite doesn't check

<!-- include: code/56-relations-and-joins/no-pragma.php -->

With `foreign_keys` off, *Kindred* is deleted, and two loans now point to a book that doesn't exist. The `LEFT JOIN` shows them with `NULL` titles. Such rows are called **orphans**, and every page that lists loans has to cope with them.

**Fix:** `PRAGMA foreign_keys = ON` right after connecting, on every connection. *Shelf*'s `openDatabase()` does it.

### A JOIN without ON

<!-- include: code/56-relations-and-joins/no-on.php -->

Without `ON`, every loan is paired with every book: 4 × 4 = 16 rows. With real tables of thousands of rows, that is millions of rows, and a page that never loads.

**Fix:** every `JOIN` gets an `ON` that says which rows belong together.

### Ambiguous column names

<!-- include: code/56-relations-and-joins/ambiguous.php -->

Both tables have an `id`, so SQLite can't know which one you mean: `ambiguous column name: id`.

**Fix:** name the table, `loans.id`.

## Principle check

**DRY:** a book's title is stored once, in `books`; a loan refers to it by id, so renaming a book changes one row. **Secure by default:** the database refuses loans for books or members that don't exist, whatever the code does. **KISS:** one `JOIN` query gives whole `Loan` objects, instead of one query per loan.

## Security

Foreign keys protect the data's consistency, which matters for security too: a check that "this loan belongs to this member" (Part 9) is only as good as the references it reads. The queries in this chapter take values through placeholders, as always; table and column names in a `JOIN` are written in the code, never taken from a request.

## Shelf

Members and loans move into the database. Lending a book now stores the loan, so *Shelf* knows who has what, and a book that is lent can't be removed.

**The tables.** `shelf/schema.sql` gets `members` and `loans`:

<!-- include: code/56-relations-and-joins/shelf/schema.sql -->

`openDatabase()` turns on foreign keys, `shelf/src/functions.php`:

<!-- include: code/56-relations-and-joins/shelf/src/functions.php -->

**Two new repositories.** Members are found by name, `shelf/src/MemberRepository.php`:

<!-- include: code/56-relations-and-joins/shelf/src/MemberRepository.php -->

<!-- include: code/56-relations-and-joins/shelf/src/PdoMemberRepository.php -->

Loans are stored and read with their book and member, `shelf/src/LoanRepository.php`:

<!-- include: code/56-relations-and-joins/shelf/src/LoanRepository.php -->

<!-- include: code/56-relations-and-joins/shelf/src/PdoLoanRepository.php -->

- `add()` stores the ids, which it finds with two small queries inside the `INSERT`, called **subqueries**: `(SELECT id FROM books WHERE title = :title)`. A title or name that doesn't exist gives `NULL`, and `NOT NULL` refuses the loan.
- `forMember()` joins the three tables, and one row becomes one `Loan`, with its `Book` and its `Member`.
- `isOnLoan()` counts a book's loans.
- The due date is stored as ISO text, `2026-03-16`, which sorts in date order. `createFromFormat('!Y-m-d', ...)` reads it back; the `!` sets the time to midnight.

**The rules.** `Library` gets a repository for each kind of thing, stores each loan, lists a member's loans, and refuses to remove a book that is on loan, with a clear message rather than the database's error. `shelf/src/Library.php`:

<!-- include: code/56-relations-and-joins/shelf/src/Library.php -->

**The command line** imports members too, and gets `loans <member>`. A loan's due date depends on the day you lend, so the session below doesn't lend: the tests do, with a fixed date. `shelf/shelf.php`:

<!-- include: code/56-relations-and-joins/shelf/shelf.php -->

The front controller makes the three repositories from one connection, `shelf/public/index.php`:

<!-- include: code/56-relations-and-joins/shelf/public/index.php -->

When removing a book is refused, the book's page shows why, above its forms. `App` catches the refusal, `shelf/src/Web/App.php`:

<!-- include: code/56-relations-and-joins/shelf/src/Web/App.php -->

`shelf/templates/book.php`:

<!-- include: code/56-relations-and-joins/shelf/templates/book.php -->

**The tests** get members and loans in arrays, `shelf/tests/InMemoryMemberRepository.php` and `shelf/tests/InMemoryLoanRepository.php`:

<!-- include: code/56-relations-and-joins/shelf/tests/InMemoryMemberRepository.php -->

<!-- include: code/56-relations-and-joins/shelf/tests/InMemoryLoanRepository.php -->

`LibraryTest` lends with fixed dates, and checks the order of a member's loans and the refusal, `shelf/tests/LibraryTest.php`:

<!-- include: code/56-relations-and-joins/shelf/tests/LibraryTest.php -->

`AppTest` checks that a book on loan stays, `shelf/tests/Web/AppTest.php`:

<!-- include: code/56-relations-and-joins/shelf/tests/Web/AppTest.php -->

After pulling this chapter, delete `shelf/var/shelf.sqlite` and import again: the old file has no `members` table yet, and `IF NOT EXISTS` would add the new tables, but not the members. Chapter 58 shows how to change a database that holds data you want to keep.

## Exercises

The exercises are in `exercises/56-relations-and-joins/`. Check your work with:

```
composer exercise 56 beginner
```

1. **Beginner, `queries.php`:** every loan with its title and member, and the books nobody has borrowed.
2. **Intermediate, `counts.php`:** loans per member, including none, and per author.
3. **Advanced, `overdue.php`:** the list of overdue loans on a given day, with how many days late.

## Summary

- A foreign key holds another row's id; `REFERENCES` makes the database keep it valid.
- In SQLite, turn foreign keys on with `PRAGMA foreign_keys = ON` on every connection.
- `JOIN ... ON` brings related rows together; `LEFT JOIN` keeps rows with no match, with `NULL`s.
- Name the table in front of each column when a query has more than one table.
- Store each thing once, and refer to it by id.

## Further reading

- SQLite documentation, foreign key support: https://www.sqlite.org/foreignkeys.html
- SQLite documentation, SELECT (joins): https://www.sqlite.org/lang_select.html
- PHP manual, `DateTimeImmutable::createFromFormat`: https://www.php.net/manual/en/datetimeimmutable.createfromformat.php
