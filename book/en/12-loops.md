# 12. Loops

## Goals

- You will be able to repeat code with `while`, `for` and `foreach`, and choose the right one.
- You will be able to read a loop step by step, writing down what each variable holds.
- You will be able to leave a loop early with `break`, and skip a round with `continue`.
- You will be able to spot the two classic loop mistakes: a loop that never ends, and a loop that runs once too often or too rarely.

## Before you start

- Chapter 11: you can write conditions and use `if`.

## The idea

Computers are good at doing the same thing many times. Print a label for every shelf, go through every book in a list, read day after day until the book is finished. Writing the same line a hundred times would work for a hundred shelves, and break for a hundred and one.

A **loop** runs a block of code again and again, as long as a condition is true. Each run is called an **iteration**, or a round. PHP has three loops, for three common situations:

- `while`: repeat as long as a condition is true, when you don't know in advance how many rounds it takes.
- `for`: count from one number to another.
- `foreach`: go through every item of a list.

## In code

### `while`

<!-- include: code/12-loops/while.php -->

`while` checks its condition, runs the block, and checks again. When the condition is false, PHP goes on after the loop.

Something in the block must change the condition, or the loop never ends. Here, every round subtracts a day's pages from `$pagesLeft`. `max(0, ...)` gives the larger of two numbers, so the count stops at 0 instead of going below it.

### `for`

<!-- include: code/12-loops/for.php -->

`for` puts everything that counting needs into one line, in three parts separated by semicolons:

1. `$shelf = 1`: runs once, before the first round.
2. `$shelf <= 4`: checked before every round. When it is false, the loop ends.
3. `$shelf++`: runs after every round.

It does the same as a `while` with a counter, but the counting can't be forgotten, because it is all in the first line.

### `foreach`

<!-- include: code/12-loops/foreach.php -->

`['Kindred', 'Beloved', 'Kitchen']` is a **list**: several values in one variable, in square brackets. Chapter 13 explains lists properly; here you only need to go through one.

`foreach` takes the items one by one: in each round, `$title` holds the next item. There is no counter and no condition to get wrong, which makes `foreach` the loop you will use most.

### Reading a loop step by step

<!-- include: code/12-loops/trace.php -->

When a loop does something unexpected, read it like the computer does, round by round, and write down what each variable holds at the end of each round:

| Round | `$day` | `$minutes` | `$totalMinutes` |
|---|---|---|---|
| before | | | 0 |
| 1 | 1 | 10 | 10 |
| 2 | 2 | 20 | 30 |
| 3 | 3 | 30 | 60 |
| 4 | 4 | 40 | 100 |

After round 4, `$day++` makes it 5, the condition `5 <= 4` is false, and the loop ends. This kind of table is called a **trace**. It takes a minute, and it finds most loop bugs. The `echo` inside the loop does the same on screen.

### `break` and `continue`

<!-- include: code/12-loops/break-continue.php -->

- `continue` skips the rest of the round and goes on with the next one. Day 2 is a closed day, so nothing else happens on it.
- `break` leaves the loop at once. Once the book can be borrowed, there is no need to look at the other days.

Use them sparingly. A loop whose end is in its first line is easier to read than one that ends somewhere in the middle.

## Common mistakes

### A loop that never ends

<!-- include: code/12-loops/endless.bad.php -->

✗ Nothing in this loop changes `$copies`, so `$copies > 0` stays true, and the program prints "Lending a copy" until you stop it. This is an **endless loop** (or infinite loop). Press `Ctrl+C` in the terminal to stop the program.

**Fix:** make sure every round brings the loop closer to its end. Here: `$copies--;` inside the block. With `for` and `foreach`, this mistake is much rarer, one more reason to prefer them.

### Once too often, or once too rarely

<!-- include: code/12-loops/off-by-one.php -->

There are 4 shelves, but only 3 labels: `$shelf < $shelves` is false when `$shelf` is 4. This is called an **off-by-one error**, and every programmer makes it.

**Fix:** check the first and the last round. Starting at 1 and going up to 4 including 4 needs `<=`. A quick trace of the last round shows it.

## Principle check

**DRY (don't repeat yourself):** a loop is the first tool against repetition. When you notice the same lines with only a number or a title changing, they are one block in a loop.

## Security

A loop over data from outside, such as a file or a form, can be as long as the data. Later chapters set limits on input, so that a very large input can't keep your program busy for hours.

## Shelf

Nothing changes in this chapter. *Shelf* still holds one book. In chapter 13 it gets a list of books, and `foreach` prints them all.

## Exercises

The exercises are in `exercises/12-loops/`. Check your work with:

```
composer exercise 12 beginner
```

1. **Beginner, `labels.php`:** print a label for each shelf, with `for`.
2. **Intermediate, `plan.php`:** print a reading plan day by day, with `while`, until the book is finished.
3. **Advanced, `reading-list.php`:** number a list of titles with `foreach`, skip the empty ones, and say so when there are none.

## Summary

- `while` repeats while a condition is true; something in the block must change it.
- `for` counts, with start, condition and step in one line.
- `foreach` goes through every item of a list; it is the loop you will use most.
- `continue` skips to the next round, `break` leaves the loop.
- Trace a loop round by round to find endless loops and off-by-one errors.

## Further reading

- PHP manual, `while`: https://www.php.net/manual/en/control-structures.while.php
- PHP manual, `for`: https://www.php.net/manual/en/control-structures.for.php
- PHP manual, `foreach`: https://www.php.net/manual/en/control-structures.foreach.php
- PHP manual, `break` and `continue`: https://www.php.net/manual/en/control-structures.break.php
