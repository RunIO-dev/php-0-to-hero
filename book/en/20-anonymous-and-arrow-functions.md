# 20. Anonymous functions and arrow functions

## Goals

- You will be able to store a function in a variable and pass it to another function.
- You will be able to filter, map and sort arrays with `array_filter`, `array_map` and `usort`.
- You will be able to hand an outside value to a function with `use`, and explain the word "closure".
- You will be able to write short functions with `fn`, and know what they can't do.

## Before you start

- Chapter 18: a function sees only its parameters, its own variables and constants.
- Chapter 14: you can filter and map a list with `foreach`.

## The idea

In chapter 14, filtering and mapping were `foreach` loops that built a new array. Every filter had the same shape: an empty array, a loop, an `if`, and an append. Only the test in the `if` was different.

PHP has functions for that shape: `array_filter` does the loop, and you give it only the test. To pass a test to a function, the test must be a value, and in PHP, a function can be one. An **anonymous function** is a function without a name, written where it is needed, and stored or passed like any other value.

Because of chapter 18, such a function can't see the variables around it. When the test needs an outside value, such as a year, `use` hands it in on purpose. A function that carries outside values with it is called a **closure**: it "closes over" those values.

The **arrow function** is the short form: `fn(array $book): bool => $book['year'] >= $since`. It is one expression, and it sees the outside variables on its own.

## In code

### A function as a value

<!-- include: code/20-anonymous-and-arrow-functions/anonymous.php -->

- `function (string $text): string { ... }` without a name creates a function value, stored in `$shout`. Note the `;` after the closing brace: this is an assignment.
- `$shout('quiet, please')` calls it, like a named function.
- `callable` is the type for "something that can be called". `twice` gets a function and calls it two times.

### `array_filter` and `array_map`

<!-- include: code/20-anonymous-and-arrow-functions/filter-map.php -->

- `array_filter($books, $test)` calls the test with each book, and keeps the books for which it returns `true`. The array comes first.
- `array_map($change, $books)` calls the function with each book, and puts each result into a new array. Here the function comes first. The two orders are a known oddity of PHP; the manual shows each one.
- Neither changes `$books`: both return a new array.

### `use`: a closure

<!-- include: code/20-anonymous-and-arrow-functions/use.php -->

The test needs `$since`, which is outside. `use ($since)` after the parameters passes it in. Like an argument, it is a copy of the value, taken at the moment the function is created.

### Arrow functions

<!-- include: code/20-anonymous-and-arrow-functions/arrow.php -->

- `fn(array $book): bool => $book['year'] >= $since` is an **arrow function**. After `=>` comes one expression, and its value is returned, without `return`.
- It sees `$since` without `use`: an arrow function takes copies of the outside variables it uses.
- Use `fn` for short tests and changes, which is most of them. For several lines, write a normal anonymous function.

### Sorting with your own rule

<!-- include: code/20-anonymous-and-arrow-functions/usort.php -->

- `usort` sorts in place, like `sort`, but asks your function which of two items comes first. The function returns a negative number when `$a` comes first, a positive one when `$b` does, and 0 when they are equal.
- `<=>` (the **spaceship operator**) gives exactly that: -1, 0 or 1. `$b['year'] <=> $a['year']` puts the newest first; swap `$a` and `$b` for the oldest first.
- `strcasecmp` compares two strings in any case, and returns a number in the same way.

## Common mistakes

### The keys after `array_filter`

<!-- include: code/20-anonymous-and-arrow-functions/keys.error.php -->

⚠ `array_filter` keeps each item's key. The two available books keep their keys 0 and 2, so there is no key 1: "Undefined array key 1". The `count` is right, which makes the mistake easy to miss.

**Fix:** when you need a list with the keys 0, 1, 2, wrap the result in `array_values`: `array_values(array_filter(...))`. `foreach` doesn't care about the keys, so it works either way.

### Forgetting `use`

<!-- include: code/20-anonymous-and-arrow-functions/no-use.error.php -->

⚠ The anonymous function can't see `$since`, so PHP warns once per book, uses `null`, and every year is `>= null`. All 3 books pass the test: a wrong result, not a stop.

**Fix:** add `use ($since)`, or write the test as an arrow function.

### Changing an outside value from an arrow function

<!-- include: code/20-anonymous-and-arrow-functions/arrow-copy.error.php -->

⚠ The arrow function adds 1 to its **copy** of `$count`. The `$count` outside stays 0. PHP runs it without a message; PHPStan reports that the calls "do not do anything".

**Fix:** let the function return a value, and work with that. To count, `count(array_filter(...))` is enough.

## Principle check

**DRY:** the loop, the empty array and the append are written once, inside `array_filter`; only the test is yours. **KISS:** use `fn` for one short expression. When a test grows, give it a name: a named function, or an anonymous one in a well-named variable, as `$contains` in *Shelf* below.

## Security

Nothing new here yet. One rule for later: a function value should always come from your own code. Calling a function whose name came from input, such as `$_GET['action']()`, lets a visitor run any function in PHP. Part 7 uses a fixed list of allowed actions instead.

## Shelf

*Shelf* prints its books sorted by title, with `usort`, and its search becomes an `array_filter` with an arrow function. `$contains` is a small closure used twice, for the title and the author. Change `shelf/shelf.php` to:

<!-- include: code/20-anonymous-and-arrow-functions/shelf/shelf.php -->

Run it with `php shelf/shelf.php`. `sortedByTitle` returns a sorted copy, so `$books` itself keeps its order.

## Exercises

The exercises are in `exercises/20-anonymous-and-arrow-functions/`. Check your work with:

```
composer exercise 20 beginner
```

1. **Beginner, `labels.php`:** turn a list of titles into labels with `array_map` and `fn`.
2. **Intermediate, `since.php`:** list the titles since a given year, with the keys 0, 1, 2.
3. **Advanced, `sort.php`:** sort books by author and then by year, with `usort`.

## Summary

- An anonymous function is a value: store it, pass it, call it. Its type is `callable`.
- `array_filter` keeps items, `array_map` changes them, `usort` sorts by your rule; `<=>` compares.
- `use ($x)` hands an outside value to an anonymous function, which makes it a closure.
- `fn(...) => expression` is one expression, sees outside values, and can't change them.
- `array_filter` keeps the keys; `array_values` makes a list again.

## Further reading

- PHP manual, Anonymous functions: https://www.php.net/manual/en/functions.anonymous.php
- PHP manual, Arrow functions: https://www.php.net/manual/en/functions.arrow.php
- PHP manual, `array_filter`: https://www.php.net/manual/en/function.array-filter.php
- PHP manual, `array_map`: https://www.php.net/manual/en/function.array-map.php
- PHP manual, `usort`: https://www.php.net/manual/en/function.usort.php
