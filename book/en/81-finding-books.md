# 81. Finding books

## Goals

- You will be able to build a search form whose every search is an address, to keep, share or go back to.
- You will be able to search several columns with one box, with the visitor's text kept safe from `LIKE`'s wildcards.
- You will be able to read a page's filters, order and number from the address with `#[MapQueryParameter]`, and refuse what doesn't fit.
- You will be able to page a list with a pager that stays short, and an order that never repeats or skips a book.
- You will be able to tell with `EXPLAIN QUERY PLAN` what an index does for a query, and what it doesn't.

## Before you start

- Chapter 70: pages, filters and sorts for the API.
- Chapter 73: routes, controllers and Twig.
- Chapter 74: Doctrine's query builder and migrations.
- Chapter 80: *Shelf*'s components.

## The idea

A library with four books needs no search. A library with four hundred is useless without one. Chapter 70 gave the API pages and filters, for programs. This chapter gives them to people: a search box, a choice of order, "only on the shelf", and pages with numbers under the list. To have something to search, *Shelf* also gets a catalogue of fifty classic books.

**A search is an address.** The form sends `GET`, so its fields end up in the query string: `/?q=hesse&sort=newest&page=2`. Such an address can be bookmarked, shared, reloaded and reached with the back button, and changes nothing on the server. `POST` is for forms that change something (chapter 47).

**One box, several columns.** Visitors don't think in columns. They type "Butler", "kindred" or the ISBN from a book's back. The search looks in the title and the author, in any case, and in the ISBN when the text looks like one: four to thirteen digits, with or without dashes. The visitor's text goes into `LIKE` through a placeholder, as always. But `LIKE` has wildcards of its own, `%` and `_`, which a placeholder doesn't stop: they are escaped, so a `%` that a visitor types is a letter.

**Values from the address, checked.** `#[MapQueryParameter]` (chapter 73) reads a parameter into a typed argument. Symfony checks it against the type: `int` must be a whole number, `bool` a yes or no, and a **backed enum**'s parameter must be one of its values. With `validationFailedStatusCode: 400`, anything else is a "400 Bad Request", not a list that quietly ignores it. A page after the last is a 404, as for any address with nothing behind it.

**An order with a last word.** `?sort=newest` sorts by year. Three books from 1987 are equal by year, and SQL may return equals in any order, which can be different for each query. Then page 1 and page 2 might show the same book, and another one never. So every order ends with the title, which is unique: the order is then complete, and the pages split the list the same way every time.

**Indexes, honestly.** An index keeps a column in order, so a sort by that column, or a search for one exact value, doesn't read the whole table. A search for *part* of a name, `LIKE '%morrison%'`, has to look at every row whatever indexes there are, because the part can be anywhere in the name. For a library of thousands of books that takes a few milliseconds. For millions, a database's **full-text search** builds an index of the words (see Further reading). `EXPLAIN QUERY PLAN` shows which case a query is.

## In code

The examples share a small catalogue in memory, `code/81-finding-books/catalogue.php`:

<!-- include: code/81-finding-books/catalogue.php -->

### One search box

<!-- include: code/81-finding-books/search.php -->

<!-- include: code/81-finding-books/search.out -->

- "BUTLER" finds Octavia E. Butler's books in any case, and "wood" a word inside a title.
- "978-0-8070" is an ISBN with dashes; "0802142443" is part of one without them.
- `%` and `o_e` find nothing, as they should. Unescaped, `%` would match every book, and `o_e` would find "Beloved" (o, any letter, e).

### Sorts as an enum

<!-- include: code/81-finding-books/sorts.php -->

<!-- include: code/81-finding-books/sorts.out -->

- `tryFrom()` returns `null` for a value that isn't a case: the address picks from the enum, and never writes SQL.
- The three books from 1987 come in title order, so page 1 ends with Beloved and page 2 starts with Dawn, every time.
- `array_filter` leaves out the defaults, so the links stay short.

### What an index changes

<!-- include: code/81-finding-books/explain.php -->

