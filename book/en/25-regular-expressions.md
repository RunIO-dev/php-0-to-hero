# 25. Regular expressions, the useful minimum

## Goals

- You will be able to check that a text has a shape, such as five digits or an ISBN, with `preg_match`.
- You will be able to take parts out of a text with capturing groups.
- You will be able to clean and split text with `preg_replace` and `preg_split`.
- You will be able to decide when a regular expression is the wrong tool.

## Before you start

- Chapter 24: you validate input before you use it.
- Chapter 8: you know the string functions, and `mb_*` for letters beyond English.

## The idea

Some checks are about the **shape** of a text, not its exact value: "five digits" for a German postcode, "four digits, a hyphen, two digits, a hyphen, two digits" for a date. Writing such checks with loops and `ctype_digit` takes many lines.

A **regular expression** (regex, or pattern) describes a shape in a compact text of its own. `/^\d{5}$/` means: the start, then a digit five times, then the end. PHP's `preg_` functions use these patterns to check, to take parts out, to replace and to split.

Regular expressions are a language of their own, and a large one. This chapter teaches the small part you need most, and where to stop. Many problems that look like regex problems have a clearer answer: a string function, or a check that PHP already has.

## In code

### Does the text have this shape?

<!-- include: code/25-regular-expressions/match.php -->

The pattern is a string. The slashes `/.../` mark its start and end. Inside:

| Part | Means |
|---|---|
| `^` and `$` | the start and the end of the text |
| `\d` | a digit, 0 to 9 |
| `{5}` | the part before it exactly 5 times; `{1,3}` 1 to 3 times |
| `+` and `*` | the part before it 1 or more times; 0 or more times |
| `[A-Z]` | one character from a range; `[,;]` one of these characters |
| `\s` | a space, tab or line break |
| `.` | any one character |

`preg_match` returns 1 when the text matches, and 0 when it doesn't. The last test fails because `1O115` has a capital O where a zero belongs, a mistake a person can't see but the pattern can.

Single quotes around the pattern matter: in double quotes, PHP would read `\d` and `$` itself, before the regex sees them.

### Taking parts out

<!-- include: code/25-regular-expressions/parts.php -->

- Round brackets **capture** the part they match. `(?<year>...)` also gives it a name.
- The third argument, `$match`, receives the parts: `$match['title']` and `$match['year']`.
- `.+` means "one or more characters of any kind". `\(` and `\)` are the brackets around the year, as plain characters: a backslash takes away a character's special meaning. This is called **escaping**.

### Replacing and splitting

<!-- include: code/25-regular-expressions/replace.php -->

- `preg_replace($pattern, $replacement, $text)` replaces every match. Here every run of spaces and tabs becomes one space.
- `preg_split($pattern, $text)` splits at every match, like `explode`, but with a pattern: commas with or without spaces around them.

### A shape and a calculation: the ISBN

<!-- include: code/25-regular-expressions/isbn.php -->

An ISBN-13 has 13 digits, starts with 978 or 979, and its last digit is a **check digit**, calculated from the other twelve. A typo changes the check digit, so most typos are caught.

The regex checks only the shape, after the hyphens and spaces are removed. The calculation is plain arithmetic in a loop. Doing both in one pattern would be close to impossible, and unreadable. The `D` after the closing slash is explained under the common mistakes below.

### When not to use a regular expression

<!-- include: code/25-regular-expressions/email.php -->

The rules for email addresses fill several pages, and no short regex gets them right. `filter_var` with `FILTER_VALIDATE_EMAIL` knows them. Whenever PHP has a check for something (emails, URLs, numbers, dates), use it instead of a pattern.

<!-- include: code/25-regular-expressions/no-regex.php -->

For fixed text, the string functions say what they do: `str_starts_with`, `str_contains`, and `ctype_digit` for "only digits". A reader understands them without decoding a pattern.

## Common mistakes

### A pattern without `^` and `$`

<!-- include: code/25-regular-expressions/anchors.php -->

