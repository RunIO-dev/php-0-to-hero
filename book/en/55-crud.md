# 55. CRUD: create, read, update, delete

## Goals

- You will be able to write the four basic operations on stored data with PDO.
- You will be able to turn rows into objects, and check them on the way in.
- You will be able to tell when a change found nothing to change.
- You will be able to give *Shelf* a page per book, where its copies change and it can be removed.

## Before you start

- Chapter 54: PDO and prepared statements.
- Chapter 51: CSRF tokens, which every form that changes data carries.

## The idea

Almost every program that stores data does four things with it: **c**reate, **r**ead, **u**pdate and **d**elete. Together they are called **CRUD**. In SQL, they are `INSERT`, `SELECT`, `UPDATE` and `DELETE`; on the web, a form that adds, a page that shows, a form that changes, and a button that removes.

In chapter 54, *Shelf*'s repository kept the shape of a file: `all()` read every book, and `save()` wrote every book back. A database doesn't need that. To lend one copy, it can change one row. This chapter gives the repository one method per operation, so each change writes only what it changes. It is faster, and two people changing two different books no longer overwrite each other.

Two rules come with CRUD on the web:

- **Reading uses GET, changing uses POST.** A GET request can be sent by a link, a browser that loads pages ahead, or a search engine following every link. If a GET could delete a book, any of them could, without a click.
- **A change can find nothing to change.** An `UPDATE` or `DELETE` whose `WHERE` matches no row is not an error for the database. Your program has to notice, with `rowCount()`, and say so.

## In code

### The four operations

<!-- include: code/55-crud/crud.php -->

- **Create:** `INSERT`, then `lastInsertId()` for the new row's `id`.
- **Read:** `SELECT ... WHERE id = :id`, and `fetch()` for the one row. After the delete, `fetch()` returns `false`: there is no row.
- **Update** and **delete** find their row with `WHERE`, and `rowCount()` says how many rows they changed.
- Each statement is prepared once and could run many times.

### From rows to objects

<!-- include: code/55-crud/objects.php -->

- The rest of the program works with `Book` objects, never with the arrays PDO returns. The array's shape is known in one place.
- `fromRow()` checks the row as it would check a form. A changed table, or a wrong column name in the `SELECT`, stops here with a clear message, instead of a `null` somewhere later.
- `findBook()` returns `null` for "no such book". The caller decides what that means: a 404 page, or a message.

PDO can also make objects for you, with `FETCH_CLASS`. It sets the properties *before* the constructor runs, so it doesn't work with `readonly` properties set in the constructor, and the constructor's checks run on empty values. Mapping by hand is a few lines, and you can read them.

## Common mistakes

### A change that found nothing

<!-- include: code/55-crud/missing.php -->

"Sulla" is a typing mistake. The `UPDATE` runs without an error and changes nothing. If the program then says "Saved", the person believes it.

**Fix:** check `rowCount()` after an `UPDATE` or `DELETE` that should change a row, and tell the person, or throw. *Shelf*'s repository throws `BookNotFound`.

### Deleting on a GET

<!-- include: code/55-crud/remove-link.bad.php -->

✗ A link such as `<a href="/remove-link.bad.php?id=3">` deletes a book when anything requests it: a browser that loads the next page early, a link checker, or a page on another site with `<img src="https://shelf.example/remove-link.bad.php?id=3">`. A GET also can't carry a CSRF token in a form.

**Fix:** a form with `method="post"` and the CSRF token, as *Shelf*'s book page has below. `App` answers a GET to `/remove` with `405`.

### Reading everything to change one thing

In chapter 54, lending a copy read every book and wrote every book back. With two librarians at work, both can read the list, each changes a different book, and the one who saves last writes the other's book back as it was: the first change is lost.

**Fix:** change only the row you mean, with `UPDATE ... WHERE`. Chapter 57 adds the second half of the answer, transactions.

## Principle check

**Secure by default:** changes need a POST with the CSRF token; the repository says when a book isn't there, instead of failing quietly. **KISS:** one method per operation, each one statement. **YAGNI:** the book page changes only what a librarian needs today, the number of copies; a full edit form can come when someone needs it.

## Security

- **Authorization:** for now, anyone who can open *Shelf* can remove a book. Chapter 63 lets only librarians do it. Until then, run *Shelf* only on your computer.
- **Deleting is final.** The book page says so next to the button. Many real systems mark a row as deleted instead, so it can come back; *Shelf* doesn't need that.
- **GET changes nothing,** and every POST is checked for the CSRF token (chapter 51).

