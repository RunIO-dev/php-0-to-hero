# 70. Pagination and filters

## Goals

- You will be able to answer a long list a page at a time, with `LIMIT` and `OFFSET`.
- You will be able to filter and sort a list by what the address asks, without putting the address into the SQL.
- You will be able to tell a program where the other pages are, with the `Link` header.
- You will be able to change an API without breaking the programs that already use it.

## Before you start

- Chapter 67: `GET /api/books`.
- Chapter 54: prepared statements and placeholders.
- Chapter 53: `ORDER BY` and `LIMIT`.

## The idea

*Shelf*'s `/api/books` answers every book at once. With seven books, that is fine. With seventy thousand, every request reads the whole table, builds a huge JSON answer, and sends megabytes that the phone app shows twenty of. And anyone can copy the whole catalogue, or keep the server busy, with one request.

So a list comes **a page at a time**:

- `?page=2&per_page=20`: the program says which page, and how long. The server sets a default, 20, and a maximum, 100, so nobody can ask for everything.
- In SQL: `LIMIT 20 OFFSET 20`. `OFFSET` skips the earlier pages: `(page − 1) × per_page` rows.
- The order must be fixed, `ORDER BY title`. Without it, the database may return the rows in any order, and a book can appear on two pages, or on none.

The program also needs to know where the other pages are. The **`Link` header** (RFC 8288) names them, with their relation:

```
Link: </api/books?page=1&per_page=20>; rel="first", </api/books?page=3&per_page=20>; rel="next", ...
```

`first`, `prev`, `next` and `last`. A program follows `next` until there is none. Some APIs also send how many items there are in total, in a header such as `X-Total-Count`, or wrap the list in an object, `{"data": [...], "total": 70}`.

**Filters** narrow the list: `?author=butler`, `?available=true`. Like everything in the address, they are input: each is checked, and goes into the SQL through a placeholder.

**Sorting** is harder: `ORDER BY` takes a column name, and a placeholder can only hold a value, never a name. So the address *chooses* from a list of sorts that the code writes itself: `?sort=year` picks `'year' => 'year, title'`, and anything that isn't in the list is refused.

## In code

The example has a database in memory with seven books, and the answers of chapter 68, `code/70-pagination/catalog.php`:

<!-- include: code/70-pagination/catalog.php -->

The list, `code/70-pagination/books.php`:

<!-- include: code/70-pagination/books.php -->

- `number()` accepts only digits, and `page` and `per_page` are checked against their limits: `400` otherwise.
- `SORTS` is the list of sorts. `SORTS[$sort]` puts SQL from the code into the statement, never text from the address.
- `(:author = '' OR author LIKE :pattern)`: when no author is asked for, the first part is true, and the filter switches itself off. So the statement is the same for every request, and has no SQL put together from pieces.
- `LIKE '%butler%'` finds part of a name. `addcslashes()` makes a `%` or `_` that someone types count as a letter, not as a wildcard; `ESCAPE '\'` tells SQLite which character escapes.
- `LIMIT` and `OFFSET` take numbers: `bindValue()` with `PDO::PARAM_INT` binds them as such.
- `http_build_query()` writes the address of each link, with the filter and sort kept.

The requests:

<!-- include: code/70-pagination/books.http requests 1, 3, 5, 7 -->

## Common mistakes

### The sort order from the address

<!-- include: code/70-pagination/sort.bad.php -->

✗ `?sort=year` works. So does `?sort=(SELECT password_hash FROM members LIMIT 1)`, or worse: whatever is in the address is SQL now. A placeholder can't help, because `ORDER BY :sort` sorts by a fixed text, the same for every row.

**Fix:** a list of allowed sorts, written in the code, as `SORTS` in `books.php`.

### Everything at once

<!-- include: code/70-pagination/all.bad.php -->

✗ Every request reads and sends the whole table. A program that only shows the first ten waits for all of them, and the server's memory grows with the library.

**Fix:** a page at a time, with a default and a maximum for `per_page`.

### Changing what programs already read

Chapter 67's `/api/books` answers a list. Turning it into `{"data": [...]}` now would break every program that reads it as a list: they would stop working on the day of the update, without having changed anything.

