# 53. Databases and SQL basics, with SQLite

## Goals

- You will be able to explain what a database is, and why *Shelf* needs one.
- You will be able to ask questions in SQL: `SELECT` with `WHERE`, `ORDER BY` and `LIMIT`.
- You will be able to change data with `INSERT`, `UPDATE` and `DELETE`.
- You will be able to design a table whose rules the database checks.

## Before you start

- Chapter 22: files, which is how *Shelf* keeps its books so far.
- Chapter 33: the `BookRepository` interface, which chapter 54 implements with a database.

## The idea

*Shelf* keeps its books in `books.json`. That works for one person and a few books, but a file has limits:

- To change one book, the program reads the whole file and writes it again.
- Two requests at the same moment can both read the file, and the second one to write it loses the first one's change.
- Every rule, such as "copies is never negative", lives only in our PHP code. A file edited by hand, or by another program, can break it.
- A question such as "which books by Toni Morrison are on the shelf?" means reading everything and filtering in PHP.

A **database** is a program that keeps data for you and answers questions about it. The kind we use is a **relational database**: the data is in **tables**. A table has **columns**, each with a name and a type, and **rows**, one per thing. The `books` table has the columns `title`, `author`, `year` and `copies`, and one row per book.

You talk to it in **SQL** (Structured Query Language): you say *what* you want, and the database finds out *how*. SQL has been the language of relational databases for about 50 years. MySQL, MariaDB, PostgreSQL and SQLite all speak it, each with small differences.

This part uses **SQLite**. It is not a server: the whole database is one file, and PHP has it built in, so nothing needs to be installed. It is used in phones, browsers and many programs. Chapter 59 runs the same code against MariaDB, a database server.

## In code

The examples of this chapter use two small helpers in `code/53-sql-basics/db.php`:

- `database()` makes a new, empty database in memory and runs an `.sql` file in it;
- `show()` runs one SQL statement and prints it with its answer.

How PHP talks to the database is chapter 54. Here, read only the SQL: it is the same in any language.

The examples' library, `code/53-sql-basics/library.sql`:

<!-- include: code/53-sql-basics/library.sql -->

- `CREATE TABLE` makes a table, column by column: a name and a type. `INTEGER` is a whole number and `TEXT` is text.
- `id INTEGER PRIMARY KEY` gives every row a number that identifies it. SQLite chooses the next free number when a row is added without one.
- `NOT NULL` means the column must have a value. `NULL` is SQL's word for "no value", like PHP's `null`.
- `UNIQUE` means no two rows may have the same value in that column.
- `DEFAULT 1`: a row added without `copies` gets 1.
- `INSERT INTO ... VALUES` adds rows. Text is written between single quotes.
- SQL keywords are written in capitals here, so you can tell them from names. SQLite would accept `select` too.

### Asking: SELECT

<!-- include: code/53-sql-basics/select.php -->

- `SELECT title, year FROM books` asks for two columns of every row. Without `ORDER BY`, the order is whatever the database finds fastest: never count on it.
- `ORDER BY year DESC` sorts, here from the newest; `ASC`, the default, sorts from the smallest.
- `LIMIT 3` keeps the first three rows of the answer.
- `SELECT *` asks for every column. It is handy for a look, as here. In a program, name the columns you need: the code then says what it reads, and a new column doesn't change the answer.

### Choosing rows: WHERE

<!-- include: code/53-sql-basics/where.php -->

- `WHERE` keeps the rows for which the condition is true. `=` compares, as `===` does in PHP, and `AND`, `OR` and `NOT` combine conditions.
- `LIKE '%the%'` matches text with a pattern: `%` stands for any text, including none. SQLite's `LIKE` ignores the case of English letters, so it finds "Parable of the Sower". "Things Fall Apart" has no "the" in it.
- `IS NOT NULL` keeps the rows that have a value. Why it isn't `!= NULL` is under Common mistakes.

### Changing: INSERT, UPDATE and DELETE

<!-- include: code/53-sql-basics/change.php -->

- The `INSERT` leaves out `id` and `copies`: SQLite gives *Americanah* the next number, 7, and the default of one copy.
- `UPDATE books SET copies = copies - 1 WHERE title = 'Sula'` changes one column of the matching rows. The new value can use the old one.
- `DELETE FROM books WHERE ...` removes the matching rows.
- Each one answers with how many rows it changed. 0 is not an error: no row matched.

### Counting: COUNT, SUM and GROUP BY

<!-- include: code/53-sql-basics/count.php -->

