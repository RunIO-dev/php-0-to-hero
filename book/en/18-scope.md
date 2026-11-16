# 18. Scope, and why there are no globals

## Goals

- You will be able to say which variables a function can see, and which it can't.
- You will be able to pass what a function needs as parameters, and get its results back with `return`.
- You will be able to use constants for values that never change.
- You will be able to explain why this course never uses `global`.

## Before you start

- Chapter 17: you can write typed functions with defaults.
- Chapter 13: you know lists and maps.

## The idea

A function is a room with two doors. Values come in through the parameters, and one value goes out through `return`. What happens inside stays inside: a variable created in a function exists only there, and only while the function runs. And the variables outside are not visible from inside.

Where a variable can be seen is called its **scope**. In PHP, every function has its own scope, and the code outside all functions has another one, the **global scope**.

This can look like a limit, but it is the reason functions are easy to use. To understand `lateFee(3, 50)`, you read its first line and its body, and nothing else. No variable in some other file can change what it does, and it can't change yours by accident.

## In code

### Variables inside a function are local

<!-- include: code/18-scope/local.php -->

`$perDay` is a **local** variable: it is created when `lateFee` runs and gone when it returns. The next call starts again without it.

### The same name, two variables

<!-- include: code/18-scope/same-name.php -->

There are two variables named `$count` here. The function's parameter gets a **copy** of the value 3, counts it down to 0, and the copy disappears at the end. The `$count` outside is still 3. Names don't connect scopes; only parameters and `return` do.

### What a function needs comes in as a parameter

<!-- include: code/18-scope/parameter.php -->

`lateFee` needs the fee per day, so it is a parameter. The caller decides the value, and the function's first line shows everything the function depends on.

### Constants

<!-- include: code/18-scope/constant.php -->

Some values are fixed for the whole program: the fee per day, the length of a loan. A **constant** holds such a value:

- `const FEE_PER_DAY = 50;` defines it, outside all functions. Constant names are written in capitals, with underscores.
- It is used without a `$`, and it can be read anywhere, also inside functions.
- It can never be changed, so reading it anywhere is safe: no code can surprise another with a new value.

### A copy, not the original

<!-- include: code/18-scope/copy.error.php -->

⚠ Arrays are copied too. `withTitle` adds to its own copy, so the first call changes nothing outside: still 2 titles. To keep the result, the caller stores what the function returns: `$titles = withTitle($titles, 'Beloved');`.

PHP gives no message for the first call, but PHPStan does: "Call to function withTitle() on a separate line has no effect". This is why `tools/verify` runs it.

You have seen functions that do change their argument: `sort($titles)` in chapter 14. They use a **reference** (`&$array` in the parameter list), which hands the function the original instead of a copy. The functions in this course don't: a function that returns a new value is easier to follow and to test.

## Common mistakes

### Using a variable from outside

<!-- include: code/18-scope/outside.error.php -->

⚠ `$perDay` is defined outside, so inside `lateFee` it doesn't exist. PHP warns "Undefined variable $perDay", uses `null`, and `3 * null` is 0. The program goes on and prints a fee of 0 cents: a wrong answer, with only a warning to show for it.

**Fix:** pass the value as a parameter, as in `parameter.php`, or make it a constant if it never changes.

### Reaching out with `global`

<!-- include: code/18-scope/global.bad.php -->

✗ `global $perDay;` connects the function to the variable outside. It works, and that is the problem: now any code anywhere can change `$perDay`, and `lateFee` silently changes with it. To understand the function, you need to read the whole program. A function with `global` also can't be tested on its own, because its result depends on something that is not in its call.

`tools/verify` refuses `global` in every file of this course.

**Fix:** a parameter, a return value, or a constant.

### Expecting a change outside

When a function changes its parameter, as `withTitle` does, and the caller doesn't store the result, the change is lost, and PHP says nothing. In `copy.error.php`, the first call does exactly that.

**Fix:** store the result: `$titles = withTitle($titles, 'Beloved');`. When a function returns a value, use it.

## Principle check

**KISS:** a function that uses only its parameters can be read, tested and moved on its own. **Secure by default:** no code can change what a function sees without going through its parameters, so a value can't be swapped from another part of the program.

## Security

`global` is not only a design problem. In very old PHP, a setting turned form fields into global variables, so a visitor could set any variable by adding `?isAdmin=1` to a URL. That setting was removed long ago, but the lesson stays: a function should see only what it is given on purpose.

## Shelf

Nothing changes in this chapter. *Shelf* already passes everything its functions need as parameters, and has no globals. In chapter 19, a missing book becomes an exception.

## Exercises

The exercises are in `exercises/18-scope/`. Check your work with:

```
composer exercise 18 beginner
```

1. **Beginner, `fix-scope.php`:** a function uses a variable from outside; pass it as a parameter.
2. **Intermediate, `members.php`:** write a function that returns a new list with one more member.
3. **Advanced, `counts.php`:** a function tries to change two variables outside; return both numbers in one map instead.

## Summary

- Every function has its own scope; its variables exist only inside, and only while it runs.
- A function sees only its parameters, its own variables and constants.
- Arguments are copies: changing one inside a function changes nothing outside.
- `const NAME = value;` holds a value that never changes and can be read everywhere.
- `global` connects a function to the outside; this course never uses it, and `tools/verify` checks that.

## Further reading

- PHP manual, Variable scope: https://www.php.net/manual/en/language.variables.scope.php
- PHP manual, Constants: https://www.php.net/manual/en/language.constants.php
- PHP manual, Passing by reference: https://www.php.net/manual/en/language.references.pass.php
