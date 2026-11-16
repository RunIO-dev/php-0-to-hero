# 13. Arrays: lists and maps

## Goals

- You will be able to keep many values in one variable, as a list or as a map.
- You will be able to read, add and change items by position or by key.
- You will be able to go through an array with `foreach`, with and without its keys.
- You will be able to build a list of maps, the shape most data in this course has.

## Before you start

- Chapter 12: you can write a `foreach` loop over a short list.

## The idea

In chapter 6, *Shelf* kept one book in four variables, and the chapter ended with a problem: a hundred books would need four hundred variables. An **array** solves it. It is one variable that holds many values, each under its own **key**.

PHP uses arrays in two ways:

- A **list** holds values in order. The keys are positions, counted from 0: the first title is at 0, the second at 1. Think of numbered places on a shelf.
- A **map** (an associative array) uses names as keys: `'title'`, `'author'`, `'year'`. Think of a library's index card, with a label in front of each value.

Both are the same type, `array`. Only the keys differ. And because an array can hold any value, it can hold other arrays: a list of books, where each book is a map. That is how *Shelf* keeps its books from this chapter on.

## In code

### A list

<!-- include: code/13-arrays/list.php -->

- Square brackets make an array, with the values separated by commas. PHP gives them the keys 0, 1, 2.
- `$titles[0]` reads the value at position 0, the first one. `$titles[2]` is the third.
- `count($titles)` gives the number of items.
- `$titles[] = 'Things Fall Apart';` with empty brackets adds a value at the end, at the next free position.
- `$titles[1] = 'Sula';` replaces the value at position 1.

### A map

<!-- include: code/13-arrays/map.php -->

- `'title' => 'Kindred'` stores the value `'Kindred'` under the key `'title'`. Writing one pair per line, with a comma after the last one too, keeps longer maps readable.
- `$book['title']` reads a value by its key.
- Assigning to a new key adds it: `$book['copies'] = 2;`. `$book['copies']--` changes the value in place.
- Inside double quotes, put an array value in curly braces: `"{$book['title']}"`.

### `foreach` with keys

<!-- include: code/13-arrays/keys.php -->

`foreach ($book as $field => $value)` gives you the key and the value in each round. For a map, the keys are the names. For a list, they are the positions, which shows once more that a list is a map with the keys 0, 1, 2.

### Looking inside an array

<!-- include: code/13-arrays/inspect.php -->

`echo` can't print a whole array, but two functions can:

- `print_r` shows keys and values in a short, readable form. Note that it prints `true` as `1`, like `echo`.
- `var_dump` also shows every value's type, and the number of items: `array(3)`.

When an array doesn't hold what you expect, look at it with one of these, as you did with single values in chapter 7.

### A list of maps

<!-- include: code/13-arrays/nested.php -->

`$books` is a list, and each item in it is a map. `$books[1]['title']` reads in two steps: the item at position 1 (the second book), then its title.

`foreach` hands you one book map per round, so inside the loop you write `$book['title']`, as for a single book. The loop works the same for 3 books or 3,000.

### A key that may be missing

<!-- include: code/13-arrays/missing-key.php -->

- `$book['isbn'] ?? 'no ISBN'` reads the key, or gives the fallback when it is missing (or `null`). This is the `??` from chapter 11.
- `array_key_exists('year', $book)` answers whether the key is there.
- `isset($book['isbn'])` answers whether the key is there **and** its value is not `null`.

## Common mistakes

### A position that doesn't exist

<!-- include: code/13-arrays/undefined-key.error.php -->

The list has three titles at positions 0, 1 and 2. There is no position 3, so PHP warns about an "Undefined array key" and uses `null`, which `echo` prints as nothing. The program goes on, with a result that is wrong.

**Fix:** remember that counting starts at 0, so the last item is at `count($titles) - 1`. When a key may be missing, use `??` or `array_key_exists`.

### Printing a whole array

<!-- include: code/13-arrays/array-to-string.error.php -->

`echo` needs text, and an array can't become text: PHP warns "Array to string conversion" and prints the word `Array`.

**Fix:** print the value you mean (`{$book['title']}`), or look at the whole array with `print_r` or `var_dump`.

### A typo in a key

`$book['tilte']` is a different key from `$book['title']`, and it doesn't exist. You get the same "Undefined array key" warning as above, with the misspelled key in the message, which makes it easy to find.

**Fix:** copy the key from where the array is created. In Part 5, classes give data fixed property names that your editor can check for you.

## Principle check

**DRY:** one book map, one loop, and the same four `echo` lines work for every book. Before arrays, every new book meant four new variables and four new lines.

## Security

Nothing new here yet. Later, data from outside your program, such as a form, arrives in PHP as arrays (`$_GET`, `$_POST`). Then a missing key is the normal case, not a bug, and `??` becomes an everyday tool.

## Shelf

*Shelf* grows from one book to a list of books. Each book is a map with the same four keys, and one `foreach` prints them all. Change `shelf/shelf.php` to:

<!-- include: code/13-arrays/shelf/shelf.php -->

Run it with `php shelf/shelf.php`. Add a fourth book to the list: the loop prints it without any other change.

## Exercises

The exercises are in `exercises/13-arrays/`. Check your work with:

```
composer exercise 13 beginner
```

1. **Beginner, `titles.php`:** add a title to a list, and print its size, the first and the last title.
2. **Intermediate, `return.php`:** a copy of a book comes back: update the map and print it.
3. **Advanced, `available.php`:** list the books with copies on the shelf, and add up the copies.

## Summary

- An array holds many values, each under a key. A list uses the positions 0, 1, 2; a map uses names.
- `$array[key]` reads and writes an item; `$list[] = $value` adds to the end; `count` gives the size.
- `foreach ($array as $key => $value)` goes through keys and values.
- `print_r` and `var_dump` show a whole array; `echo` can't.
- A list of maps is how *Shelf* keeps its books; `??` handles keys that may be missing.

## Further reading

- PHP manual, Arrays: https://www.php.net/manual/en/language.types.array.php
- PHP manual, `count`: https://www.php.net/manual/en/function.count.php
- PHP manual, `print_r`: https://www.php.net/manual/en/function.print-r.php
- PHP manual, `array_key_exists`: https://www.php.net/manual/en/function.array-key-exists.php