**Fix:** add, don't change. *Shelf* keeps the list, and puts the pages into a header, which old programs ignore. When a change can't be avoided, the new form gets a new address, such as `/api/v2/books`, and the old one keeps working for a while.

## Principle check

**Secure by default:** every list has a maximum page size; filters go through placeholders; sorts come from a list. **KISS:** `LIMIT`, `OFFSET` and one header. **DRY:** one statement for every filter, and the same query object for the page and the count.

## Security

- **Limits protect the server.** A maximum `per_page`, and a maximum `page` too on very large tables: `OFFSET 1000000` makes the database read and skip a million rows.
- **Lists show what exists.** A filter by member, or a count, can tell a stranger things a single page wouldn't. The rules of chapter 63 hold for every list: only what the caller may see, filtered in the query.
- **Very long lists** are often paged by a **cursor** instead: "the 20 books after *Kindred*" (`WHERE title > :after`), which stays fast at any depth, and doesn't skip or repeat books when one is added meanwhile.

## Shelf

`/api/books` comes a page at a time, and can be filtered by author and by whether a copy is on the shelf.

**The query.** What a list is filtered by, `shelf/src/BookQuery.php`:

<!-- include: code/70-pagination/shelf/src/BookQuery.php -->

**The repositories** get `page()` and `count()`, `shelf/src/BookRepository.php`:

<!-- include: code/70-pagination/shelf/src/BookRepository.php -->

`shelf/src/PdoBookRepository.php`:

<!-- include: code/70-pagination/shelf/src/PdoBookRepository.php -->

SQLite's `LIKE` ignores case only for English letters; `BookQuery::matches()`, used by the repository in memory, ignores it for all. The contract tests use English names, which both find the same way.

The repository for the tests, `shelf/tests/InMemoryBookRepository.php`:

<!-- include: code/70-pagination/shelf/tests/InMemoryBookRepository.php -->

**The library** turns a page number into an offset, and counts the pages, `shelf/src/Library.php`:

<!-- include: code/70-pagination/shelf/src/Library.php -->

**The route.** `apiBooks()` reads `page`, `per_page`, `author` and `available`, answers `400` for anything wrong, and sends the `Link` header. The body is the list of chapter 67, so nothing breaks. `shelf/src/Web/App.php`:

<!-- include: code/70-pagination/shelf/src/Web/App.php lines 247-287 -->

**The tests.** Both repositories must page and filter alike, `shelf/tests/BookRepositoryContract.php`:

<!-- include: code/70-pagination/shelf/tests/BookRepositoryContract.php -->

`shelf/tests/LibraryTest.php`:

<!-- include: code/70-pagination/shelf/tests/LibraryTest.php -->

`shelf/tests/Web/AppTest.php`:

<!-- include: code/70-pagination/shelf/tests/Web/AppTest.php -->

`shelf/web.http` asks for the second page of two, for Octavia E. Butler's available books, and for a page too long:

<!-- include: code/70-pagination/shelf/web.http -->

## Exercises

The exercises are in `exercises/70-pagination/`. Check your work with:

```
composer exercise 70 beginner
```

1. **Beginner, `pages.php`:** how many pages, and the offset of a page.
2. **Intermediate, `links.php`:** the `Link` header for any page, with the filters kept.
3. **Advanced, `members.php`:** members a page at a time, filtered by role, sorted from a list, with `X-Total-Count`.

## Summary

- Answer long lists a page at a time: `LIMIT` and `OFFSET`, with a fixed order, a default and a maximum size.
- The `Link` header names the `first`, `prev`, `next` and `last` pages.
- Filters go through placeholders; an empty filter can switch itself off in the same statement.
- Sorts come from a list in the code, never from the address.
- Add to an API; don't change what programs already read.

## Further reading

- RFC 8288, Web Linking: https://www.rfc-editor.org/rfc/rfc8288
- SQLite documentation, `LIMIT` and `OFFSET`: https://www.sqlite.org/lang_select.html#the_limit_clause
- SQLite documentation, the `LIKE` operator: https://www.sqlite.org/lang_expr.html#the_like_glob_regexp_match_and_extract_operators
- OWASP, SQL Injection Prevention Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html
