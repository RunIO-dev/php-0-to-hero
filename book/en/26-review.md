# 26. Mini-review: reading our program as a whole

## Goals

- You will be able to read a program of a few hundred lines and find what is repeated, unclear or in the wrong place.
- You will be able to give a repeated rule one name, in one place.
- You will be able to split a program into files with `require_once`.
- You will be able to change code in small steps, and check after each that it still works.

## Before you start

- Chapters 15 to 25: everything *Shelf* does so far.
- Chapter 14: the rule of three.

## The idea

*Shelf* grew one chapter at a time. Each change was small and made sense on its own. Read as a whole, the program shows things that no single chapter did: the same rule written in two places, a list of commands kept twice, and one file with 257 lines and three different jobs.

This is normal. Code is written forwards, but understood as a whole, so from time to time you stop adding features, read everything, and tidy up. Changing the structure of code without changing what it does is called **refactoring**.

A checklist for the reading:

- **Repeated knowledge:** is a rule, a number or a list written in more than one place? Then a change can be made in one place and forgotten in another.
- **Unclear names:** can you say what a function does from its name alone?
- **Jobs mixed up:** does a function, or a file, do things that change for different reasons, such as rules and output?
- **Size:** is a file so long that you have to scroll to find things?

Refactor in small steps. After each step, run the program, or its tests, and compare the output with before. The `.out` files of this course do that for you.

## In code

### A rule written twice drifts apart

<!-- include: code/26-review/repeated.php -->

Three functions compare titles in any case. They were written at different times, and now they disagree: one trims spaces, one uses `strtolower`, which doesn't know "Ü" (chapter 8). Each copy looked right where it was written.

<!-- include: code/26-review/named.php -->

`sameTitle` gives the rule a name and one home. Every caller now agrees, and when the rule changes, for example to ignore accents, it changes once.

DRY is about **knowledge**, not about lines that look alike. Two lines that happen to look the same may mean different things and change for different reasons; leave those alone. But a rule of your library, "what counts as the same title", belongs in one place, even when it is used only twice so far.

### Splitting a program into files

<!-- include: code/26-review/format.php -->

<!-- include: code/26-review/report.php -->

- `format.php` only defines functions; it prints nothing when it is loaded.
- `require_once __DIR__ . '/format.php';` loads it. The functions are then available as if they were written in `report.php`.
- `__DIR__` makes the path independent of the folder you start PHP in, as in chapter 22.
- `require` stops with a fatal error if the file is missing; the older `include` only warns and goes on, which is rarely what you want. The `_once` loads a file only the first time, however often it is required.

## Common mistakes

### Loading a file twice

<!-- include: code/26-review/twice.error.php -->

⚠ Plain `require` loads the file every time. The second time, `money()` is defined again, and PHP stops: "Cannot redeclare function money()".

**Fix:** use `require_once` for files that define functions.

### Refactoring and changing behaviour at once

A refactoring step that also "fixes a little thing" makes it impossible to tell, when the output changes, which of the two caused it.

**Fix:** keep them apart. Refactor first, with the output unchanged, and fix the bug in its own step, with its own check. The bug fix in *Shelf* below is listed separately for that reason.

### Splitting too early, or too fine

A program of 60 lines in six files is harder to read than in one: you jump between files to follow one idea.

**Fix:** split when a file has several jobs and is long enough that you search in it. Split by job, not by size.

## Principle check

**DRY:** one rule, one place; but only for knowledge, not for lines that merely look alike. **KISS:** three files with one job each are easier to read than one file with three. **YAGNI:** the review only tidies what is there. It adds nothing "for later".

## Security

Reviews find security problems that grew unnoticed. In *Shelf*, every input already passes through one place before it is used: `required`, `addBook` and `toBook`. That is the property to keep as the program grows: one checked entrance for each kind of input, never a second, unchecked way in.

## Shelf

Reading *Shelf* as a whole found five things to change, done as separate steps:

1. **One rule twice:** `findBook` and `addBook` both compared titles in any case, each in its own way. Now both use `sameTitle`.
2. **One cleaning twice:** removing hyphens and spaces from an ISBN was written in two places. Now it is `isbnDigits`.
3. **The commands twice:** `run` listed them in an `in_array` check and again in the `match`. Now only the `match` lists them, and its `default` refuses the rest. `required` takes the check for a missing argument out of `run`.
4. **Jobs mixed up:** `addBook`, a rule of the library, used `USAGE`, a text of the command line. Now the rules know nothing about the command line.
5. **One file, three jobs:** the program is now three files: `books.php` reads and writes the data, `library.php` holds the rules, and `shelf.php` the commands.

And one bug, fixed in its own step: `lend kindred` printed "Lent: kindred", with the title as typed. Now it prints the book's real title.

One repetition stays: the shape of a book, written in every PHPDoc comment. Part 5 replaces it with a `Book` class. Create `shelf/books.php`:

<!-- include: code/26-review/shelf/books.php -->

Create `shelf/library.php`:

<!-- include: code/26-review/shelf/library.php -->

And change `shelf/shelf.php` to:

<!-- include: code/26-review/shelf/shelf.php -->

Run `php shelf/shelf.php` and every command from chapters 24 and 25. Everything prints what it printed before, except the fixed bug.

## Exercises

The exercises are in `exercises/26-review/`. Check your work with:

```
composer exercise 26 beginner
```

1. **Beginner, `fees.php` and `receipt.php`:** move a function both scripts define into its own file, and load it with `require_once`.
2. **Intermediate, `fees.php`:** three copies of the late-fee rule have drifted apart; give the rule one place.
3. **Advanced, `commands.php`:** a list of commands is kept twice; keep it once, without changing what the commands do.

## Summary

- From time to time, read the whole program: look for repeated knowledge, unclear names, mixed jobs and long files.
- Refactoring changes the structure, not the behaviour; check the output after every small step.
- DRY is about knowledge: give a rule one name in one place.
- `require_once __DIR__ . '/file.php'` loads a file of functions once; a missing file stops the program.
- Split files by job, and only when a file has several.

## Further reading

- PHP manual, `require_once`: https://www.php.net/manual/en/function.require-once.php
- PHP manual, `require`: https://www.php.net/manual/en/function.require.php
- PHP manual, `include`: https://www.php.net/manual/en/function.include.php