<!-- include: code/81-finding-books/explain.out -->

- `SCAN books` reads every row. `USE TEMP B-TREE` sorts the result for this query alone.
- With the indexes, the sorts walk the index (`USING INDEX`). SQLite then only orders the books of the same author or year among themselves (`LAST TERM`): a few books, not the whole table.
- One exact author is a `SEARCH`, straight to its rows. Part of a name is still a `SCAN`: the index can't help.

## Common mistakes

### A search form that sends POST

`<form method="post">` for a search: the address stays `/`, so the search can't be bookmarked or shared, reloading asks to send the form again, and the back button shows a warning instead of the last search.

**Fix:** `method="get"` for every form that only reads. `POST` is for forms that change something.

### The visitor's wildcards

```php
$find->execute(['pattern' => "%$text%"]); // ✗ a % or _ in $text is a wildcard
```

The placeholder keeps the text out of the SQL, but `LIKE` still reads `%` and `_` in it. A search for `%` finds every book; on a big table, a pattern such as `%_%_%_%` can make a search slow on purpose.

**Fix:** escape them, `addcslashes($text, '%_\\')`, and tell `LIKE` the escape letter: `LIKE :pattern ESCAPE '\'`.

### An order without a last word

`ORDER BY year` alone: books of the same year can come in a different order in each query, so a book may show up on two pages and another on none.

**Fix:** end every order with a unique column: `ORDER BY year DESC, title`.

### Ignoring what the address got wrong

`?sort=price` shows the list sorted by title, and `?page=999` an empty page: the visitor can't tell their link is broken.

**Fix:** refuse it: 400 for a value that isn't allowed, 404 for a page that doesn't exist.

## Principle check

**KISS:** `LIKE` and two indexes, no search server. That is enough for a library, and the chapter says where it stops. **DRY:** the orders are one enum, used by the form, the address, the database and the list in memory. **Secure by default:** every value from the address is typed and checked, the search goes through a placeholder with its wildcards escaped, and it is cut at 100 characters.

## Security

- **A search reads only what a visitor may see.** *Shelf*'s list is public, so its search is too. A search of members or loans would filter by who is asking in the query, as chapter 63's rules say for every list.
- **Limits.** The search is cut at 100 characters, and a page holds 12 books. A visitor can't make the server read and send the whole catalogue with one request.
- **Escaped on the page.** The search comes back in the box and in "13 books for …", escaped by Twig like everything else (chapter 73).

## Shelf

*Shelf*'s list gets a search box, a choice of order, "Only on the shelf", and pages of twelve books.

### Orders

`shelf/src/BookSort.php`:

<!-- include: code/81-finding-books/shelf/src/BookSort.php -->

`compare()` gives the same order to the list in memory that the tests use, so both repositories sort alike, and the contract tests (chapter 60) check it.

### The query

`BookQuery` gets the search text and the order, `shelf/src/BookQuery.php`:

<!-- include: code/81-finding-books/shelf/src/BookQuery.php -->

The database does the same in SQL, `shelf/src/Doctrine/DoctrineBookRepository.php`:

<!-- include: code/81-finding-books/shelf/src/Doctrine/DoctrineBookRepository.php -->

`Library::page()` also says how many books were found in all, `shelf/src/Library.php`:

<!-- include: code/81-finding-books/shelf/src/Library.php -->

### The page

The controller reads the address, `shelf/src/Controller/BookController.php`:

<!-- include: code/81-finding-books/shelf/src/Controller/BookController.php -->

Which numbers the pager shows, `shelf/src/Web/Pager.php`:

<!-- include: code/81-finding-books/shelf/src/Web/Pager.php -->

The list, with the form and the count, `shelf/templates/list.html.twig`:

<!-- include: code/81-finding-books/shelf/templates/list.html.twig -->

`role="search"` makes the form a landmark, and `role="status"` has screen readers announce the count after a search. The pager, `shelf/templates/components/_pager.html.twig`:

<!-- include: code/81-finding-books/shelf/templates/components/_pager.html.twig -->

