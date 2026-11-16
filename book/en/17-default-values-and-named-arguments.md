# 17. Default values, named arguments and nullable types

## Goals

- You will be able to give a parameter a default value, so that a call may leave it out.
- You will be able to pass arguments by name, and explain when that makes a call clearer.
- You will be able to use nullable types (`?string`) for a value that may be missing.
- You will be able to avoid passing `null` on to code that can't handle it.

## Before you start

- Chapter 16: you can write functions with types and `strict_types`.
- Chapter 11: you know `??`, the null coalescing operator.

## The idea

Some arguments are almost always the same. Most books cost 50 cents per day late; only a few cost something else. Writing `lateFee(3, 50)` in every call repeats a number that the function could know. A **default value** lets the function fill it in: `int $perDay = 50`. A call can leave it out, or pass another value.

When a function has several optional parameters, a call such as `bookLine('Kindred', 'Octavia E. Butler', 1979, false)` is hard to read: what does `false` mean? A **named argument** says it: `withYear: false`. The name is the parameter's name, without the `$`.

And some values may simply not exist. A book that is not on loan has no due date. PHP's word for "no value" is `null`, and a **nullable type** allows it: `?string` means "a string, or null". It makes the missing case part of the function's first line, so the caller can't forget it.

## In code

### Default values

<!-- include: code/17-default-values-and-named-arguments/defaults.php -->

`= 50` after the parameter is its default. The first call passes one argument, so `$perDay` is 50. The second passes two, so `$perDay` is 20.

Parameters with a default come **after** the ones without. Otherwise, a call could never leave them out, because the arguments are matched by position.

### Named arguments

<!-- include: code/17-default-values-and-named-arguments/named.php -->

- `withYear: false` passes `false` to the parameter `$withYear`. Parameters that are left out keep their defaults.
- With names, the order doesn't matter: `bookLine(author: ..., title: ...)` works.
- A long parameter list can be written one parameter per line, with a comma after the last one, as `bookLine` is here.

<!-- include: code/17-default-values-and-named-arguments/named-builtin.php -->

Built-in functions have parameter names too; the PHP manual lists them. In `number_format`, the names say which argument is the decimal separator and which is the thousands separator, the kind of detail that is easy to swap by mistake. A long call can also be split, one argument per line.

Use names where a value alone doesn't say what it is, such as `true`, `false`, `null` or a bare number. For `lateFee(3)`, a name adds nothing.

### Nullable types

<!-- include: code/17-default-values-and-named-arguments/nullable.php -->

- `?string $dueDate` accepts a string or `null`. The function then checks which one it got: `$dueDate === null`.
- `: ?string` as a return type means "returns a string, or null". `authorOf` returns `null` for a title it doesn't know. That's not an error, just an answer: "no author known".
- At the call, `??` turns the `null` into a fallback text: `authorOf(...) ?? 'unknown'`.

### A parameter that may be left out

<!-- include: code/17-default-values-and-named-arguments/optional-null.php -->

When a parameter is optional and has no sensible default, combine both: `?string $email = null`. Left out, it is `null`, and the function handles that case.

## Common mistakes

### A misspelled name

<!-- include: code/17-default-values-and-named-arguments/unknown-name.error.php -->

⚠ The parameter is called `$daysLate`, not `$days`. PHP stops with "Unknown named parameter $days". This is good: a misspelled name can't silently go to the wrong parameter.

**Fix:** use the exact parameter name, without the `$`.

### Passing `null` on

<!-- include: code/17-default-values-and-named-arguments/null-passed-on.error.php -->

⚠ 'Sula' is not in the list, so `authorOf` returns `null`, as it promised with `?string`. `strtoupper` needs a string, and with strict types, `null` is not one: "Argument #1 ($string) must be of type string, null given".

**Fix:** handle `null` where you receive it: `authorOf(...) ?? 'unknown'`, or an `if`. A `?` in a return type is a note to every caller: check this.

### Two more rules

- **Positional after named:** `lateFee(perDay: 20, 3)` is a "Fatal error: Cannot use positional argument after named argument". Put the positional arguments first.
- **An optional parameter before a required one:** in `function lateFee(int $perDay = 50, int $daysLate)`, the default can never be used. PHP says so: "Optional parameter $perDay declared before required parameter $daysLate is implicitly treated as a required parameter". Move the parameters with defaults to the end.

## Principle check

**DRY:** a default value writes the usual case once, in the function, instead of in every call. **YAGNI:** add a parameter, with or without a default, only when a real caller needs it. A function with seven optional parameters "in case" is hard to call and hard to test.

## Security

Nothing new here yet. One habit to start now: a default should be the **safe** choice. When a parameter decides whether something is shown to everyone or checked first, the default is "checked". A caller who forgets it then gets the careful behaviour.

## Shelf

*Shelf* gets a fourth book, and the search an optional author filter: `?string $author = null`. Left out, the search works as before. With `author: 'butler'`, it only looks at books by that author. Change `shelf/shelf.php` to:

<!-- include: code/17-default-values-and-named-arguments/shelf/shelf.php -->

Run it with `php shelf/shelf.php`. The last search has an empty search text: every title contains `''`, so it lists all books by one author.

## Exercises

The exercises are in `exercises/17-default-values-and-named-arguments/`. Check your work with:

```
composer exercise 17 beginner
```

1. **Beginner, `fee.php`:** give the late fee per day a default value.
2. **Intermediate, `slips.php`:** print two loan slips, with defaults and named arguments.
3. **Advanced, `due.php`:** write a function that returns a due date or `null`, and one that turns the answer into a line.

## Summary

- `int $perDay = 50` gives a parameter a default; parameters with defaults come last.
- Named arguments (`withYear: false`) say which parameter gets a value, in any order.
- `?string` allows a string or `null`, as a parameter or as a return type.
- `?string $x = null` makes a parameter optional when there is no sensible default.
- A function that may return `null` asks every caller to handle it, often with `??`.

## Further reading

- PHP manual, Default parameter values: https://www.php.net/manual/en/functions.arguments.php#functions.arguments.default
- PHP manual, Named arguments: https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments
- PHP manual, Nullable types: https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.nullable
- PHP manual, `number_format`: https://www.php.net/manual/en/function.number-format.php
