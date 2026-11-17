# 74. Doctrine ORM and the PDO we wrote

## Goals

- You will be able to map a class to a table with attributes, and store and find its objects with Doctrine.
- You will be able to explain the unit of work and the identity map: what `persist()` and `flush()` do.
- You will be able to query with DQL and the query builder, with parameters, and load relations without 1 + N queries.
- You will be able to change the tables with Doctrine Migrations, and to choose between an ORM and plain PDO.

## Before you start

- Chapters 53 to 58: SQL, PDO, relations, transactions and migrations.
- Chapter 60: repositories, and contract tests.
- Chapter 73: the container and `services.yaml`.

## The idea

*Shelf*'s repositories turn rows into objects and back: `Book::fromArray($row)` after each `SELECT`, `$book->toArray()` before each `INSERT`, a `JOIN` written by hand to bring a loan's book and member with it. That code is the same for every class, and it grows with every column.

An **ORM** (object-relational mapper) does it for you. You describe once how a class fits a table, and the ORM writes the SQL. **Doctrine ORM** is the one Symfony uses. Its parts:

- **Entities.** A class whose objects live in a table, such as `Book`. Attributes on the class say which table, and on each property which column: `#[ORM\Column]`. An entity needs an **ID**, a column that tells its rows apart; the database gives it.
- **The entity manager.** `persist($book)` hands a new object to Doctrine. `flush()` writes everything that is new or changed, in one transaction. `find()` and `getRepository()` bring objects back.
- **The unit of work.** Doctrine remembers every object it loaded, and what it looked like. At `flush()`, it compares, and writes an `UPDATE` only for what changed. A changed object isn't saved by `$book->changeCopies(5)`, but by the next `flush()`.
- **The identity map.** One row is one object: finding Kindred twice gives the same object both times. A change is visible everywhere at once, before it is stored.
- **Relations.** `#[ORM\ManyToOne]` on `Loan::$book` makes a `book_id` column in the table, and a `Book` in PHP. Doctrine loads the book when it is first used, or in the same query when you ask with a `JOIN`.
- **DQL** (Doctrine Query Language) looks like SQL, but names classes and properties: `SELECT l FROM Shelf\Loan l JOIN l.book b WHERE b.title = :title`. Doctrine turns it into SQL for the database in use.
- **Doctrine Migrations** compares the entities with the database, and writes the migration that makes them match, as chapter 58's SQL files did by hand.

**An ORM or PDO?** An ORM saves the repetitive code of loading and storing objects, and of relations. It costs a layer to understand: what is loaded when, and when it is written. Plain SQL stays better for reports over many rows, for changing thousands of rows at once, and for anything the database does better than PHP. Doctrine allows both: DQL, and the connection's own SQL, in the same app.

## In code

The examples are a Composer project with two entities in `src/` (`code/74-doctrine/composer.json`). The book, `code/74-doctrine/src/Book.php`:

<!-- include: code/74-doctrine/src/Book.php -->

- `#[ORM\Entity]` makes it an entity, `#[ORM\Table]` names its table.
- Each `#[ORM\Column]` is a column; Doctrine reads its type from the property's type: `string` is text, `int` a number.
- `$id` is `null` until the database gives the book its number. `private(set)`: only the class, and Doctrine, can set it.

The loan, `code/74-doctrine/src/Loan.php`:

<!-- include: code/74-doctrine/src/Loan.php -->

`code/74-doctrine/entities.php` makes an entity manager on SQLite in memory, makes the tables from the entities, and can print each SQL statement Doctrine sends:

<!-- include: code/74-doctrine/entities.php -->

### Storing and finding

<!-- include: code/74-doctrine/persist.php -->

- `persist()` sends nothing to the database; `flush()` sends both `INSERT`s, with placeholders, in one transaction.
- After `flush()`, Kindred has its ID.
- `findOneBy(['title' => 'Sula'])` is a `SELECT` with a placeholder. The book comes back as a `Book`, made without calling its constructor.

### The unit of work

<!-- include: code/74-doctrine/changes.php -->

- Two finds of the same row give the same object (`true`).
- `lend()` changes the object. The table still says 2, until `flush()` sees the change and writes it.

### DQL and the query builder

<!-- include: code/74-doctrine/dql.php -->

- `SELECT l, b … JOIN l.book b` loads the loans and their books in one query: the SQL that Doctrine made from it is printed above the results.
- `:member` is a parameter, as in chapter 54: the value never becomes part of the query.
- The query builder makes the same kind of query from method calls, which helps when parts are added only sometimes, as filters are.

## Common mistakes

### 1 + N queries

<!-- include: code/74-doctrine/lazy.bad.php -->

✗ `findAll()` loads the loans; then each `$loan->book` loads its book with a query of its own. For 500 loans, 501 queries. Nothing looks wrong in the code, and every page gets slower as the library grows.

