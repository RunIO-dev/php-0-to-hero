# 19. Errors and exceptions

## Goals

- You will be able to stop a function with `throw` when it can't do its job.
- You will be able to handle an exception with `try` and `catch`, and let the program go on.
- You will be able to define your own kind of exception, such as `BookNotFound`.
- You will be able to explain the difference between a warning, an exception and an error, and why this course fails fast.

## Before you start

- Chapter 17: you can write functions with types, and you know `null` as a "no value" answer.
- Chapter 14: you know `array_search` and its `false` for "not found".

## The idea

Some functions can't always do their job. `positionOf($titles, 'Sula')` is asked for a book that isn't there. There is no position to return. What should it do?

Returning a special value, such as `false` or `-1`, has a weakness: the caller must remember to check it. If they don't, the special value travels on and causes a wrong result far away from the real problem. You saw this with `array_search` in chapter 14.

An **exception** is PHP's way to say "I can't do this" loudly. `throw` stops the function at once and sends an exception object up to the code that called it. That code can **catch** it with `try` and `catch` and decide what to do: show a message, try something else, or skip one item. If nobody catches it, the program stops with a "Fatal error: Uncaught ..." message. It doesn't go on with a wrong value.

That is called **failing fast**: notice a problem as early as possible, and stop there, rather than go on and fail later, in a place that has nothing to do with the cause.

## In code

### `throw`

<!-- include: code/19-errors-and-exceptions/throw.error.php -->

⚠ `throw new RuntimeException("...")` creates an exception with a message and throws it. The function ends there, and so does the program, because nobody catches it. The last `echo` is never reached.

The output reads like the errors you have seen: the kind of exception, its message, the file and line where it was thrown (10), and a **stack trace**. The stack trace lists the calls that led there, newest first: `positionOf(Array, 'Sula')` was called on line 18.

`RuntimeException` is a kind of exception built into PHP, for problems that can only be found while the program runs.

### `try` and `catch`

<!-- include: code/19-errors-and-exceptions/try-catch.php -->

- The code in the `try` block runs as usual. When something in it throws, PHP jumps straight to the matching `catch` block. The rest of the `try` block is skipped.
- `catch (RuntimeException $e)` catches exceptions of that kind, and puts the exception into `$e`. `$e->getMessage()` gives its message.
- After the `catch` block, the program goes on as normal.

### Your own kind of exception

<!-- include: code/19-errors-and-exceptions/own-exception.php -->

`final class BookNotFound extends RuntimeException {}` defines a new kind of exception, named after the problem. The line is a **class**, which Part 5 explains. For now, read it as: "a `BookNotFound` is a `RuntimeException` with its own name".

The name lets the caller catch exactly this problem: `catch (BookNotFound $e)` handles a missing book, and lets every other exception pass on. Here the `try` is inside the loop, so a missing title only ends its own round.

### Checking the input first

<!-- include: code/19-errors-and-exceptions/fail-fast.php -->

`InvalidArgumentException` is the built-in kind for "this argument can't be right". The checks come first, before any work: a **guard clause**. 0 days would otherwise reach the division and stop with a less helpful error. The message says what was wrong and which value arrived.

### `finally`

<!-- include: code/19-errors-and-exceptions/finally.php -->

A `finally` block runs after `try`, whether something was thrown or not. Use it for what must happen in every case, such as closing a file (chapter 22).

### Warnings, exceptions and errors

<!-- include: code/19-errors-and-exceptions/php-errors.error.php -->

PHP reports problems in three ways:

- A **warning**, such as "Undefined array key", is printed, and the program goes on with a fallback value. Here the second title is `null`, printed as nothing.
- An **exception** (`RuntimeException`, `InvalidArgumentException`, your own) is for problems your program can expect and handle: a missing book, wrong input.
- An **error** (`TypeError`, `DivisionByZeroError`, `ArgumentCountError`) is thrown by PHP itself for a bug in the code. It can be caught, as here, but the right fix is to change the code. Don't catch errors to hide them.

## Common mistakes

### An empty `catch`

<!-- include: code/19-errors-and-exceptions/swallowed.php -->

The `catch` block catches the exception and does nothing with it. The program says "Lent", although nothing was lent. This is called **swallowing** an exception, and it hides exactly the problem the exception was there to show.

**Fix:** in every `catch`, do something: tell the user, write it down, or try something else. If there is nothing sensible to do, don't catch the exception at all.

### Returning `false` instead of throwing

<!-- include: code/19-errors-and-exceptions/false-instead.error.php -->

⚠ `findBook` returns `false` when the book is missing, and the caller forgets to check. PHP warns "Trying to access array offset on false", and the year prints as nothing. The real problem, a missing book, is never named.

**Fix:** throw an exception with a clear message. The caller who forgets to handle it gets a fatal error that says what is wrong, not a quiet empty value.

### Catching too much

`catch (Throwable $e)` catches everything, also the `TypeError` from a bug you haven't found yet. Your program then handles a bug as if it were a missing book.

**Fix:** catch the narrowest kind that fits: `BookNotFound`, not `RuntimeException`, and never `Throwable` to make a problem go away. To handle two kinds the same way, list both: `catch (BookNotFound|NoCopyLeft $e)`.

## Principle check

**Secure by default:** failing fast means a program never goes on with data it knows to be wrong. **KISS:** a guard clause at the start of a function is easier to check than a condition hidden in the middle. Each guard says one thing that must be true.

## Security

What an exception message says is shown to whoever sees the output. In the command line, that is you. On a website, it would be a visitor, and a message with file paths or database details helps an attacker. From Part 7 on, the visitor sees a general message, and the details go to a log file that only you read.

## Shelf

*Shelf* can now lend a book. `findBook` throws `BookNotFound` for a title that isn't there, and `lend` throws `NoCopyLeft` when all copies are out. The loop catches both, so every request gets an answer. In `shelf/shelf.php`, the two exceptions are new, and `findBook`, `lend` and the loop take the place of `searchTitles`, `searchLine` and the searches; the books, `statusOf` and `bookLine` stay as they are:

<!-- include: code/19-errors-and-exceptions/shelf/shelf.php lines 1-10, 30-75 -->

Run it with `php shelf/shelf.php`. Kitchen had one copy, and now shows as on loan. Kindred was asked for in small letters and found, because `findBook` compares in any case.

## Exercises

The exercises are in `exercises/19-errors-and-exceptions/`. Check your work with:

```
composer exercise 19 beginner
```

1. **Beginner, `guard.php`:** make a function refuse impossible input with an `InvalidArgumentException`.
2. **Intermediate, `find-member.php`:** find a member by email, or throw `MemberNotFound`.
3. **Advanced, `returns.php`:** one bad return stops the program; catch it, so that every return is handled.

## Summary

- `throw` stops a function and sends an exception up to its caller; uncaught, it stops the program.
- `try`/`catch` handles an exception and lets the program go on; `finally` runs in every case.
- Name your own exceptions after the problem (`BookNotFound`), and catch the narrowest kind.
- Warnings go on with a wrong value; exceptions are for expected problems; errors mean a bug.
- Fail fast: check input first, and never swallow an exception in an empty `catch`.

## Further reading

- PHP manual, Error handling: https://www.php.net/manual/en/language.error-handling.php
- PHP manual, Exceptions and errors (`Throwable`): https://www.php.net/manual/en/language.error-handling.throwable.php
- PHP manual, SPL exceptions: https://www.php.net/manual/en/spl.exceptions.php
- PHP manual, `RuntimeException`: https://www.php.net/manual/en/class.runtimeexception.php