## Shelf

Each book gets its own page, where the library can change how many copies it has, and remove the book.

**The repository, one method per operation.** `shelf/src/BookRepository.php`:

<!-- include: code/55-crud/shelf/src/BookRepository.php -->

`save()` is gone; `find()`, `add()`, `update()` and `remove()` take its place. The database version, `shelf/src/PdoBookRepository.php`:

<!-- include: code/55-crud/shelf/src/PdoBookRepository.php -->

- `find()` finds a title in any case, thanks to the column's `COLLATE NOCASE` (chapter 53).
- `update()` and `remove()` throw `BookNotFound` when `rowCount()` is 0.
- `JsonBookRepository` is removed: the only use of `books.json` left is the one-time import, which reads the file with `readList()` and `Book::fromArray()`.

The test version keeps its books in an array, `shelf/tests/InMemoryBookRepository.php`:

<!-- include: code/55-crud/shelf/tests/InMemoryBookRepository.php -->

**The rules.** A book can now change its number of copies, never below 0, `shelf/src/Book.php`:

<!-- include: code/55-crud/shelf/src/Book.php -->

`Library` finds, lends, changes copies and removes through the new methods. `changeCopies()` takes the number as typed into the form, and checks it with `filter_var()`, as `add()` checks the year (chapter 47). `shelf/src/Library.php`:

<!-- include: code/55-crud/shelf/src/Library.php -->

**The pages.** `App` gets three routes: `GET /book` shows a book, `POST /copies` changes its copies, and `POST /remove` removes it, with its cover. Both changes redirect after success (chapter 47), and set a message for the next page (chapter 50). `shelf/src/Web/App.php`:

<!-- include: code/55-crud/shelf/src/Web/App.php lines 30-56, 101-147 -->

`Covers` can now delete a cover, `shelf/src/Web/Covers.php`:

<!-- include: code/55-crud/shelf/src/Web/Covers.php -->

The book's page has two forms, each with the CSRF token and the book's title in hidden fields. `shelf/templates/book.php`:

<!-- include: code/55-crud/shelf/templates/book.php -->

In the list, each title links to its page, and one book is "1 book", not "1 books". `shelf/templates/list.php`:

<!-- include: code/55-crud/shelf/templates/list.php -->

The stylesheet gets a few rules for the book's page and the remove button. Copy it again, as in chapter 49: https://github.com/RunIO-dev/php-0-to-hero/blob/main/code/55-crud/shelf/public/style.css

**The command line** gets `remove`. `shelf/shelf.php`:

<!-- include: code/55-crud/shelf/shelf.php -->

**The tests.** `LibraryTest` checks the new rules, with a data provider for numbers of copies that are refused, `shelf/tests/LibraryTest.php`:

<!-- include: code/55-crud/shelf/tests/LibraryTest.php -->

`AppTest` checks the pages, including that a GET can't remove and a POST without the token is refused, `shelf/tests/Web/AppTest.php`:

<!-- include: code/55-crud/shelf/tests/Web/AppTest.php -->

`TemplatesTest` checks that the book page escapes what it shows, `shelf/tests/Web/TemplatesTest.php`:

<!-- include: code/55-crud/shelf/tests/Web/TemplatesTest.php -->

The whole round in the browser, `shelf/web.http`:

<!-- include: code/55-crud/shelf/web.http requests 14-16, 19-20 -->

## Exercises

The exercises are in `exercises/55-crud/`. Check your work with:

```
composer exercise 55 beginner
```

1. **Beginner, `rename.php`:** correct a title, and report when there was no such book.
2. **Intermediate, `members.php`:** a class with the four operations for members.
3. **Advanced, `books.php`:** books as objects, saved by one method that inserts new ones and updates stored ones.

## Summary

- CRUD is create, read, update, delete: `INSERT`, `SELECT`, `UPDATE`, `DELETE`.
- Change only the rows you mean, with `WHERE`; don't read and write everything.
- An `UPDATE` or `DELETE` that matched nothing is not an error: check `rowCount()`.
- Turn rows into objects in one place, and check them there.
- Reading is GET; changing is POST, with the CSRF token.

## Further reading

- PHP manual, `PDOStatement::rowCount`: https://www.php.net/manual/en/pdostatement.rowcount.php
- PHP manual, `PDOStatement::fetch`: https://www.php.net/manual/en/pdostatement.fetch.php
- MDN, HTTP request methods (safe methods): https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods
