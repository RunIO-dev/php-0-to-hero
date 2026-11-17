# 58. Migrations: changing the schema safely

## Goals

- You will be able to change tables that already hold data, with `ALTER TABLE`, without losing it.
- You will be able to write migrations: numbered files that each change the database once.
- You will be able to explain why a migration that has run is never edited.
- You will be able to see whether a query uses an index, and add one.

## Before you start

- Chapter 56: *Shelf*'s three tables.
- Chapter 57: transactions, and *Shelf*'s `inTransaction()`.
- Chapter 43: Git, which keeps the history of every file.

## The idea

The design of a database's tables, their columns, types and rules, is its **schema**. Until now, *Shelf*'s schema was one file, `schema.sql`, with `CREATE TABLE IF NOT EXISTS`. That only works for new databases. In chapter 56, the members and loans tables arrived, and the advice was: delete your database and import again. With real data, you can't do that.

`IF NOT EXISTS` checks only whether a table with that name exists, not whether it looks like the file says. A column added to the file never reaches a database that has the table.

A **migration** is one change to the schema, written as a file, which runs once in every database:

- Migrations are numbered, `001-books.sql`, `002-members-and-loans.sql`, and run in that order. A new database runs them all; one from last month runs only the new ones.
- The database remembers which migrations it has run, in a table of its own.
- Each runs in a transaction, with the note that it ran, so it runs completely or not at all.
- A migration that has run is never changed. Databases that ran the old text would differ from new ones, and nothing would show it. To change something again, write the next migration.

The files go into Git with the code, so the code and the schema it needs always travel together. Every framework has migrations; Symfony's are in Part 11. *Shelf*'s are about 70 lines, so you can see the whole idea.

## In code

The examples use the helpers of chapter 53, which now print "Done" for a change to the tables, and a library with data, `code/58-migrations/library.sql`:

<!-- include: code/58-migrations/library.sql -->

### Changing a table that holds data

<!-- include: code/58-migrations/alter.php -->

- `ALTER TABLE ... ADD COLUMN` adds a column to every row. The rows get `NULL`, or the column's `DEFAULT`; a `NOT NULL` column needs a default, so the existing rows have a value.
- An `UPDATE` right after it fills the new column from data that is there: here, the day each book was lent, from its due date. SQLite's `date(due, '-14 days')` counts back. Filling data in a migration is called a **backfill**.
- `RENAME COLUMN` keeps the data, and SQLite updates the queries stored in the schema, such as an index on that column. Your PHP code, though, still says `name`: change the code and the schema together, in one commit.
- What `ALTER TABLE` can't do in SQLite, such as adding a `CHECK` to a column, needs a rebuilt table: the advanced exercise does it.

### Indexes

<!-- include: code/58-migrations/query-plan.php -->

- `EXPLAIN QUERY PLAN` shows how SQLite will find the rows, without running the query.
- `SCAN loans` reads every row of the table. With a few rows, that is fast; with a hundred thousand loans, every page that shows a member's loans reads them all.
- An **index** is a sorted list of one column's values, each with its row, like the index at the back of a book. `SEARCH loans USING INDEX` jumps to the matching rows.
- An index costs a little space, and a little time on every `INSERT`, `UPDATE` and `DELETE`. Add one for the columns that queries search by. SQLite makes one for each `PRIMARY KEY` and `UNIQUE` column, but not for a foreign key.

## Common mistakes

### Editing the CREATE TABLE

<!-- include: code/58-migrations/if-not-exists.php -->

The file says `books` has a `language` column. The database has a `books` table already, so `IF NOT EXISTS` does nothing, and the code that reads `language` fails, but only on databases that existed before the change: on the developer's new database, everything works.

**Fix:** a new migration with `ALTER TABLE books ADD COLUMN language ...`.

### Editing a migration that has run

You notice a mistake in `002-members-and-loans.sql`, after it ran on your computer and on the server, and you correct the file. New databases now get the corrected table; yours and the server's keep the old one, and nothing tells you.

**Fix:** leave the file as it is, and write `004-...` with the correction. *Shelf*'s `Migrations` stores a hash of each file it runs, and refuses to go on when one has changed: `migrations/002-members-and-loans.sql was changed after it ran`.

### Dropping data

<!-- include: code/58-migrations/drop.php -->

`DROP COLUMN` and `DROP TABLE` delete the data with the column or table. The migration is in Git and can be undone; the data can't.

**Fix:** keep a backup before a migration that drops anything (Part 12 sets up backups). Split risky changes in two: first stop using a column, in one release; drop it in a later one, when you are sure.

## Principle check

**KISS:** migrations are plain `.sql` files, and the runner is one small class with a test. **Secure by default:** each migration runs in a transaction, and a changed migration stops everything. **YAGNI:** there are no "down" migrations that undo a change; restoring a backup is the honest way back, and a new migration is the way forward.

## Security

- A migration runs with rights to change tables. In chapter 59, the application's database user gets no such rights; only migrations run with them.
- Never put secrets or personal data in a migration: the files are in Git, and every copy of the repository has them.
- A migration that rebuilds a table must bring every rule back: a forgotten `UNIQUE` or `CHECK` lets bad data in, quietly.

## Shelf

*Shelf*'s schema becomes three migrations, and its database remembers which ones it has run.

**The migrations.** `schema.sql` is removed. Its two parts become the first two migrations, `shelf/migrations/001-books.sql`:

<!-- include: code/58-migrations/shelf/migrations/001-books.sql -->

`shelf/migrations/002-members-and-loans.sql`:

<!-- include: code/58-migrations/shelf/migrations/002-members-and-loans.sql -->

They keep `IF NOT EXISTS`, so a database made by `schema.sql` in chapters 54 to 57 is taken over as it is: the first two migrations change nothing there, and are noted as run. New migrations don't need it.

The first real change adds the indexes for a member's loans and a book's loans, which `PdoLoanRepository` searches by. `shelf/migrations/003-loan-indexes.sql`:

<!-- include: code/58-migrations/shelf/migrations/003-loan-indexes.sql -->

**The runner.** `shelf/src/Migrations.php`:

<!-- include: code/58-migrations/shelf/src/Migrations.php -->

- `apply()` makes its own table, `migrations`, if it isn't there, then runs every file that has no row in it, in order of name.
- Each file runs in `inTransaction()` (chapter 57), together with its row. A failing migration leaves no table half made and no note that it ran; the next try starts it again.
- The hash is SHA-256 of the file's text, with Windows line endings made Unix ones, so a Git setting can't change it.
- A file name without a number is refused, so a file can't run in an order nobody chose.
- `ran()` lists what has run.

`openDatabase()` runs the migrations that are new, every time *Shelf* opens its database. For one computer, that is the simplest way. When *Shelf* runs on servers, in Part 12, migrations run once, as a step of each release, instead. `shelf/src/functions.php`:

<!-- include: code/58-migrations/shelf/src/functions.php -->

**The tests** use migrations of their own, in a temporary folder. They check the order, that each runs once, that a failing one changes nothing, and that a changed one is refused. `shelf/tests/MigrationsTest.php`:

<!-- include: code/58-migrations/shelf/tests/MigrationsTest.php -->

**The command line** gets `migrations`, which lists what this database has run. `shelf/shelf.php`:

<!-- include: code/58-migrations/shelf/shelf.php -->

Your database from chapter 57 keeps its data: the first time *Shelf* opens it, it runs the three migrations, and only the third changes anything.

## Exercises

The exercises are in `exercises/58-migrations/`. Check your work with:

```
composer exercise 58 beginner
```

1. **Beginner, `004-member-details.sql`:** add two columns to a table with members in it, one of them required.
2. **Intermediate, `pending.php`:** find the migrations that still have to run, and stop on a broken history.
3. **Advanced, `005-book-rules.sql`:** give a table with data new rules, by rebuilding it, so every row keeps its id.

## Summary

- The schema changes over time; a migration is one change, as a numbered file that runs once.
- The database records which migrations ran; each runs in a transaction.
- Never edit a migration that has run; write the next one.
- `ALTER TABLE` adds and renames columns with the data in place; dropping deletes data for good.
- `EXPLAIN QUERY PLAN` shows whether a query reads the whole table; an index on the searched column fixes that.

## Further reading

- SQLite documentation, ALTER TABLE (and the steps to rebuild a table): https://www.sqlite.org/lang_altertable.html
- SQLite documentation, CREATE INDEX: https://www.sqlite.org/lang_createindex.html
- SQLite documentation, EXPLAIN QUERY PLAN: https://www.sqlite.org/eqp.html
