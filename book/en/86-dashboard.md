# 86. The librarian's dashboard

## Goals

- You will be able to count in the database with `GROUP BY`, `COUNT`, `SUM` and `HAVING`, and bring back only the answers.
- You will be able to count the rows that have no match, with `LEFT JOIN` and `COUNT(column)`.
- You will be able to fill in the gaps a `GROUP BY` leaves, such as a month without loans.
- You will be able to write a report as a query class that changes nothing, apart from the entities, and add its optional filters with Doctrine's `QueryBuilder`.
- You will be able to change a table that has rows when the database can't simply add the column.

## Before you start

- Chapter 56: `JOIN`; chapter 57: transactions.
- Chapter 74: Doctrine, its entities and its connection; chapter 77: migrations.
- Chapter 81: a list a page at a time; chapter 83: the loans that stay as history.

## The idea

Since chapter 83, a loan that comes back stays, with its `returned_on`. That history can now answer the librarian's questions. Which books are overdue, and who has them? Which copies wait at the desk? Which books are borrowed most, and which never? How many loans came back on time? And what did one member borrow, or who had one book?

**Count where the rows are.** Loading every loan into PHP to count them works with forty loans, and not with forty thousand. SQL counts in the database: `GROUP BY` puts the rows into groups, one per book, and `COUNT(*)` counts each group. Only the answers come back, one row per book. `HAVING` keeps the groups whose count fits, as `WHERE` keeps rows: `WHERE` runs before the groups are made, `HAVING` after.

**What isn't there.** A book nobody borrowed has no loans, so a `JOIN` drops it. `LEFT JOIN` keeps it, with `NULL` for the loan's columns. Then `COUNT(*)` counts its one row as 1, but `COUNT(l.id)` counts only values that aren't `NULL`: 0. Months work the same way the other way round: a month without loans has no rows, so it has no group, and the database can't count it as 0. PHP walks the months, and fills in the zeros.

**A count with a condition.** `SUM(CASE WHEN returned_on > due THEN 1 ELSE 0 END)` turns each row into 1 or 0, and adds them up: how many came back late, in the same query as how many came back.

**A report is not an entity.** Entities are for changing things by their rules. A report only reads, many rows at once, and its answer is a number or a short list. So *Shelf* gets a query class, `Report\Statistics`: plain SQL through Doctrine's connection, every value a parameter, and small read-only objects for the answers. Its test runs the SQL on a real SQLite database: a report is its query, and a copy of it in PHP would test nothing.

**When was it lent?** Counting loans per month needs the day each loan began, which *Shelf* never stored. A new column, `lent_on`, `NOT NULL`. SQLite can't add a `NOT NULL` column to a table that has rows, so the migration builds the table again and copies the rows; for the loans that exist, it works the day out from the due date.

## In code

### Counting in the database

<!-- include: code/86-dashboard/count.php -->

<!-- include: code/86-dashboard/count.out -->

- `GROUP BY b.id, b.title`: one group per book. Every column in `SELECT` is either grouped or counted.
- `HAVING COUNT(*) > 1` keeps the groups; `WHERE COUNT(*) > 1` is an error, as the groups don't exist yet in `WHERE`.
- Beloved has no loans: `COUNT(*)` says 1, the row `LEFT JOIN` kept; `COUNT(l.id)` says 0.

### The months without loans

<!-- include: code/86-dashboard/months.php -->

<!-- include: code/86-dashboard/months.out -->

- `FETCH_KEY_PAIR`: the first column is the key, the second the value.
- February and April have no row; `?? 0` fills them in.

### A count with a condition

<!-- include: code/86-dashboard/late.php -->

<!-- include: code/86-dashboard/late.out -->

## Common mistakes

### Counting in a loop

<!-- include: code/86-dashboard/loop.bad.php -->

✗ One query per book, and every loan sent to PHP only to be counted: the page gets slower with every book.

**Fix:** one query, `SELECT b.title, COUNT(l.id) ... LEFT JOIN ... GROUP BY b.id, b.title`.

### COUNT(*) after a LEFT JOIN

`COUNT(*)` counts rows, and `LEFT JOIN` gives a book without loans one row: it is counted as borrowed once.

**Fix:** `COUNT(l.id)`, a column of the joined table, which is `NULL` where there was no match.

### The filter in WHERE

`LEFT JOIN loans l ON l.book_id = b.id WHERE l.lent_on >= ?` drops the books without loans again: their `lent_on` is `NULL`, and `NULL >= ?` is not true.

