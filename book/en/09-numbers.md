# 9. Numbers and arithmetic

## Goals

- You will be able to calculate with `+`, `-`, `*`, `/`, `%` and `**`, and know the order PHP calculates in.
- You will be able to round numbers up, down and to the nearest value, and print them in a readable form.
- You will be able to explain why `0.1 + 0.2` is not exactly 0.3.
- You will be able to calculate with money safely, in cents.

## Before you start

- Chapter 7: you know the types `int` and `float`, and you can check a value with `var_dump`.

## The idea

Programs calculate all the time: how many days a loan has left, how many copies are on the shelf, how much a late return costs. PHP calculates with the usual signs, called **operators**, and in the order you know from school: multiplication and division before addition and subtraction.

There is one catch that surprises every programmer once. Computers store floats in binary, with a fixed number of digits, so most decimal numbers are stored as a very close neighbour, not exactly. For a page count that doesn't matter. For money it does, and the fix is simple: count money in cents, as whole numbers.

## In code

### The operators

<!-- include: code/09-numbers/arithmetic.php -->

- `+`, `-` and `*` add, subtract and multiply.
- `/` divides. The result is a float when it has decimals: 264 pages at 30 a day take 8.8 days.
- `intdiv` divides and keeps only the whole part: 8 full days.
- `%` (modulo) gives what is left over after a whole division: after 8 days of 30 pages, 24 pages are left.
- `**` raises to a power: 2 to the power of 10 is 1024.

`intdiv` and `%` belong together: one gives the whole part, the other the rest.

### The order of calculation

<!-- include: code/09-numbers/order.php -->

- `2 + 3 * 4` is 14, not 20: `*` comes first, as in school mathematics. Brackets change the order: `(2 + 3) * 4` is 20.
- `$copies += 2;` is short for `$copies = $copies + 2;`. `-=`, `*=` and `/=` work the same way.
- `$copies++;` adds one. `$copies--;` subtracts one.

When you are not sure about the order, add brackets. They cost nothing, and the next reader doesn't have to think about it either.

### Rounding

<!-- include: code/09-numbers/rounding.php -->

- `round` rounds to the nearest whole number, or to a number of decimals: `round($days, 1)` is 8.8.
- `floor` always rounds down, and `ceil` (ceiling) always rounds up. If Amira reads 30 pages a day, she needs `ceil(8.8)`, 9 days, to finish: the last day is a short one, but it is a day.
- `number_format` prints a number with a thousands separator and a fixed number of decimals. It returns a string, for printing.

### Floats are close, not exact

<!-- include: code/09-numbers/floats.php -->

`echo` rounds a float to 14 digits before printing, so you see 0.3. `var_dump` shows the stored value: 0.30000000000000004. The tiny difference comes from how floats are stored in binary, and it happens in every programming language, not only in PHP.

For measurements, such as a rating of 4.5, the difference never matters. For money it does: add up thousands of prices, and cents start to appear or disappear.

### Money in cents

<!-- include: code/09-numbers/cents.php -->

Store money as a whole number of cents, in an `int`. Ints are exact, so `25 * 3` is exactly 75. Only when you print the amount do you turn it into the usual form, with two decimals.

The `Cents` at the end of `$feePerDayCents` is there on purpose: the name tells every reader the unit.

## Common mistakes

### Dividing by zero

<!-- include: code/09-numbers/division-by-zero.error.php -->

Dividing by zero has no answer, so PHP stops with a `DivisionByZeroError`. `intdiv` and `%` do the same.

**Fix:** make sure the number you divide by can't be zero. In this course, chapter 11 shows how to check a value before you use it.

### Forgetting the order of calculation

<!-- include: code/09-numbers/average.php -->

The average of 4 and 5 is 4.5, but the first line prints 6.5. `/` is calculated before `+`, so PHP calculates `4 + (5 / 2)`. There is no error message: the number is simply wrong.

**Fix:** put brackets around what belongs together: `($ratingAmira + $ratingJonas) / 2`.

### Money in floats

`0.1 + 0.2` in the example above is not exactly 0.3. A program that adds prices as floats can be off by a cent, and nothing warns you.

**Fix:** calculate in cents, with ints, as in "Money in cents".

## Principle check

**KISS:** ints for money is the simplest correct solution. There is no rounding to think about while you calculate, and the conversion to "1.40" happens in one place, where the amount is printed.

## Security

Nothing new here. Wrong money calculations are a bug, not a security hole, but they cost real money: test them well. Chapter 41 shows how.

## Shelf

Nothing changes in this chapter. Loans with due dates come to *Shelf* in chapter 21; anything it charges for a late return is a calculation in cents, as in this chapter.

## Exercises

The exercises are in `exercises/09-numbers/`. Check your work with:

```
composer exercise 09 beginner
```

1. **Beginner, `reading-plan.php`:** how many days does a book take, and how many pages are left for the last day?
2. **Intermediate, `late-fee.php`:** calculate a late fee in cents, and print it with two decimals.
3. **Advanced, `share.php`:** split a cost of 1000 cents between three people, so that it adds up exactly.

## Summary

- `+ - * /` calculate as in school, `*` and `/` first; brackets change the order.
- `intdiv` gives the whole part of a division, `%` the rest.
- `round`, `floor` and `ceil` round to the nearest, down and up; `number_format` prints a number readably.
- Floats are close, not exact: `0.1 + 0.2` is 0.30000000000000004.
- Count money in cents, as an `int`, and format it only when you print it.

## Further reading

- PHP manual, Arithmetic operators: https://www.php.net/manual/en/language.operators.arithmetic.php
- PHP manual, Operator precedence: https://www.php.net/manual/en/language.operators.precedence.php
- PHP manual, Floating point numbers: https://www.php.net/manual/en/language.types.float.php
- PHP manual, `number_format`: https://www.php.net/manual/en/function.number-format.php