- `COUNT(*)` counts rows, and `SUM(copies)` adds up a column. `AS` names a column of the answer.
- `GROUP BY author` makes one group per author, and `COUNT(*)` then counts the rows of each group. The database does the work that a loop and an array would do in PHP.

## Common mistakes

### An UPDATE without WHERE

<!-- include: code/53-sql-basics/no-where.php -->

✗ The `WHERE` was forgotten, so the change applies to every row: six rows changed. *Kindred* now has −1 copies. SQL doesn't ask "are you sure?".

**Fix:** write the `WHERE` first, and read the "rows changed" number: it should match what you meant. The rules in the table, below, stop the −1.

### Comparing with NULL

<!-- include: code/53-sql-basics/null.php -->

`isbn = NULL` finds nothing, not even the rows without an ISBN. `NULL` means "unknown", so "is this equal to unknown?" has no answer, and `WHERE` keeps only rows where the answer is *true*.

**Fix:** `IS NULL` and `IS NOT NULL`.

### A quote inside text

<!-- include: code/53-sql-basics/quotes.php -->

The `'` in "Midnight's" ends the text early, and SQLite reads `s Children'` as more SQL: `near "s": syntax error`.

**Fix:** in SQL, a quote inside text is written twice: `'Midnight''s Children'`. In a program, you won't do even that: chapter 54 sends values apart from the SQL, and this mistake, and a dangerous attack built on it, can't happen.

## Principle check

**Secure by default:** the rules that must never break, such as no negative copies and no two books with one title, go into the table, so every program that uses the database obeys them. **KISS:** SQLite is one file, with no server to run. **YAGNI:** one table now; members and loans come in chapter 56, when *Shelf* needs them.

## Security

The quote mistake above is a warning: a value that ends up inside SQL text can change the SQL. When the value comes from a visitor, they can change the query to read or delete anything. This is called **SQL injection**, and chapter 54 shows the one way we write queries, so that it can't happen.

A database file holds your data. Keep it outside `public/`, as the covers in chapter 52, so no address can download it.

## Shelf

*Shelf* gets the design of its table. Chapter 54 creates it in a file and moves the books into it.

`shelf/schema.sql`:

<!-- include: code/53-sql-basics/shelf/schema.sql -->

- `IF NOT EXISTS`: chapter 54 runs this file every time *Shelf* opens its database. The first time it creates the table; after that, it does nothing.
- `COLLATE NOCASE` compares titles without the case of English letters, so "KINDRED" counts as "Kindred" for `UNIQUE`. `Library::add` still checks titles first, in any language, and gives the visitor a clear message (chapter 47). The table's rule catches whatever the code misses.
- `CHECK (...)` is a condition each row must meet. The year has the same range as `Book::FIRST_YEAR` and `Book::LAST_YEAR`.
- `isbn TEXT UNIQUE`: no two books share an ISBN, but many books may have none, because `NULL` is never equal to `NULL`.
- `STRICT` makes SQLite refuse a value of the wrong type. Without it, SQLite would store "last year" in an `INTEGER` column.

The rules at work, `code/53-sql-basics/shelf-schema.php`:

<!-- include: code/53-sql-basics/shelf-schema.php -->

Each refused `INSERT` names the rule it broke, and only the first book is stored.

## Exercises

The exercises are in `exercises/53-sql-basics/`. Check your work with:

```
composer exercise 53 beginner
```

1. **Beginner, `queries.php`:** three questions to a library, in SQL: one author's titles, the three newest books, and the books on the shelf.
2. **Intermediate, `changes.php`:** add a book, return a copy, and remove the books with no copy left.
3. **Advanced, `members.sql`:** a members table whose rules the database checks: unique names, two roles, a date of the right shape.

## Summary

- A relational database keeps data in tables of columns and rows, and answers questions in SQL.
- `SELECT ... FROM ... WHERE ... ORDER BY ... LIMIT` asks; `INSERT`, `UPDATE` and `DELETE` change. Always check the `WHERE`.
- `NULL` means "no value": compare it with `IS NULL`.
- Put the rules that must never break into the table: `NOT NULL`, `UNIQUE`, `CHECK` and `STRICT`.
- Never write values into SQL text yourself; chapter 54 shows how.

## Further reading

- SQLite documentation, SQL as understood by SQLite: https://www.sqlite.org/lang.html
- SQLite documentation, STRICT tables: https://www.sqlite.org/stricttables.html
- SQLite documentation, appropriate uses for SQLite: https://www.sqlite.org/whentouse.html
