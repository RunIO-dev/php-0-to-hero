# 10. Comparison and logic

## Goals

- You will be able to compare values with `<`, `>`, `<=`, `>=`, `===` and `!==`.
- You will be able to explain why this course compares with `===` and not with `==`.
- You will be able to combine conditions with `&&`, `||` and `!`.
- You will be able to use the order in which PHP checks a condition to avoid an error.

## Before you start

- Chapter 7: you know the type `bool`, and that PHP sometimes converts values by itself.
- Chapter 9: you know the arithmetic operators.

## The idea

Programs decide all the time. May this person borrow a book? Is a copy on the shelf? Is the loan overdue? Each of these is a question with the answer yes or no, so each answer is a `bool`: `true` or `false`.

You get such answers by **comparing** values (is the number of copies greater than 0?), and you combine several answers with **logic** (is the person a member *and* is a copy on the shelf?). This chapter shows both. Chapter 11 then uses the answers to decide what the program does.

## In code

### Comparing numbers

<!-- include: code/10-comparison-and-logic/comparing.php -->

- `>` greater than, `>=` greater than or equal, `<` less than, `<=` less than or equal. Each comparison gives a `bool`.
- `<=>` (the "spaceship" operator) compares and returns a number: -1 when the left side is smaller, 0 when both are equal, and 1 when the left side is greater. You will need it for sorting, in chapter 14.

### Equal: `===` and `!==`

<!-- include: code/10-comparison-and-logic/strict.php -->

`===` is true when both values are equal **and** have the same type. `1958 === '1958'` is false, because one is an int and the other a string. `!==` is the opposite: true when the values differ in value or in type.

Text is compared exactly, letter by letter: `'Kindred'` and `'kindred'` are different, because K and k are different characters.

### Why not `==`

<!-- include: code/10-comparison-and-logic/loose.php -->

PHP also has `==` (and `!=`), the **loose** comparison. Before comparing, it converts the values to a common type, with rules that take a page to describe. The results can be surprising:

- `'1' == '01'` is true: both strings look like numbers, so PHP compares them as numbers.
- `'10' == '1e1'` is true: `1e1` is a way of writing 10 in scientific notation.
- `null == false` is true.

Each rule makes sense on its own, but nobody keeps all of them in their head while reading code. **This course always compares with `===` and `!==`.** They do what they look like they do, and the type is part of the question.

### Comparing text

<!-- include: code/10-comparison-and-logic/text-order.php -->

`<` and `>` on strings compare them letter by letter, by the characters' codes. That is alphabetical for words in the same case: apple comes before banana. But all capital letters come before all small ones, so `'Zebra' < 'apple'`.

When both strings look like numbers, PHP compares them as numbers: `'10' < '9'` is false, because 10 is not less than 9. Add a letter, and they are compared as text again, where 1 comes before 9. Chapter 14 shows how to sort text the way people expect.

### Combining conditions

<!-- include: code/10-comparison-and-logic/logic.php -->

- `&&` (and) is true when both sides are true.
- `||` (or) is true when at least one side is true.
- `!` (not) turns true into false, and false into true.

`$isMember && $copies > 0 && !$hasOverdueBooks` reads almost like the library's rule: a member, a copy on the shelf, and no overdue books. Storing the answer in a well-named variable such as `$canBorrow` makes the rule easy to find and to read.

### Checking from left to right

<!-- include: code/10-comparison-and-logic/short-circuit.php -->

PHP checks `&&` and `||` from left to right, and stops as soon as the answer is clear. When the left side of `&&` is false, the whole answer is false, so the right side never runs. This is called **short-circuit evaluation**.

That makes the order useful: put the check that guards against a problem first. Here, `$people !== 0` comes first, so the division by zero from chapter 9 can't happen.

## Common mistakes

### One `=` instead of `===`

<!-- include: code/10-comparison-and-logic/assign.php -->

`$copies = 0` doesn't compare: it **assigns** 0 to `$copies`, and the result is the assigned value. The original value 2 is gone. There is no error message, and this mistake is easy to overlook when reading.

**Fix:** compare with `===`. When you read `=` alone, read it as "becomes", not "is".

### `and` and `or` instead of `&&` and `||`

<!-- include: code/10-comparison-and-logic/and-or.error.php -->

PHP also has the words `and` and `or`. They work like `&&` and `||`, but they are weaker than `=`. So the first line is read as `($canBorrow = true) and false`: `$canBorrow` becomes true, and the `and false` is calculated and thrown away.

**Fix:** use `&&` and `||`. This course never uses `and` and `or`. Static analysis, which you will meet in chapter 40, reports this line as a mistake before the program ever runs.

### `==` where `===` was meant

The values in `loose.php` above are all different, but `==` says they are equal. With ISBNs or codes that are made of digits, this leads to real bugs.

**Fix:** compare with `===`. If the types differ on purpose, convert one side first, as in chapter 7: `(int) $copiesText === $copies`.

## Principle check

**KISS:** `===` has one rule (same value, same type), and `==` has a table of them. The simple rule is the one that is easy to read correctly. Likewise, a long condition becomes readable when its parts get names: `$canBorrow` instead of three checks in a row.

## Security

`==` has caused real security holes: code that compared a password check or a secret code loosely could be tricked with values like `'0e123'`, which `==` treats as the number 0. Comparing with `===` closes that door. (Passwords themselves are checked differently, with `password_verify`, in chapter 61.)

## Shelf

Nothing changes in this chapter. In chapter 11, *Shelf* uses a comparison to decide whether a book is available.

## Exercises

The exercises are in `exercises/10-comparison-and-logic/`. Check your work with:

```
composer exercise 10 beginner
```

1. **Beginner, `can-borrow.php`:** turn the library's three rules into one `bool`. The tests try it with several people.
2. **Intermediate, `same-book.php`:** two different ISBNs and two different codes come out as equal. Fix the comparisons.
3. **Advanced, `logic.php`:** predict seven answers in `prediction.txt` before you run the file.

## Summary

- `<`, `>`, `<=`, `>=`, `===` and `!==` compare and give a `bool`; `<=>` gives -1, 0 or 1.
- `===` compares value and type. `==` converts first and surprises; this course uses `===`.
- `&&`, `||` and `!` combine conditions. Use them, not `and` and `or`.
- PHP checks from left to right and stops when the answer is clear: put the guard first.

## Further reading

- PHP manual, Comparison operators: https://www.php.net/manual/en/language.operators.comparison.php
- PHP manual, Logical operators: https://www.php.net/manual/en/language.operators.logical.php
- PHP manual, Type comparison tables: https://www.php.net/manual/en/types.comparisons.php