**Fix:** put the condition on the joined table into the `JOIN`: `LEFT JOIN loans l ON l.book_id = b.id AND l.lent_on >= ?`.

## Principle check

**Single responsibility:** `Library` changes things by the rules; `Report\Statistics` only reads. **KISS:** plain SQL for the counts, no second database or reporting tool. **Secure by default:** the dashboard and the history are for the staff only; the history's filters are parameters, added by the `QueryBuilder`.

## Security

- **The history says who read what.** It is personal data: only the staff see it (`#[IsGranted('ROLE_STAFF')]` on the whole controller), and a member who deletes their account takes their history with them (chapter 82).
- **Filters are values.** A book or a name typed into the history's form goes in as a parameter, never into the SQL. A query with optional parts is put together by Doctrine's `QueryBuilder` (`andWhere()`, `setParameter()`), not by joining strings.
- **A number is not a secret, a list may be.** The counts on the dashboard name no member; the overdue list does, and stays behind the login.

## Shelf

### The day a loan began

`lentOn`, with an index for the counts by date, `shelf/src/Loan.php`:

<!-- include: code/86-dashboard/shelf/src/Loan.php -->

Doctrine's SQL (`doctrine:schema:update --dump-sql`) builds the table again, and copies the rows without `lent_on`, which fails on the first row. The migration copies them with a `lent_on` worked out from the due date, `shelf/migrations/Version20261121090000.php`:

<!-- include: code/86-dashboard/shelf/migrations/Version20261121090000.php -->

### The report

`shelf/src/Report/Statistics.php`:

<!-- include: code/86-dashboard/shelf/src/Report/Statistics.php -->

Its answers are small read-only classes: `Overview`, `Tally` (a label and a number), `Returns` and `PastLoan`, in `shelf/src/Report/`. `Returns` works out the percent on time, `shelf/src/Report/Returns.php`:

<!-- include: code/86-dashboard/shelf/src/Report/Returns.php -->

`Library::overdueLoans()` gives the loans past their due date, from the loans that are out.

### The pages

`shelf/src/Controller/DashboardController.php`:

<!-- include: code/86-dashboard/shelf/src/Controller/DashboardController.php -->

The dashboard, `shelf/templates/dashboard.html.twig`. The loans per month are a table with a `<meter>` in each row: a chart that a screen reader reads as a table, without JavaScript or inline styles, which the CSP refuses.

<!-- include: code/86-dashboard/shelf/templates/dashboard.html.twig -->

The history, `shelf/templates/history.html.twig`, uses chapter 81's pager, which now takes the route of its list (`shelf/templates/components/_pager.html.twig`, in the snapshot). Its variables are `by_title` and `by_member`: every page already has `member`, the member who is logged in.

The pages' transcript, `shelf/web.http`, now starts with `# today 2026-11-18 10:00`: the tool stops Symfony's clock at that moment, so the dates in `web.out` are the same on whichever day it runs.

### Tests

The report on a real SQLite database, `shelf/tests/Database/StatisticsTest.php`:

<!-- include: code/86-dashboard/shelf/tests/Database/StatisticsTest.php -->

`DashboardTest` checks the pages: what is overdue, the history narrowed to a book or a member, a page after the last, and that a reader gets 403.

## Exercises

The exercises are in `exercises/86-dashboard/`. Check your work with:

```
composer exercise 86 beginner
```

1. **Beginner, `perMember.php`:** the loans of each member, with one `GROUP BY`.
2. **Intermediate, `months.php`:** every month from one to another, with 0 where the database had no row.
3. **Advanced, `Report.php`:** the books borrowed most, the share back on time, and the books nobody borrowed.

## Summary

- `GROUP BY` counts in the database; `HAVING` keeps groups as `WHERE` keeps rows.
- `LEFT JOIN` keeps the rows without a match; `COUNT(column)` counts them as 0.
- A `GROUP BY` has no row for what isn't there: PHP fills in the gaps.
- A report is a query class that only reads; entities are for changing things.
- SQLite adds a `NOT NULL` column to a table with rows by building the table again.

## Further reading

- SQLite documentation, aggregate functions: https://www.sqlite.org/lang_aggfunc.html
- SQLite documentation, SELECT (GROUP BY and HAVING): https://www.sqlite.org/lang_select.html
- SQLite documentation, ALTER TABLE: https://www.sqlite.org/lang_altertable.html
- Doctrine DBAL documentation, data retrieval and manipulation: https://www.doctrine-project.org/projects/doctrine-dbal/en/current/reference/data-retrieval-and-manipulation.html
- MDN, the meter element: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meter
