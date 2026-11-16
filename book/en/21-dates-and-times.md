# 21. Dates and times

## Goals

- You will be able to create, format and compare dates with `DateTimeImmutable`.
- You will be able to calculate a due date, and the days between two dates.
- You will be able to show the same moment in different time zones.
- You will be able to check that a date typed by a person is a real date.

## Before you start

- Chapter 19: you can read an exception and its message.
- Chapter 16: you can write typed functions.

## The idea

Dates look simple and aren't. Months have 28 to 31 days, some years have a 29 February, and 18:00 in Berlin is 02:00 the next morning in Tokyo. Calculating with dates as text or as numbers of seconds goes wrong at exactly these edges.

PHP has a class for this: `DateTimeImmutable`. A value of a class is called an **object**; Part 5 explains classes properly. For now, read an object as a value that also has its own functions, called **methods**, which you call with an arrow: `$date->format('Y-m-d')`.

A `DateTimeImmutable` object holds one moment: a date, a time and a time zone. It knows the calendar, so adding 14 days to 25 December gives 8 January. "Immutable" means it never changes: every calculation gives you a new object and leaves the old one as it was.

## In code

### Creating and formatting a date

<!-- include: code/21-dates-and-times/create.php -->

- `new DateTimeImmutable('2026-11-17')` creates a date object from text. `new` is the word that creates an object.
- `format()` turns it into text. Each letter stands for a part: `Y` the year with four digits, `m` the month with two, `d` the day with two, `l` the weekday's name, `F` the month's name, `j` the day without a leading zero.
- Use `Y-m-d` (ISO 8601) whenever a date is stored or exchanged. It is unambiguous, and sorts correctly as text. Other formats are for people.

### Calculating with dates

<!-- include: code/21-dates-and-times/modify.php -->

`modify('+14 days')` returns a **new** date, 14 days later. `$lentOn` stays 17 November, which is why it can still be printed afterwards. `modify` understands many English phrases: `'+1 month'`, `'-2 days'`, `'next monday'`.

### Comparing dates

<!-- include: code/21-dates-and-times/compare.php -->

- Two dates compare with `<`, `>` and `==`, by the moment they stand for.
- `$dueOn->diff($returnedOn)` gives the time between them. Its `days` is the number of whole days.

### Time zones

<!-- include: code/21-dates-and-times/zones.php -->

The event happens once, at 18:00 in Berlin. `setTimezone` shows that same moment on another clock: 14:00 in São Paulo, 02:00 the next day in Tokyo. **UTC** is the world's reference time, with no daylight saving.

A good habit: store moments in UTC, and convert to a person's time zone only when you show them. A date without a time, such as a due date, needs no conversion.

Time zone names have the form `Continent/City`. The PHP manual lists them all.

### Today, as a parameter

<!-- include: code/21-dates-and-times/testable.php -->

`new DateTimeImmutable('today')` is today's date, and `new DateTimeImmutable()` is this very moment. A function that asks the clock itself gives another answer every day, so its result can't be checked against a fixed `.out` or a test.

`dueDate` gets the lending day as a parameter instead. The caller passes today in the real program, and any day it likes in a test. This course's examples use fixed dates for the same reason.

## Common mistakes

### `DateTime` changes itself

<!-- include: code/21-dates-and-times/mutable.php -->

`DateTime`, without "Immutable", is the older class. Its `modify` changes the object itself and returns that same object. `$lentOn` and `$dueOn` are now one object, and the lending day has moved to the due date.

**Fix:** use `DateTimeImmutable`. This course uses it everywhere.

### A date that doesn't exist

<!-- include: code/21-dates-and-times/overflow.php -->

There is no 30 February, but PHP doesn't refuse it: it counts on into March and gives 2 March. For a date a person typed, that is a wrong date, accepted without a word.

**Fix:** read the input with a fixed format, `createFromFormat('!Y-m-d', $input)`, and check that formatting the result gives back exactly the input. The `!` sets the parts that are not in the format, such as the time, to zero. `createFromFormat` returns `false` for text it can't read at all.

### Text that is not a date

<!-- include: code/21-dates-and-times/malformed.error.php -->

⚠ For text it can't understand, `new DateTimeImmutable` throws a `DateMalformedStringException`. Its message names the text and the position where reading failed.

**Fix:** for input, check it first, as above, and catch the exception where the program can handle it.

## Principle check

**KISS:** let `DateTimeImmutable` do the calendar. Code that adds 86,400 seconds per day gets daylight saving wrong twice a year. **Secure by default:** a date typed by a person is checked against a fixed format, not "understood" by `modify`'s many phrases.

## Security

A date from outside is input like any other. `new DateTimeImmutable($input)` accepts phrases such as "+100 years" or "last day of next month", which a form field for a return date should not. Check the format first, as in `overflow.php`, and check the range: a loan can't be returned before it started.

## Shelf

A loan now has a due date, 14 days after lending. `LOAN_DAYS` is a constant, and `dueDate` gets the lending day as a parameter. The script uses a fixed "today", so its output is the same every day. Change `shelf/shelf.php` to:

<!-- include: code/21-dates-and-times/shelf/shelf.php -->

Run it with `php shelf/shelf.php`. To see real dates, change `$today` to `new DateTimeImmutable('today')`.

## Exercises

The exercises are in `exercises/21-dates-and-times/`. Check your work with:

```
composer exercise 21 beginner
```

1. **Beginner, `due.php`:** write a function for the due date, with 14 days as the default.
2. **Intermediate, `late.php`:** count the days a book came back late.
3. **Advanced, `valid-date.php`:** accept only real dates written as YYYY-MM-DD.

## Summary

- `DateTimeImmutable` holds a moment; `format` turns it into text, `Y-m-d` for storing.
- `modify` returns a new date; dates compare with `<` and `>`; `diff()->days` counts the days between.
- `setTimezone` shows the same moment on another clock; store moments in UTC.
- Pass "today" as a parameter, so functions give the same answer in every test.
- Check typed dates with `createFromFormat('!Y-m-d', ...)` and compare the result with the input.

## Further reading

- PHP manual, `DateTimeImmutable`: https://www.php.net/manual/en/class.datetimeimmutable.php
- PHP manual, Date formats for `format()`: https://www.php.net/manual/en/datetime.format.php
- PHP manual, Relative formats for `modify()`: https://www.php.net/manual/en/datetime.formats.php
- PHP manual, List of supported time zones: https://www.php.net/manual/en/timezones.php