**Fix:** load what the page uses in the same query, `SELECT l, b FROM … JOIN l.book b`, as *Shelf*'s `DoctrineLoanRepository::forMember()` does. Symfony's profiler (`symfony/web-profiler-bundle`, for developers) counts the queries of each page.

### Forgetting flush()

`$book->changeCopies(5)` without `flush()`: the page shows 5, because the object says so, and the next request shows the old number. Nothing was written, and nothing said so.

**Fix:** a repository method that changes something ends with `flush()`, as *Shelf*'s `update()` does. Tests that clear the entity manager before they read (`$entities->clear()`) catch a missing `flush()`.

### Changing the tables by hand

A column added to the database, but not to the entity, or the other way round: the app fails on the server, or on a colleague's computer, which never got the change.

**Fix:** change the entity, run `doctrine:migrations:diff`, read the migration it wrote, and commit it with the code. `doctrine:schema:validate` says whether the entities and the database match.

## Principle check

**DRY:** the mapping is written once, on the class; Doctrine writes every `SELECT`, `INSERT` and `UPDATE` from it. **KISS:** for a report or a change of thousands of rows, plain SQL is still the simpler tool. **Secure by default:** DQL and `findBy()` use parameters, so a value can't become part of a query, unless you put it into the query's text yourself.

## Security

- **DQL can be injected too.** `createQuery("… WHERE l.member = '$member'")` has the same hole as chapter 54's SQL. Values go in through `setParameter()`, always.
- **Sorting from the address** (chapter 70) is still a list in the code: `orderBy()` takes a name, never a parameter.
- **Mass assignment.** Copying a whole request into an entity, field by field, lets a visitor set fields they shouldn't, such as `role`. Entities are made from checked values (chapter 75), never from the request directly.

## Shelf

`Book`, `Member` and `Loan` become entities, and the API's tokens get one, `ApiToken`. The repositories through PDO are replaced by repositories through Doctrine, behind the same interfaces, so `Library`, the controllers and the tests in memory don't change. Doctrine Migrations replaces chapter 58's SQL files, and `shelf.php` becomes Symfony commands.

```
cd shelf
composer config extra.symfony.docker false
composer require doctrine/doctrine-bundle doctrine/orm doctrine/doctrine-migrations-bundle
```

`extra.symfony.docker false` keeps the recipes from adding a `compose.yaml` for a database server: *Shelf* uses SQLite.

`shelf/composer.json`:

<!-- include: code/74-doctrine/shelf/composer.json -->

### The connection

`shelf/.env` names the database as Doctrine does, with a URL. `SHELF_DATABASE` and `SHELF_ENV` go: Symfony's `APP_ENV` says which environment it is:

<!-- include: code/74-doctrine/shelf/.env -->

`shelf/config/packages/doctrine.yaml`:

<!-- include: code/74-doctrine/shelf/config/packages/doctrine.yaml -->

`shelf/config/packages/doctrine_migrations.yaml`:

<!-- include: code/74-doctrine/shelf/config/packages/doctrine_migrations.yaml -->

`shelf/config/services.yaml` hands each repository's interface to its Doctrine class, and switches on SQLite's foreign keys:

<!-- include: code/74-doctrine/shelf/config/services.yaml -->

### The entities

`shelf/src/Book.php`:

<!-- include: code/74-doctrine/shelf/src/Book.php -->

- `collation NOCASE` keeps chapter 54's rule: one title in any case. It is SQLite's; on MariaDB, a column compares in any case by default.
- The ISBN is an `Isbn` object in PHP, and its digits in the table. A column type of our own does the turning, `shelf/src/Doctrine/IsbnType.php`:

<!-- include: code/74-doctrine/shelf/src/Doctrine/IsbnType.php -->

`shelf/src/Member.php`, with the password hash of chapter 61:

<!-- include: code/74-doctrine/shelf/src/Member.php -->

`shelf/src/Loan.php`:

<!-- include: code/74-doctrine/shelf/src/Loan.php -->

`shelf/src/ApiToken.php`:

<!-- include: code/74-doctrine/shelf/src/ApiToken.php -->

The classes were `readonly` before; an entity's ID is set by Doctrine after the class is made, so only the properties that never change are `readonly` now. The classes stay `final`: Doctrine loads a relation lazily with PHP's own lazy objects, which need no subclass.

### The repositories

`shelf/src/Doctrine/DoctrineBookRepository.php`:

<!-- include: code/74-doctrine/shelf/src/Doctrine/DoctrineBookRepository.php -->

`shelf/src/Doctrine/DoctrineMemberRepository.php`:

<!-- include: code/74-doctrine/shelf/src/Doctrine/DoctrineMemberRepository.php -->

`shelf/src/Doctrine/DoctrineLoanRepository.php`:

<!-- include: code/74-doctrine/shelf/src/Doctrine/DoctrineLoanRepository.php -->

- `wrapInTransaction()` is chapter 57's transaction: it flushes and commits at the end, or rolls back if anything throws.
- The `UPDATE … WHERE b.copies > 0` still takes the copy in one statement. It changes the table, not the object, so `refresh()` reads the book again.