The pager is a `nav` with its own name, and the current page is marked with `aria-current`, as the menu is (chapter 80). A 400 gets a page of its own, `shelf/templates/unclear.html.twig`, which `ErrorPages` shows:

<!-- include: code/81-finding-books/shelf/src/EventSubscriber/ErrorPages.php -->

### Indexes

`Book` names its indexes, `shelf/src/Book.php`:

<!-- include: code/81-finding-books/shelf/src/Book.php -->

`doctrine:migrations:diff` writes the migration. For SQLite, it copied the whole `books` table, dropped it and made it again, which SQLite needs for most changes to a table. For an index it doesn't: always read what `diff` wrote before you run it. `shelf/migrations/Version20261118084441.php`:

<!-- include: code/81-finding-books/shelf/migrations/Version20261118084441.php -->

### The catalogue

`shelf/catalogue.json` holds 53 classic books, from *Don Quixote* to *Americanah*, without ISBNs. `shelf:import` (chapter 74) fills an empty database once. `shelf:catalogue` may run any number of times: it adds only the titles that aren't there yet, and leaves the others as they are. So it can run after every deployment, `shelf/src/Command/CatalogueCommand.php`:

<!-- include: code/81-finding-books/shelf/src/Command/CatalogueCommand.php -->

```
php shelf/bin/console doctrine:migrations:migrate --no-interaction
php shelf/bin/console shelf:catalogue shelf/catalogue.json
```

### Tests

Both repositories search and sort alike, `shelf/tests/BookRepositoryContract.php`:

<!-- include: code/81-finding-books/shelf/tests/BookRepositoryContract.php -->

The pages, as a browser uses them, `shelf/tests/Web/PagesTest.php`:

<!-- include: code/81-finding-books/shelf/tests/Web/PagesTest.php -->

The pager (`tests/Web/PagerTest.php`), the catalogue (`tests/Command/CommandsTest.php`) and the accessibility of a search (`tests/Web/AccessibilityTest.php`) are tested too, in the chapter's snapshot.

### Try it

`shelf/web.http` now fills the catalogue, then searches, sorts and pages, and asks for an order and a page that don't exist. Its answers, a 400 and a 404 among them, are in `shelf/web.out`. Or open http://localhost:8000 after `docker compose up web`, and search.

## Exercises

The exercises are in `exercises/81-finding-books/`. Check your work with:

```
composer exercise 81 beginner
```

1. **Beginner, `like.php`:** the pattern for a `LIKE` search, with the visitor's wildcards escaped.
2. **Intermediate, `Pager.php`:** the page numbers under a list, with gaps, and as many neighbours as asked for.
3. **Advanced, `BookSearch.php`:** a list's search, order and page, read from the query string by hand and checked, with the links to other pages.

## Summary

- A search form sends `GET`: every search is an address.
- One box searches title, author and ISBN. The visitor's `%` and `_` are escaped, and `LIKE` is told the escape letter.
- `#[MapQueryParameter]` reads typed values, backed enums included. With `validationFailedStatusCode: 400`, a wrong value is refused.
- Every order ends with a unique column, so pages never repeat or skip a book. The pager shows the ends, the neighbours and gaps.
- An index helps sorting and exact matches, not a search for part of a word. `EXPLAIN QUERY PLAN` shows which.
- `shelf:catalogue` adds only what is new, so it is safe to run again.

## Further reading

- Symfony documentation, mapping query parameters: https://symfony.com/doc/7.4/controller.html#mapping-query-parameters-individually
- PHP manual, backed enumerations: https://www.php.net/manual/en/language.enumerations.backed.php
- SQLite documentation, the LIKE operator: https://www.sqlite.org/lang_expr.html#like
- SQLite documentation, EXPLAIN QUERY PLAN: https://www.sqlite.org/eqp.html
- SQLite documentation, full-text search (FTS5): https://www.sqlite.org/fts5.html
- MDN Web Docs, the search landmark role: https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/Reference/Roles/search_role
