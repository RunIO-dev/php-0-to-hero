# 14. Working with arrays

## Goals

- You will be able to sort lists of numbers and of text, the way people expect.
- You will be able to search a list, and handle the answer "not found" correctly.
- You will be able to filter a list and map it to a new one with `foreach`.
- You will be able to use `array_column`, `array_sum`, `implode` and `explode`.

## Before you start

- Chapter 13: you can build a list of maps and go through it with `foreach`.
- Chapter 10: you know why this course compares with `===`.

## The idea

Once data is in arrays, most work on it is one of a few jobs, and they have names:

- **Sort:** put the items in order, by year or by title.
- **Search:** is a title in the list, and where?
- **Filter:** keep only the items that pass a test, such as the books with a copy on the shelf.
- **Map:** turn every item into something else, such as every book into its title.

PHP has a built-in function for many of these. For filtering and mapping, you write a short `foreach` that builds a new array. Chapter 20 shows the shorter forms, `array_filter` and `array_map`, once you can write your own functions.

## In code

### Sorting

<!-- include: code/14-working-with-arrays/sort.php -->

- `sort` puts a list in ascending order, `rsort` in descending order.
- They change the array itself, **in place**: after `sort($years);`, `$years` is sorted. That is different from the string functions in chapter 8, which returned a new value.
- `implode(', ', $years)` joins the items into one string, with `', '` between them, which makes a list easy to print.

### Sorting text the way people expect

<!-- include: code/14-working-with-arrays/sort-text.php -->

By default, `sort` compares text by character codes, as `<` did in chapter 10: every capital letter comes before every small one, so "Kindred" and "Sula" come before "beloved".

`SORT_STRING | SORT_FLAG_CASE` tells `sort` to compare the text with case ignored. The `|` combines the two **flags**, settings that change how a function works. Now the list is alphabetical, as in a library catalogue.

This works well for English. Sorting German or Arabic words exactly as their dictionaries do needs the `intl` extension; for this course's examples, the flags are enough.

### Searching

<!-- include: code/14-working-with-arrays/search.php -->

- `in_array($value, $list, true)` answers whether the value is in the list.
- `array_search($value, $list, true)` gives the key where it is: here 2, the third position. When the value is missing, it returns `false`.
- The third argument, `true`, makes both compare with `===`. Always pass it; the mistakes below show why.

### Filtering

<!-- include: code/14-working-with-arrays/filter.php -->

A filter starts with an empty array, goes through the items, and adds only the ones that pass the test. The original `$books` stays as it was, so you can use both.

### Mapping, and a column of maps

<!-- include: code/14-working-with-arrays/map.php -->

- A map also starts with an empty array, and adds one new value for **every** item: here, each title in capitals.
- `array_column($books, 'title')` is a ready-made map for a common case: it takes one key out of every map in a list.
- `array_sum` adds up a list of numbers. Together with `array_column`, it counts all copies in one line.

### Joining and splitting text

<!-- include: code/14-working-with-arrays/join-split.php -->

`implode` joins a list into a string; `explode` does the opposite and splits a string at every separator into a list. Data often arrives as text like `history,africa,novel`, and `explode` turns it into a list you can loop over.

## Common mistakes

### Using what `sort` returns

<!-- include: code/14-working-with-arrays/sort-returns.php -->

`sort` sorts the array in place and returns only `true`, to say it worked. `$sorted` holds `true`, not a sorted list.

**Fix:** call `sort($titles);` on its own line, and then use `$titles`.

### "Found" at position 0

<!-- include: code/14-working-with-arrays/found-at-zero.php -->

"Kindred" is the first title, at position 0, so `array_search` returns `0`. In a condition, 0 counts as false, so the first `if` says "Not found" for a book that is there.

**Fix:** compare the result with `false` explicitly: `$position !== false`. This is a case where `===` and `!==` are not a matter of style.

### Searching without `true`

<!-- include: code/14-working-with-arrays/loose-search.php -->

Without the third argument, `in_array` compares with `==`, and `'1e1' == '10'` is true, as in chapter 10. The code `'1e1'` is not in the list, yet the search finds it.

**Fix:** always pass `true` to `in_array` and `array_search`.

## Principle check

**DRY, and the rule of three:** *Shelf*'s search below is a loop over the books with a test. If you add a search by author, you write almost the same loop again. That's fine. A third time, by year, three copies of one idea need the same change whenever it changes, and that is the moment to give the loop a name and reuse it. Chapter 15 shows how: with your own functions. Not after the first copy, not after the tenth: after the third.

## Security

Nothing new here yet. When a search text comes from a user, in chapter 24 and in the web part, the search itself stays the same; what changes is that the text is checked first, and escaped when it is shown.

## Shelf

*Shelf* prints its books on one line each, and gets its first feature: a search by title, in any case. Change `shelf/shelf.php` to:

<!-- include: code/14-working-with-arrays/shelf/shelf.php -->

Run it with `php shelf/shelf.php`, then change `$search` and run it again. Try a text that is in no title.

## Exercises

The exercises are in `exercises/14-working-with-arrays/`. Check your work with:

```
composer exercise 14 beginner
```

1. **Beginner, `sorted.php`:** sort titles alphabetically, with capital and small letters treated the same.
2. **Intermediate, `position.php`:** find a title and say where it is, counting from 1, or that it is missing. Watch out for position 0.
3. **Advanced, `by-author.php`:** list all books by one author, sorted, or say there are none.

## Summary

- `sort` and `rsort` sort in place and return `true`; `SORT_STRING | SORT_FLAG_CASE` ignores case.
- `in_array` and `array_search` search; pass `true` so they compare with `===`, and compare `array_search`'s result with `false`.
- Filter and map with `foreach` into a new array; `array_column` takes one key from every map.
- `implode` joins a list into a string, `explode` splits a string into a list.
- When the same loop appears a third time, it is time for a function (chapter 15).

## Further reading

- PHP manual, Sorting arrays: https://www.php.net/manual/en/array.sorting.php
- PHP manual, `in_array`: https://www.php.net/manual/en/function.in-array.php
- PHP manual, `array_search`: https://www.php.net/manual/en/function.array-search.php
- PHP manual, `array_column`: https://www.php.net/manual/en/function.array-column.php
- PHP manual, `implode`: https://www.php.net/manual/en/function.implode.php
