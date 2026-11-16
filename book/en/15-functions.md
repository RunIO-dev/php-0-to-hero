# 15. Functions

## Goals

- You will be able to write your own functions, with parameters and a return value.
- You will be able to explain the difference between a function that returns a value and one that prints it.
- You will be able to split a task into small functions with one job each.
- You will be able to read the error PHP gives when a call has too few arguments.

## Before you start

- Chapter 14: you have seen the same loop written twice, and the rule of three.
- Chapter 11: you can use `if` and the ternary operator.

## The idea

You have used many functions already: `strlen`, `count`, `round`, `implode`. Each one has a name, takes some values, and does one job. You don't need to know how `count` counts; you call it and use the answer.

A **function** of your own works the same way. You give a block of code a name, once. Then you call it by that name wherever you need it, as often as you need it. When the code inside has to change, it changes in one place.

Three words come with functions:

- A **parameter** is a variable in the function's first line. It gets its value from the call.
- An **argument** is the value you pass in the call. In `lateFee(3)`, 3 is the argument for the parameter `$daysLate`.
- The **return value** is what the function hands back to the code that called it.

## In code

### Defining and calling a function

<!-- include: code/15-functions/define.php -->

- `function printRule()` starts the definition: the word `function`, a name, and brackets. The block in curly braces is the function's **body**.
- Defining a function runs nothing. The body runs each time the function is called: `printRule();`.
- Function names follow the same style as variables: camelCase, and a verb that says what the function does.

### Parameters

<!-- include: code/15-functions/parameters.php -->

`$name` is a parameter. In the first call, it holds `'Amira'`; in the second, `'Jonas'`. With several parameters, the arguments are matched by their order: the first argument goes to the first parameter.

### Returning a value

<!-- include: code/15-functions/return.php -->

- `return` hands a value back. The call `lateFee(3)` becomes that value, 150, which is stored in `$fee`.
- A call is an expression, so it can go wherever a value can: in a calculation, in `echo`, or as an argument to another function.

Most functions in this course return a value and print nothing. The code that calls them decides what to do with the result: print it, add it up, or save it. That keeps each function useful in more places.

### `return` ends the function

<!-- include: code/15-functions/early-return.php -->

`return` also leaves the function at once. When `$daysLeft` is below 0, the first `return` runs, and the rest of the body is skipped. This is often clearer than a long `if`/`elseif`/`else`: each special case is handled and done, and the normal case comes last.

### Small functions that work together

<!-- include: code/15-functions/small.php -->

`bookLine` builds a line, and leaves the decision "available or not" to `statusOf`. Each function has one job, and each name says what that job is. When the library changes the wording of the status, only `statusOf` changes.

How small is small? This course aims for functions under about 20 lines that you can describe in one sentence without the word "and". A function that "checks the input and calculates the fee and prints the receipt" is three functions.

## Common mistakes

### Too few arguments

<!-- include: code/15-functions/too-few.error.php -->

⚠ `lateFee` has one parameter, but the call passes nothing. PHP stops with an `ArgumentCountError`. The message names the function, says how many arguments were passed (0) and how many were expected (exactly 1), and gives two line numbers: where the call is (line 8), and where the function is defined (line 3).

**Fix:** pass a value for every parameter, in the right order. Chapter 17 shows how a parameter can have a default value, so that it can be left out.

### Printing instead of returning

<!-- include: code/15-functions/echo-not-return.php -->

This function prints the fee with `echo` and returns nothing. So 150 appears at the moment of the call, before "Fee:", and `$fee` holds `null`, which prints as nothing: "Fee:  cents".

**Fix:** `return` the value, and print it where it is used. A function without `return` gives back `null`.

### Defining the same function twice

A function name can be defined only once. A second `function lateFee(...)` stops the program with "Cannot redeclare function lateFee()". PHP has no way to know which of the two you mean.

**Fix:** give each function its own name, or change the one you have.

## Principle check

**DRY:** in chapter 14, the search loop was written once, and the rule of three said: a third copy means it's time for a function. Here it is. **KISS:** small functions with clear names make the main part of a program read like a list of steps.

## Security

Nothing new here yet. Later, functions are where checks live: one function that validates an email address, called everywhere an email comes in, can't be forgotten in one place the way a copied `if` can.

## Shelf

*Shelf*'s work moves into functions: one for a book's status, one for its line, one for the search, and one for the search's line. The main part at the end now reads like a description of the program, and the search runs three times without a copied loop. Change `shelf/shelf.php` to:

<!-- include: code/15-functions/shelf/shelf.php -->

Run it with `php shelf/shelf.php`. Add a fourth search: it takes one line.

## Exercises

The exercises are in `exercises/15-functions/`. Check your work with:

```
composer exercise 15 beginner
```

1. **Beginner, `label.php`:** finish a function that returns a shelf label.
2. **Intermediate, `reading-days.php`:** write a function that says how many days a book takes to read.
3. **Advanced, `three-books.php`:** the same line is written three times; turn it into a function and use it three times.

## Summary

- A function is a named block of code; it runs each time it is called.
- Parameters receive the call's arguments, in order. Too few arguments stop the program with `ArgumentCountError`.
- `return` hands a value back and ends the function; without it, the function returns `null`.
- Prefer functions that return a value over functions that print it.
- Keep functions small, with one job and a name that says what it is.

## Further reading

- PHP manual, Functions: https://www.php.net/manual/en/language.functions.php
- PHP manual, User-defined functions: https://www.php.net/manual/en/functions.user-defined.php
- PHP manual, Function arguments: https://www.php.net/manual/en/functions.arguments.php
- PHP manual, Returning values: https://www.php.net/manual/en/functions.returning-values.php
