# 40. Static analysis with PHPStan

## Goals

- You will be able to explain what static analysis finds that running the code doesn't.
- You will be able to run PHPStan on a file or a project, at a level you choose, and read its messages.
- You will be able to fix the most common findings: a typo, a possible `null`, an array without a value type, and `mixed` data from outside.
- You will be able to give a project its own PHPStan configuration, at the highest level.

## Before you start

- Chapter 16: types, `?string` and `declare(strict_types=1)`.
- Chapter 23: JSON, and why data from a file must be checked.
- Chapter 39: tools that read code, run from the course folder.

## The idea

Running code finds a mistake only on the lines that run, with the values they get that day. A typo in an `if` that runs once a month waits a month. A function that returns `null` when nothing is found breaks only on the day nothing is found.

**Static analysis** reads the code without running it, and follows every path. It knows the types: that `findTitle` returns `string` or `null`, that `strtoupper` needs a `string`, and that `Book` has no property `$autor`. So it can say, before anyone runs the program: on this line, this value can be of a type that this function doesn't accept.

**PHPStan** is the static analyser this course uses. You have met it in every chapter without seeing it: `composer verify` has run it on every example, every solution, *Shelf* and the course's own tools. It works in **levels**, from 0 to 10. Each level checks everything the one below does, and more:

| Level | Adds |
|---|---|
| 0 | unknown classes and functions, wrong numbers of arguments, variables that are never defined |
| 1 | variables that might not be defined |
| 2 | unknown methods and properties on every object, checks of PHPDoc |
| 3 | return types, and the types written into properties |
| 4 | dead code: checks that are always true or false, code that can't be reached |
| 5 | the types of arguments |
| 6 | missing types, including what an array holds |
| 7 | union types that are only partly right |
| 8 | `null`: methods called on, and values passed from, something that can be `null` |
| 9 | `mixed`: a value of unknown type may only be passed on, not used |
| 10 | also values whose type is unknown because it was never written |

A new project starts at the highest level and stays there. An old project starts low and climbs one level at a time. The course's examples run at level 5 before chapter 16, which had no types yet, and at 6 after it; solutions from this chapter on and the course's tools run at 8, and *Shelf*, after this chapter, at 10.

## In code

Every example in this chapter runs, and prints what it should. PHPStan still finds a problem in each one. Run it from the course folder, on a file and at a level:

```
vendor/bin/phpstan analyse --level 2 code/40-static-analysis/typo.php
```

### A typo that hasn't happened yet

<!-- include: code/40-static-analysis/typo.php -->

The program prints `Kindred`, because `$long` is `false` and the broken line never runs. With `true`, it would print the warning "Undefined property: Book::$autor" and no author. PHPStan reads every line, so it finds the typo now. The message names the line, the problem, and an identifier, `property.notFound`, which you can look up.

**Fix:** `$book->author`.

### `null` where a string is needed

<!-- include: code/40-static-analysis/null.php -->

`findTitle` says it returns `?string`. Today it finds "Kindred". With a search for "Dune", it returns `null`, and `strtoupper(null)` stops the program with a `TypeError` under `strict_types`. PHPStan doesn't need that day: from level 8, it sees that `string|null` may go where only `string` is allowed.

**Fix:** decide what should happen when nothing is found, and write it:

```php
$title = findTitle(['Kindred', 'Sula'], 'Dune');
echo $title === null ? "Not found\n" : strtoupper($title) . "\n";
```

After the `=== null` check, PHPStan knows that `$title` is a `string` in the other branch. This is called **narrowing**: a check makes the type smaller. Don't hide the `null` with a cast, `strtoupper((string) $title)`: PHPStan is quiet then, but the program prints an empty line where it should say what happened.

### An array of what?

<!-- include: code/40-static-analysis/value-type.php -->

`array` says nothing about what is inside. PHPStan from level 6 asks for it, because without it, it can't check `mb_strlen($title)`: `$title` could be anything.

**Fix:** the PHPDoc types you have written since chapter 16, above the function:

```php
/** @param list<string> $titles */
```

### `mixed`: data from outside

<!-- include: code/40-static-analysis/mixed.php -->

`book.json` could hold anything: an object, a list, a number, or `{"title": null}`. So `json_decode` returns `mixed`, and level 10 refuses to use a `mixed` value as an array or to print it. The program works today, because today's file is right. Tomorrow's file may not be.

**Fix:** check the data where it enters the program, and fail early with a clear message:

<!-- include: code/40-static-analysis/checked.php -->

After the `if`, PHPStan knows that `$book` is an array with a string `title` and an int `year`, and the answer is `[OK] No errors`. This is the job of `Book::fromArray` in *Shelf* (chapter 32): data from files is checked once, at the edge, and the rest of the program can trust its types.

