# 16. Types on functions and `strict_types`

## Goals

- You will be able to give parameters and return values a type.
- You will be able to explain what `declare(strict_types=1);` changes, and why every file in this course starts with it from now on.
- You will be able to describe what an array holds, in a PHPDoc comment.
- You will be able to read a `TypeError` and find the line that caused it.

## Before you start

- Chapter 15: you can write functions with parameters and `return`.
- Chapter 7: you know the types int, float, string, bool and null, and that PHP converts between them on its own.

## The idea

In chapter 15, `lateFee($daysLate)` accepted anything: a number, a text, a list. Nothing in the code said that `$daysLate` must be a whole number, so a wrong value went in quietly and gave a wrong answer later, somewhere else.

A **type declaration** writes the rule into the function's first line: `int $daysLate` says "this parameter is an int", and `: int` after the brackets says "this function returns an int". PHP checks both on every call. A wrong value stops the program with a `TypeError`, at the call that passed it.

There is one catch. By default, PHP still tries to convert a wrong value first, as it did in chapter 7: the string `'3'` becomes the int 3 without a word. The line `declare(strict_types=1);` at the top of a file turns that off. With it, PHP accepts only the declared type. There is one exception: an int is accepted where a float is expected, because no information is lost.

From this chapter on, every file in this course starts with `declare(strict_types=1);`, and `tools/verify` checks it.

## In code

### A function with types

<!-- include: code/16-types/typed.php -->

- `declare(strict_types=1);` comes first, right after `<?php`. It applies to the calls written in this file.
- `int $daysLate`: the type goes before the parameter.
- `: int`: the return type goes after the brackets, before the body.

The first line of the function now tells you everything you need to call it: give it an int, get back an int.

### `strict_types` at work

<!-- include: code/16-types/strict.error.php -->

⚠ `'3'` is a string. With strict types, PHP refuses it: "Argument #1 (`$daysLate`) must be of type int, string given". The message names the function, the parameter and both types. It also names two places: the line of the call (11), which is usually where the mistake is, and the line where the function is defined (5).

Without `strict_types`, this call would return 150, and the problem would be hidden until a string that is not a number came along.

### The types you know, on functions

<!-- include: code/16-types/types.php -->

- Every type from chapter 7 can be a parameter or a return type: `int`, `float`, `string`, `bool`. Here: `int` in, `float` or `bool` out.
- `void` means "returns nothing". It fits functions that are called for what they do, such as printing.
- `pricePerPage` returns a float: 1299 / 320 is not a whole number.

### Either of two types

<!-- include: code/16-types/union.php -->

`int|float` is a **union type**: either type is fine. Use it when both really are correct, as for an amount that may come with a fraction. When you write `string|int|float|bool` to make a problem go away, the function does too many things.

### What is inside an array

<!-- include: code/16-types/array-types.php -->

The type `array` says that a value is an array, but not what is in it. The comment above the function adds that:

- A comment that starts with `/**` is a **PHPDoc** comment. PHP ignores it, but editors and checking tools read it.
- `@param list<string> $titles` says: `$titles` is a list of strings.
- For a map with known keys, write its shape: `array{title: string, year: int}`. *Shelf* below does that for a book.

`tools/verify` runs PHPStan, a tool that reads your code without running it and finds mistakes such as a string passed where an int is expected. From this chapter on, it checks the course's examples with these comments, at a stricter level than before.

## Common mistakes

### Returning the wrong type

<!-- include: code/16-types/return-type.error.php -->

⚠ For 0 days, the function returns the text `'free'`, but it promised an int. The `TypeError` points at the `return` on line 8.

**Fix:** return a value of the declared type: here `0`. Let the code that prints the fee decide to show the word "free".

### A path without `return`

<!-- include: code/16-types/missing-return.error.php -->

⚠ For 3 days, the function returns 150. For 0 days, the `if` is false, the body ends, and nothing is returned: "Return value must be of type int, none returned". Without the return type, it would have returned `null` silently.

**Fix:** make sure every way through the function ends with a `return`. Here: `return 0;` after the `if`.

### Types written only in a comment

A PHPDoc comment such as `@param int $daysLate` is not checked by PHP when the program runs. Only the type in the code is.

**Fix:** write the type in the code (`int $daysLate`). Use PHPDoc only for what the code can't say, such as what an array holds.

## Principle check

**Secure by default:** strict types turn a quiet wrong value into a loud error at the place where it happens. That is failing fast, a theme of the next chapters. **KISS:** a typed first line is the shortest documentation a function can have, and it can't go out of date.

## Security

Everything that comes from outside your program arrives as a string: typed in, read from a file, sent by a form. Strict types make you convert it on purpose, at the point where it comes in. That point is where it should also be checked, a rule you will use from chapter 24 on.

## Shelf

*Shelf* gets `declare(strict_types=1);`, a type on every parameter and return value, and a PHPDoc shape for a book: `array{title: string, author: string, year: int, copies: int}`. The output stays the same. Change `shelf/shelf.php` to:

<!-- include: code/16-types/shelf/shelf.php -->

The shape is written four times. That is a repetition you can't remove with what you know yet: in Part 5, a `Book` class replaces it.

## Exercises

The exercises are in `exercises/16-types/`. Check your work with:

```
composer exercise 16 beginner
```

1. **Beginner, `add-types.php`:** add parameter and return types to two working functions.
2. **Intermediate, `fix-input.php`:** a number arrives as text; convert it where it comes in.
3. **Advanced, `average.php`:** write a typed function for the average of a list, with a PHPDoc comment for the list.

## Summary

- `int $x` types a parameter, `: int` types the return value; `void` means nothing is returned.
- `declare(strict_types=1);` stops PHP from converting wrong values; this course uses it in every file from now on.
- A `TypeError` names the function, the parameter, both types and the line of the call.
- `int|float` accepts either type; use it only when both are correct.
- A PHPDoc comment says what an array holds: `@param list<string> $titles`.

## Further reading

- PHP manual, Type declarations: https://www.php.net/manual/en/language.types.declarations.php
- PHP manual, Strict typing: https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict
- PHP manual, `declare`: https://www.php.net/manual/en/control-structures.declare.php
- PHPStan, PHPDoc types: https://phpstan.org/writing-php-code/phpdoc-types