`shelf/src/Doctrine/DoctrineTokenRepository.php`:

<!-- include: code/74-doctrine/shelf/src/Doctrine/DoctrineTokenRepository.php -->

`PdoBookRepository`, `PdoMemberRepository`, `PdoLoanRepository`, `PdoTokenRepository`, `Migrations`, `Config`, and `openDatabase()` with the other functions for PDO, are removed. `shelf/src/functions.php` keeps the rest:

<!-- include: code/74-doctrine/shelf/src/functions.php -->

### The migration

With the entities in place:

```
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

`diff` compared the entities with an empty database, and wrote `shelf/migrations/Version20261117210449.php`:

<!-- include: code/74-doctrine/shelf/migrations/Version20261117210449.php -->

`migrate` runs every migration the database hasn't run yet, and records it in a table of its own, as chapter 58's `Migrations` did. The SQL files of chapter 58 are removed: a new database gets its tables from this migration. A *Shelf* that has data in the old tables would need a migration that moves it; *Shelf*'s data comes from `books.json` and `members.json`, so a new database is simpler.

### The command line

`shelf.php` becomes Symfony commands in `src/Command/`. Each is a class with `#[AsCommand]`, and an `__invoke()` whose arguments are the command's arguments. The container hands each one what its constructor asks for. `shelf/src/Command/ImportCommand.php`:

<!-- include: code/74-doctrine/shelf/src/Command/ImportCommand.php -->

`shelf/src/Command/PasswordCommand.php`:

<!-- include: code/74-doctrine/shelf/src/Command/PasswordCommand.php -->

`shelf:books`, `shelf:lend` and `shelf:loans` are the same, for the commands of the library's desk (`src/Command/`). Adding and removing books is on the website and the API. `php bin/console list shelf` lists them.

### The tests

The contract tests of chapter 60 run against the Doctrine repositories. Each test gets a new database in memory, with the tables from the entities, `shelf/tests/Database/Entities.php`:

<!-- include: code/74-doctrine/shelf/tests/Database/Entities.php -->

`shelf/tests/Database/DoctrineBookRepositoryTest.php`:

<!-- include: code/74-doctrine/shelf/tests/Database/DoctrineBookRepositoryTest.php -->

One test of the contract changes, `shelf/tests/BookRepositoryContract.php`: it compared a book with a new one, which has no ID, and it expected a found book to keep its old copies until `update()`. With the identity map, the found book is the changed object itself.

<!-- include: code/74-doctrine/shelf/tests/BookRepositoryContract.php -->

`shelf/tests/Database/DoctrineLoanRepositoryTest.php`:

<!-- include: code/74-doctrine/shelf/tests/Database/DoctrineLoanRepositoryTest.php -->

`DoctrineMemberRepositoryTest` and `DoctrineTokenRepositoryTest` are chapter 60's tests for the Doctrine classes, in `shelf/tests/Database/`.

PHPStan learns that Doctrine writes the IDs, `shelf/phpstan.neon.dist`:

<!-- include: code/74-doctrine/shelf/phpstan.neon.dist -->

### The check

`shelf/web.http` sets the database up with the commands, and asks for every page as before. The answers are the same as in chapter 73:

<!-- include: code/74-doctrine/shelf/web.http -->

```
php bin/console doctrine:migrations:migrate
php bin/console shelf:import
php bin/console shelf:password Amira
php bin/console doctrine:schema:validate
```

## Exercises

The exercises are in `exercises/74-doctrine/`. Check your work with:

```
composer exercise 74 beginner
```

1. **Beginner, `Member.php`:** map a reading club's member to a table: an ID, a unique name, a date, and an email that may be missing.
2. **Intermediate, `loans.php`:** DQL for the desk: overdue loans with their books in one query, a member's titles, the busiest books.
3. **Advanced, `BookFinder.php`:** chapter 70's paged and filtered list, with the query builder.

## Summary

- An entity is a class with attributes that map it to a table; Doctrine writes the SQL.
- `persist()` hands a new object to Doctrine, `flush()` writes all changes in one transaction; one row is one object.
- DQL and the query builder name classes and properties; values go in as parameters.
- Load relations with a `JOIN` where they are used, or every row costs a query.
- Doctrine Migrations writes a migration from the entities; read it, then commit it.

## Further reading

- Doctrine ORM documentation, Getting started: https://www.doctrine-project.org/projects/doctrine-orm/en/current/tutorials/getting-started.html
- Doctrine ORM documentation, Doctrine Query Language: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/dql-doctrine-query-language.html
- Doctrine ORM documentation, the unit of work: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/unitofwork.html
- Symfony documentation, Databases and the Doctrine ORM: https://symfony.com/doc/7.4/doctrine.html
- Doctrine Migrations documentation: https://www.doctrine-project.org/projects/doctrine-migrations/en/3.9/index.html