### A project's configuration

Typing the level and the paths every time is easy to get wrong. A project keeps them in `phpstan.neon.dist`, a file in **NEON** format, which is like YAML: names, a colon, and indentation for what belongs inside. The course's own `phpstan.neon.dist` in the course folder shows the parts you need:

- `parameters:` holds the settings;
- `level:` the level, and `paths:` the files and folders to analyse;
- `excludePaths:` what to leave out, such as `vendor/`;
- `ignoreErrors:` messages that are expected, each with a comment that says why.

With the file in place, the command is only `vendor/bin/phpstan analyse`, or `-c path/to/phpstan.neon.dist` for a project in another folder. As with PHP-CS-Fixer, `.dist` means the file is committed and shared.

## Common mistakes

### Silencing instead of fixing

PHPStan reads a comment such as `// @phpstan-ignore argument.type` above a line, and stays quiet about it. Used to hide a real `null`, it removes the warning and keeps the bug.

**Fix:** fix the code. When PHPStan is really wrong, which is rare, write the identifier and a reason: `// @phpstan-ignore argument.type (the list is never empty here: checked above)`. Without a reason, nobody can tell later whether the ignore is still right.

### A PHPDoc that doesn't match the code

PHPStan believes your PHPDoc types. A `@return list<string>` on a function that can return `false` makes every caller look safe when it isn't. PHPStan reports a mismatch inside the function when it can see one, but a wrong `@param` on a function nobody calls wrongly yet stays unnoticed.

**Fix:** use native types (`string`, `?Book`) wherever PHP has them, and PHPDoc only for what PHP can't say, such as `list<string>`.

### The highest level on an old project, at once

An existing project at level 10 can report hundreds of errors on day one. Nobody fixes them, and the check is switched off again.

**Fix:** start at a level with few errors, fix them, and go up one level at a time. PHPStan can also record today's errors in a **baseline** file (`--generate-baseline`), so that only new errors fail, while the old ones are fixed over time.

## Principle check

**Secure by default:** types checked on every path, before the code runs, catch the mistakes that tests miss because nobody thought of that input. **KISS:** a clear type at the edge (`fromArray`) saves checks everywhere else.

## Security

PHPStan finds type mistakes, and many security bugs start as one: a `null` that skips a check, a `mixed` value from a request used as if it were checked. It doesn't know which data comes from an attacker, so it doesn't find SQL injection or missing escaping: Parts 7 and 8 handle those with rules you apply everywhere. What it gives you is the habit this chapter shows, `mixed` checked at the edge, which is the start of every input validation.

## Shelf

*Shelf* gets its own configuration, at the highest level, 10. Create `shelf/phpstan.neon.dist`:

<!-- include: code/40-static-analysis/shelf/phpstan.neon.dist -->

- `paths:` are relative to the file: the script `shelf.php` and the classes in `src/`.
- The `ignoreErrors` entry is the one expected message. PHPStan can't know that `shelf.php` always runs from the command line, where PHP sets `$argv` (chapter 24). The entry names the exact message and the one file, so it hides nothing else.

Run it from the course folder:

```
vendor/bin/phpstan analyse -c shelf/phpstan.neon.dist
```

The answer is `[OK] No errors`. *Shelf* reaches the highest level without a change, because every chapter since 16 has given it types, and `fromArray` checks every row of `books.json` and `members.json` before it becomes an object. `composer verify` now runs *Shelf* with this configuration.

## Exercises

The exercises are in `exercises/40-static-analysis/`. Check your work with:

```
composer exercise 40 beginner
```

1. **Beginner, `nulls.php`:** handle a `null` that PHPStan finds at level 8, without a cast.
2. **Intermediate, `types.php`:** give arrays their value types, so level 6 passes.
3. **Advanced, `members.php`:** check members from JSON, so the code passes level 10 and rejects bad data with a clear message.

## Summary

- Static analysis reads every path without running the code, and finds type mistakes before they happen.
- PHPStan has levels 0 to 10; new code starts at the highest, old code climbs one level at a time.
- Fix a possible `null` with a check (narrowing), not with a cast; give arrays their value types with PHPDoc.
- Check `mixed` data from outside once, where it enters, and the rest of the program can trust its types.
- A project keeps its level and paths in `phpstan.neon.dist`; ignore a message only with its identifier and a reason.

## Further reading

- PHPStan documentation, Getting started: https://phpstan.org/user-guide/getting-started
- PHPStan documentation, Rule levels: https://phpstan.org/user-guide/rule-levels
- PHPStan documentation, Ignoring errors: https://phpstan.org/user-guide/ignoring-errors
- PHPStan documentation, PHPDoc types: https://phpstan.org/writing-php-code/phpdoc-types
