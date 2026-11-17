# 54. PDO and prepared statements

## Goals

- You will be able to connect to a database with PDO, and read rows in PHP.
- You will be able to send values with prepared statements, the only way we write queries.
- You will be able to explain SQL injection, and why placeholders stop it.
- You will be able to move *Shelf*'s books into SQLite, through the `BookRepository` interface.

## Before you start

- Chapter 53: tables and SQL.
- Chapter 33: interfaces, and *Shelf*'s `BookRepository`.
- Chapter 18: exceptions.

## The idea

**PDO** (PHP Data Objects) is the part of PHP that talks to databases. It works the same way with SQLite, MariaDB, MySQL, PostgreSQL and others: only the first line, the connection, changes. The SQL may still differ a little from one database to another.

A connection starts with a **DSN** (data source name), a short text that says which database and where: `sqlite:var/shelf.sqlite` is a SQLite file, `sqlite::memory:` a database in memory, and `mysql:host=db;dbname=shelf` a MariaDB or MySQL server (chapter 59).

The important part of this chapter is how values get into SQL. In chapter 53, a quote in "Midnight's Children" broke an `INSERT`. Now imagine that the text comes from a visitor, through a form. If the program pastes it into the SQL, the visitor writes part of your SQL. That is **SQL injection**, one of the most common and most damaging attacks on websites: it can read every row, change any data, or delete a table.

A **prepared statement** stops it completely. The SQL is sent to the database first, with **placeholders** where values go: `WHERE author = :author`. The database reads and plans that SQL. Then the values are sent on their own. They are never read as SQL, so a quote in them is only a quote. This is the only way this course writes a query with a value in it, and `tools/verify` refuses SQL built from variables.

## In code

### Connecting and reading

<!-- include: code/54-pdo/connect.php -->

- `new PDO($dsn, options: [...])` connects. Two options, which *Shelf* sets too:
  - `ERRMODE_EXCEPTION`: every database error throws a `PDOException`. It is the default since PHP 8, and we write it anyway, so that the code says it.
  - `FETCH_ASSOC`: each row is an array with the column names as keys.
- `exec()` runs SQL that returns no rows, such as the `CREATE TABLE` in `library.sql`.
- `query()` runs SQL without values and returns a `PDOStatement`, the result. `foreach` goes through its rows.
- `fetch()` returns one row, and `false` when there is none. SQLite gives whole numbers back as PHP `int`.

### Prepared statements

<!-- include: code/54-pdo/prepared.php -->

- `prepare()` sends the SQL with a placeholder, `:author`, and returns the statement. `execute()` sends the values, as an array: `['author' => ...]`.
- One prepared statement can run many times with other values: here twice.
- The second "author" is an attack: `' OR '1'='1`. Pasted into the SQL, it would make the condition true for every row. As a value, it is a name that no book has, so nothing is found.
- `?` is a placeholder without a name. The values then go in an array in the same order. Named placeholders are easier to read once there are more than two or three.
- "Midnight's Children" needs no doubled quote: the value never becomes SQL text.
- `lastInsertId()` returns the `id` the database gave the new row, as text.

### Changing rows

<!-- include: code/54-pdo/change.php -->

- `rowCount()` after an `UPDATE` or `DELETE` says how many rows changed. Here it answers the question "was a copy lent?": `AND copies > 0` makes the database refuse the last copy twice.
- `fetchColumn()` returns the first column of the next row: handy for `COUNT(*)`.

## Common mistakes

### Pasting a value into the SQL

<!-- include: code/54-pdo/injection.bad.php -->

✗ With `?author=' OR '1'='1` in the address, the query becomes `SELECT title FROM books WHERE author = '' OR '1'='1'`, and every title comes back. Other values can read other tables, or change data. Escaping the quotes by hand is not the fix: it is easy to forget once, and easy to get wrong for one database.

**Fix:** a placeholder, as in `prepared.php`. `tools/verify` refuses SQL joined with variables in every file except `*.bad.php`.

### A placeholder in quotes

<!-- include: code/54-pdo/quoted.error.php -->

⚠ `':author'` in quotes is text, the word ":author", so the SQL has no placeholder, and `execute()` has a value with nowhere to go: `column index out of range`.

**Fix:** no quotes around placeholders: `WHERE author = :author`. The database knows the value is text.

### A placeholder for a column name

<!-- include: code/54-pdo/order.php -->

`ORDER BY :column` sorts by the *value* "year", the same for every row, so the order isn't by year at all, and nothing warns you. Placeholders stand for values only, never for names of tables or columns, or for words such as `DESC`.

**Fix:** a fixed list of the sorts you offer, each with its whole SQL, and an exception for anything else. `match` makes that list short.

## Principle check

**Secure by default:** every value goes through a placeholder, from the first query on, and errors are exceptions, so a failed query can't pass unnoticed. **KISS:** PDO is built into PHP, with no library to add. **DRY:** *Shelf*'s rules use `BookRepository`, so moving to a database changes one class and two lines, not the rules.

## Security

- **SQL injection** is stopped by placeholders, and only by them. Checking or escaping input is not enough on its own.
- **The database file** is data: `var/shelf.sqlite` is outside `public/`, so no address can download it, and `shelf/.gitignore` leaves it out of git.
- **Error messages** can show parts of your SQL. A visitor should never see them: chapter 66 shows little and logs much.

## Shelf

*Shelf*'s books move from `books.json` into a SQLite database, `shelf/var/shelf.sqlite`. Nothing changes in `Library` or `App`: they use `BookRepository`, and the new class is one more `BookRepository` (chapter 33).

**Opening the database.** A function in `shelf/src/functions.php` opens the file, and makes it with its table the first time:

<!-- include: code/54-pdo/shelf/src/functions.php -->

- `mkdir(..., 0o700, true)` makes the `var/` folder, readable only by its owner.
- `schema.sql` (chapter 53) runs every time. `IF NOT EXISTS` makes that harmless once the table is there.

**The repository.** `shelf/src/PdoBookRepository.php`:

<!-- include: code/54-pdo/shelf/src/PdoBookRepository.php -->

- `all()` reads every book in the order they were added, as `books.json` kept them. `Book::fromArray()` checks each row, as it checked the file's items: data from the database is input too.
- `save()` keeps the interface's promise, "these are the books". `INSERT ... ON CONFLICT (title) DO UPDATE` adds a new title and updates one that is there; `excluded` is the row that couldn't be inserted. The values come from `Book::toArray()`, whose keys are the placeholders' names.
- `save()` still writes every book, as the file did. Chapter 55 changes the interface, so that adding a book writes one row.

**The command line.** `shelf.php` opens the database, and gets a new command, `import`, which copies `books.json` into it once. `import()` takes two `BookRepository` objects: it doesn't care that one is a file and the other a database.

From now on, the commands change the database, so the output below is a session: several commands, one after the other, each with what it printed. The book's checks run them in a copy of *Shelf*, from the list in `shelf/shelf.run`. The second `import` is refused, so it can't copy the books twice. `shelf/shelf.php`:

<!-- include: code/54-pdo/shelf/shelf.php -->

The front controller uses the database too, `shelf/public/index.php`:

<!-- include: code/54-pdo/shelf/public/index.php -->

**Git and Composer.** The database is data, made by *Shelf*: `shelf/.gitignore` leaves out `var/`:

<!-- include: code/54-pdo/shelf/.gitignore -->

`shelf/composer.json` says that *Shelf* needs PHP's SQLite driver for PDO, `ext-pdo_sqlite`, as it says it needs `ext-mbstring` (chapter 38):

<!-- include: code/54-pdo/shelf/composer.json -->

**The web pages.** A new database is empty, so `shelf/web.http` adds *Kindred* first, through the form:

<!-- include: code/54-pdo/shelf/web.http -->

Try it, from the course folder:

```
composer dump-autoload --working-dir=shelf
php shelf/shelf.php import
php shelf/shelf.php
docker compose up web
```

## Exercises

The exercises are in `exercises/54-pdo/`. Check your work with:

```
composer exercise 54 beginner
```

1. **Beginner, `find.php`:** the titles of one author, with a placeholder, safe from quotes and attacks.
2. **Intermediate, `add.php`:** add a book and return its new id.
3. **Advanced, `sorted.php`:** a list sorted the way the visitor asks, from a fixed list of sorts.

## Summary

- PDO talks to many databases; the DSN says which. Set `ERRMODE_EXCEPTION` and `FETCH_ASSOC`.
- `prepare()` with placeholders, then `execute()` with the values: the only way to put a value into a query.
- SQL injection is a visitor writing part of your SQL; placeholders stop it.
- Placeholders stand for values, never for names or keywords: choose those from a fixed list.
- *Shelf*'s books are in SQLite now, behind the same `BookRepository` interface.

## Further reading

- PHP manual, PDO: https://www.php.net/manual/en/book.pdo.php
- PHP manual, prepared statements: https://www.php.net/manual/en/pdo.prepared-statements.php
- OWASP, SQL Injection Prevention Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html
- SQLite documentation, UPSERT: https://www.sqlite.org/lang_upsert.html