Without the **anchors** `^` and `$`, the pattern only has to appear somewhere: `abc10115xyz` contains five digits in a row, so `/\d{5}/` matches it. For validation, that is almost never what you want.

**Fix:** anchor every validation pattern: `/^...$/`.

### Letters beyond English

<!-- include: code/25-regular-expressions/unicode.php -->

Without the `u` flag after the closing slash, the `preg_` functions see bytes, not letters, and "ü" is two bytes that `\w` doesn't count as a letter. "Jürgen" fails a check meant for names. "Ana Lima" fails in both cases: the pattern allows no space, which is the next lesson about names, they don't fit a short pattern.

**Fix:** add the `u` flag to patterns for text that people write. For names, check only what you need (not empty, not too long), not their shape.

### A line break at the end

<!-- include: code/25-regular-expressions/newline.php -->

`$` matches at the very end of the text, but also just before a line break at the end. A typed line that still has its Enter key passes the check, and the line break is stored with it.

**Fix:** add the `D` flag (`/^\d{5}$/D`), so `$` means only the end, and `trim` input before you check it.

## Principle check

**KISS:** a regex is the right tool for a shape. For everything else, prefer a string function or a built-in check, and split a complicated check into a short pattern and plain code, as the ISBN check does. A pattern that needs a comment to explain it is often a sign to write code instead.

## Security

Two rules for patterns:

- **Never build a pattern from input** without escaping it. A search text such as `.*` or `(a+)+` has a special meaning in a regex. `preg_quote($input, '/')` escapes every special character, so the text is matched as it is.
- **Some patterns can take forever.** Nested repetition such as `(a+)+` can make PHP try billions of ways on a crafted input of a few dozen characters. PHP stops after a limit and `preg_match` returns `false`, so compare the result with `=== 1`, as this chapter does, and keep patterns simple.

## Shelf

*Shelf* gets an `add` command: `add <title> <author> <year> [<isbn>]`. Every field is checked before the book is saved: `tidy` cleans the spaces in the title and author with `preg_replace`, `filter_var` checks the year, `isIsbn13` the optional ISBN, and a title that is already on the shelf is refused. A book now has an `isbn`, which is `null` when it's unknown; the books in your `books.json` don't need one. Change `shelf/shelf.php` to:

<!-- include: code/25-regular-expressions/shelf/shelf.php -->

Try it:

```
php shelf/shelf.php add "The Left  Hand of Darkness" "Ursula K. Le Guin" 1969
php shelf/shelf.php add "the left hand of darkness" "Ursula K. Le Guin" 1969
php shelf/shelf.php add Sula "Toni Morrison" 1973 978-0-306-40615-8
php shelf/shelf.php list
```

The first adds the book, with one space between "Left" and "Hand". The second is refused as a duplicate, and the third because of its check digit.

## Exercises

The exercises are in `exercises/25-regular-expressions/`. Check your work with:

```
composer exercise 25 beginner
```

1. **Beginner, `shelf-code.php`:** check shelf codes such as `SF-012`, and nothing else.
2. **Intermediate, `tags.php`:** split typed tags at commas and semicolons, and clean them.
3. **Advanced, `log-line.php`:** take a log line apart with named groups, or return `null`.

## Summary

- `preg_match('/^...$/D', $text) === 1` checks a shape; anchor validation patterns.
- Round brackets capture parts; `(?<name>...)` names them; a backslash escapes a special character.
- `preg_replace` replaces, `preg_split` splits; add `u` for text that people write.
- Prefer `filter_var` and string functions where they exist; split complicated checks into a pattern and code.
- Never build a pattern from input without `preg_quote`.

## Further reading

- PHP manual, PCRE functions: https://www.php.net/manual/en/ref.pcre.php
- PHP manual, Pattern syntax: https://www.php.net/manual/en/reference.pcre.pattern.syntax.php
- PHP manual, Pattern modifiers: https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
- PHP manual, `preg_quote`: https://www.php.net/manual/en/function.preg-quote.php
