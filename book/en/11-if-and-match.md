# 11. `if`, `else` and `match`

## Goals

- You will be able to run code only when a condition is true, with `if`, `elseif` and `else`.
- You will be able to choose a value from a list of cases with `match`.
- You will be able to use the short forms `?:` and `??` where they make code clearer.
- You will be able to read the error PHP gives when `match` finds no case.

## Before you start

- Chapter 10: you can write conditions with `===`, `<`, `&&` and `||`.

## The idea

So far, every program ran from the first line to the last, every time. Real programs choose: a book that is on loan shows another message than one on the shelf, and a late return costs a fee while an early one doesn't.

Code that chooses has two tools in PHP:

- `if` runs a block of code only when a condition is true. With `else`, another block runs when it is false.
- `match` picks one value out of several cases, like a table: this genre goes on that shelf.

Use `if` to decide what to **do**, and `match` to decide which **value** to use. Many decisions can be written either way, and the examples show which one reads better where.

## In code

### `if`

<!-- include: code/11-if-and-match/if.php -->

- The condition goes in brackets after `if`. When it is true, the code between the curly braces `{ }` runs. This code is called a **block**.
- When the condition is false, PHP skips the block and goes on after the closing brace. "Done" is printed either way.
- The lines inside the block are indented by four spaces. PHP doesn't need that, but people do: the indentation shows at a glance what belongs to the `if`.

### `elseif` and `else`

<!-- include: code/11-if-and-match/if-else.php -->

PHP checks the conditions from top to bottom and runs the **first** block whose condition is true. Then it skips the rest. `else` has no condition: its block runs when none of the others did.

Exactly one of the three messages is printed, whatever the number of copies.

### `match`

<!-- include: code/11-if-and-match/match.php -->

- `match` compares the value in brackets with each case on the left of `=>`, and gives back the value on the right of the first case that fits.
- It compares with `===`, as in chapter 10: the string `'sf'` fits, the same text with another type would not.
- Several cases can share one result: `'kids', 'teens' => 'Young readers'`.
- `default` is the case for everything else.
- The whole `match (...) { ... }` is one value, which is stored in `$shelfName`. Note the semicolon after the closing brace.

Compared with an `if` chain that sets `$shelfName` in every branch, the `match` shows the table and nothing else.

### `match (true)` for ranges

<!-- include: code/11-if-and-match/match-true.php -->

`match` can also check conditions. With `match (true)`, each case is a condition, and the first one that is `true` fits. This is a clear way to write ranges, such as "up to 7 days late", "more than that".

The order matters, as with `elseif`: 9 days is not `<= 0` and not `<= 7`, so the `default` fits.

### Two short forms

<!-- include: code/11-if-and-match/short-forms.php -->

- `condition ? a : b` (the **ternary operator**) is a short `if`/`else` that gives a value: `a` when the condition is true, `b` when it is false. Use it for small choices between two values, and keep it on one line.
- `$value ?? 'fallback'` (the **null coalescing operator**) gives `$value`, or the fallback when `$value` is `null`. It is the shortest way to show something sensible for "no value yet".

## Common mistakes

### `=` in a condition

<!-- include: code/11-if-and-match/assign-in-if.php -->

The code meant "if there are no copies", but `$copies = 0` assigns 0, as in chapter 10. The result, 0, counts as false, so the `else` branch runs, and `$copies` is now 0 as well. Two bugs from one comparison written as an assignment.

**Fix:** compare with `===`: `if ($copies === 0)`.

### A `match` without a fitting case

<!-- include: code/11-if-and-match/unhandled.error.php -->

No case fits `'poetry'`, and there is no `default`. `match` doesn't guess: it stops with an `UnhandledMatchError` that names the value. That is better than going on with no value, but the program still stopped.

**Fix:** add the missing case, or a `default` for everything you don't list.

### Conditions in the wrong order

With `elseif` and `match (true)`, the first condition that is true wins. If `$daysLate <= 7` came before `$daysLate <= 0` in `match-true.php`, a book returned early would get a friendly email.

**Fix:** go from the narrowest condition to the widest, and test the edges: 0, 7 and 8.

## Principle check

**KISS:** a `match` is a table, and a table is easy to check. When an `if` chain only sets one variable in every branch, a `match` says the same in half the lines. And when a `match` grows past about ten cases, the cases are data; chapter 13 shows how to keep data in an array.

## Security

Nothing new here yet. Later, an `if` will decide who may do what, such as only a librarian deleting a book. Then the important rule will be: check on the server, every time, and let `else` deny.

## Shelf

*Shelf* now shows whether the book is available, with a ternary. Change `shelf/shelf.php` to:

<!-- include: code/11-if-and-match/shelf/shelf.php -->

Run it with `php shelf/shelf.php`, then set `$copies` to 0 and run it again.

## Exercises

The exercises are in `exercises/11-if-and-match/`. Check your work with:

```
composer exercise 11 beginner
```

1. **Beginner, `status.php`:** print "On loan", "Last copy" or "Available", depending on the copies on the shelf.
2. **Intermediate, `shelf-name.php`:** rewrite a repetitive `if` chain as one `match`.
3. **Advanced, `fee.php`:** turn the library's late-fee rule, with a maximum, into code. The tests try many numbers of days.

## Summary

- `if`, `elseif` and `else` run the first block whose condition is true, and skip the rest.
- `match` picks a value by comparing with `===`; `default` covers the rest. Without a fitting case, it throws `UnhandledMatchError`.
- `match (true)` checks conditions in order, which suits ranges.
- `a ? b : c` chooses between two values; `$value ?? 'fallback'` replaces `null`.

## Further reading

- PHP manual, `if`: https://www.php.net/manual/en/control-structures.if.php
- PHP manual, `elseif`: https://www.php.net/manual/en/control-structures.elseif.php
- PHP manual, `match`: https://www.php.net/manual/en/control-structures.match.php
- PHP manual, Ternary and null coalescing operators: https://www.php.net/manual/en/language.operators.comparison.php
