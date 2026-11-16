# 8. Strings

## Goals

- You will be able to join strings, and put variables into a string.
- You will be able to explain the difference between single and double quotes.
- You will be able to use the string functions you will need most: trim, change case, search, replace and cut.
- You will be able to work with text in any language, such as Arabic and German, using the `mb_` functions.

## Before you start

- Chapter 7: you know that text is a `string`, and you can check a value with `var_dump`.

## The idea

Most of what a program shows to people is text: titles, names, messages. So a lot of everyday code builds text from pieces, cleans it up, or looks for something in it.

PHP has many functions for strings. A **function** is a named piece of code that does one job: you give it values in brackets, and it gives a result back. `strlen($title)` gives back the length of `$title`. You will write your own functions in chapter 15; here you use the ones PHP brings.

One thing needs care from the start: a computer stores text as **bytes**, and not every character is one byte. Letters like a, b and c are one byte each. ü takes two, and an Arabic letter also takes two. Some string functions count bytes, others count characters, and with text in other languages that difference shows.

## In code

### Joining strings

<!-- include: code/08-strings/joining.php -->

- The dot `.` joins two strings into one. This is called **concatenation**. The space between the names is a string of its own, `' '`, joined in the middle.
- `.=` adds a string to the end of a variable: `$line .= $author;` is short for `$line = $line . $author;`.
- `echo $author . "\n";` joins first and prints one string. `echo $line, "\n";` prints two values one after the other. Both give the same output.

### Variables inside double quotes

<!-- include: code/08-strings/interpolation.php -->

Inside double quotes, PHP replaces a variable with its value. This is called **interpolation**, and it often reads more clearly than joining many pieces with dots.

Curly braces mark exactly where the variable name starts and ends: `{$copies}`. They are optional here, but needed when letters follow the name directly: `"{$copies}x"`. Using them always is a good habit.

### Single quotes keep everything as it is

<!-- include: code/08-strings/literal.php -->

In single quotes, PHP changes nothing: `$title` stays the six characters `$title`, and `\n` stays a backslash and an n, as in chapter 5.

So the rule is short. Single quotes for fixed text; double quotes when you want a variable's value or a new line inside.

### Useful string functions

<!-- include: code/08-strings/functions.php -->

- `trim` removes spaces (and new lines) from the start and the end. Text typed by people often has them.
- `strlen` gives the length.
- `strtoupper` and `strtolower` change the case.
- `str_replace('Darkness', 'Light', $clean)` replaces every `Darkness` with `Light`. It returns a new string; `$clean` itself stays as it was.
- `substr($clean, 0, 8)` cuts out 8 characters, starting at position 0. Positions start at 0, not 1.
- `str_contains` and `str_starts_with` answer a question, so they return a bool. The search is case-sensitive: `'A '` is not found at the start.

Each function returns its result and changes nothing in place. To keep a result, store it: `$clean = trim($title);`.

### Text in any language

<!-- include: code/08-strings/multibyte.php -->

`strlen` counts bytes, not characters. "Günter Grass" has 12 characters, but ü takes two bytes, so `strlen` says 13. The Arabic name has 10 characters (with the space), each letter two bytes: 19.

The functions starting with `mb_` (multibyte) count and cut **characters**:

- `mb_strlen` gives 12 and 10.
- `mb_strtoupper` also changes ü to Ü. `strtoupper` changes only the letters a to z, so it leaves ü as it is.
- `mb_substr` cuts whole characters: the first 4 of the Arabic name are the first name, نجيب. `substr` would cut through the middle of a letter and print broken text.

Arabic has no upper and lower case, so `mb_strtoupper` leaves it unchanged, as it should.

**Rule:** whenever text can contain more than the letters a to z, which is almost always with names and titles, use the `mb_` functions.

## Common mistakes

### Joining with `+`

<!-- include: code/08-strings/plus.error.php -->

In some other languages, `+` joins strings. In PHP, `+` only adds numbers, and two names are no numbers: PHP stops with a `TypeError`.

**Fix:** join with a dot: `$firstName . ' ' . $lastName`, or write `"$firstName $lastName"`.

### A variable in single quotes

The example "Single quotes keep everything as it is" above printed `$title\n` instead of the title. There is no error message here: the output is simply wrong.

**Fix:** when a string should contain a variable's value or `\n`, use double quotes.

### Counting bytes instead of characters

`strlen` on a name with ü, é or Arabic letters gives a number that is too high, and `substr` can cut a letter in half. Again, no error message.

**Fix:** use `mb_strlen` and `mb_substr` for text that people write.

## Principle check

**KISS:** pick the form that reads most clearly. `"$title, by $author"` is easier to read than `$title . ', by ' . $author`. When a string gets long and complicated, build it in steps, in variables with good names.

## Security

Nothing new here yet. Strings become a security topic when text from users is shown on a web page; chapter 46 shows how to do that safely.

## Shelf

The four `echo` lines of *Shelf* become easier to read with double-quoted strings. Change `shelf/shelf.php` to:

<!-- include: code/08-strings/shelf/shelf.php -->

The output stays exactly the same. Run it to check:

```
php shelf/shelf.php
```

## Exercises

The exercises are in `exercises/08-strings/`. Check your work with:

```
composer exercise 08 beginner
```

1. **Beginner, `label.php`:** build a shelf label from three variables, without typing the values again.
2. **Intermediate, `slug.php`:** turn a messy title into a web-address-friendly "slug", with string functions.
3. **Advanced, `names.php`:** a script counts and cuts German and Arabic names wrongly. Find out why, and fix it.

## Summary

- `.` joins strings, and `.=` adds to the end of one.
- Double quotes put variable values and `\n` into the text; single quotes keep everything as written.
- `trim`, `strtolower`, `str_replace`, `substr`, `str_contains` and `str_starts_with` return new values and change nothing in place.
- `strlen` and `substr` work in bytes. For text in any language, use `mb_strlen`, `mb_substr` and `mb_strtoupper`.

## Further reading

- PHP manual, Strings: https://www.php.net/manual/en/language.types.string.php
- PHP manual, String functions: https://www.php.net/manual/en/ref.strings.php
- PHP manual, Multibyte string functions: https://www.php.net/manual/en/ref.mbstring.php
