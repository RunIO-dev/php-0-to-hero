# 7. Data types

## Goals

- You will be able to name PHP's five basic types: int, float, string, bool and null.
- You will be able to find out which type a value has, with `var_dump` and `get_debug_type`.
- You will be able to convert a value to another type on purpose, and know when PHP converts one by itself.
- You will be able to read the two messages PHP gives when text is used as a number.

## Before you start

- Chapter 6: you can store values in variables and print them.

## The idea

In chapter 6, some values had quotes (`'Frankenstein'`) and some didn't (`1818`). That difference is not a matter of style. Every value in PHP has a **type**: the kind of value it is. The type decides what you can do with the value. You can add two numbers and get a number. You can't sensibly add two titles.

PHP has five basic types, and they cover most of what a program stores:

| Type | What it holds | Examples |
|---|---|---|
| `int` | a whole number (integer) | `264`, `0`, `-3` |
| `float` | a number with a decimal point (floating-point number) | `4.5`, `0.25` |
| `string` | text, in quotes | `'Kindred'`, `"Hello\n"` |
| `bool` | yes or no (boolean) | `true`, `false` |
| `null` | no value at all | `null` |

You don't write the type down when you create a variable: PHP takes it from the value. `$pages = 264;` holds an int, because `264` is written as a whole number.

## In code

### The five types

<!-- include: code/07-data-types/five-types.php -->

`var_dump` shows a value together with its type. It is the tool for asking "what exactly is in this variable?":

- `int(264)`: an int, with the value 264.
- `float(4.5)`: a float.
- `string(7) "Kindred"`: a string of 7 characters, then the text itself.
- `bool(true)`: a bool.
- `NULL`: null has no value to show, so only the type is printed.

Names of bool variables often read as a question (`$isAvailable`, `$hasCopies`), so that the code reads like a sentence: "is available: true".

`null` means "there is no value here", which is different from 0 or empty text. A book that is still on loan has no return date yet: `$returnedAt = null;`. Zero would be a wrong date, and null says honestly that there is none.

### Quotes make text

<!-- include: code/07-data-types/quotes-matter.php -->

`1979` and `'1979'` look the same when printed, but they are different values: one is a number, the other is four characters of text. `get_debug_type` returns the name of a value's type, which is handy when you only want the type.

Values from outside your program, such as what a user types or what a file contains, always arrive as strings, even when they look like numbers. You will meet this again in chapter 24.

### `echo` hides the type, `var_dump` shows it

<!-- include: code/07-data-types/echo-bool.php -->

`echo` turns every value into text before it prints it. For `true`, that text is `1`. For `false` and for `null`, it is empty text, so nothing appears at all. That is why "Overdue:" is followed by nothing.

When a value looks wrong or seems to be missing, check it with `var_dump`, not with `echo`.

### Converting on purpose

<!-- include: code/07-data-types/converting.php -->

A **cast** converts a value to another type: the type in brackets, written before the value. `(int) $pagesText` turns the text `'264'` into the number 264.

- `(string) 1979` gives the text `"1979"`.
- `(int) 3.9` gives 3. A cast to int cuts the decimals off; it does not round.
- `(bool)` gives `false` for 0, `0.0`, empty text `''`, the text `'0'` and `null`. Everything else is `true`, even the text `'false'`, because it is text with five characters in it.

### Converting by itself

<!-- include: code/07-data-types/automatic.php -->

When an operation needs a number and gets a string that holds one, PHP converts it by itself: `'7' + 1` is 8. This is called **type juggling**.

Division (`/`) gives a float when the result has decimals (2.5), and an int when it divides evenly (2). Chapter 9 looks at arithmetic in detail.

Automatic conversion is convenient, and it is also where surprises come from. The next section shows two of them. From chapter 16 on, you will switch on a stricter mode that turns many silent conversions into clear errors.

## Common mistakes

### Text that only starts with a number

<!-- include: code/07-data-types/non-numeric.error.php -->

`'2 copies'` starts with a number, so PHP uses the 2, adds 1 and gets 3. But it also warns you, because the rest of the text (" copies") was thrown away. The program goes on with a result that may be wrong.

**Fix:** keep numbers as numbers: `$copies = 2;`. When a number arrives as text, check that it is a number before you use it; chapter 24 shows how.

### Text that is not a number at all

<!-- include: code/07-data-types/unsupported.error.php -->

`'Kindred' + 1` makes no sense, and PHP stops with a **fatal error**: the program ends at this line, so the last `echo` never runs. A `TypeError` means a value had the wrong type for what the code tried to do. "Unsupported operand types: string + int" says exactly that: `+` can't work with a string on the left and an int on the right.

**Fix:** find out which value has the wrong type. Here, the variable holds a title, and the code treated it as a number. Often the cause is a mixed-up variable name.

## Principle check

**Secure by default:** know what type a value has before you rely on it. Values from outside your program are always text, whatever they look like. Checking them once, where they come in, is the first habit of secure code.

## Security

Nothing new here yet. Chapter 24 is where values from outside your program come in, and with them the first checks.

## Shelf

Nothing changes in this chapter. Look at `shelf/shelf.php` from chapter 6 with new eyes: the title and author are strings, the year and the number of copies are ints. If you like, add `var_dump($copies);` at the end to check.

## Exercises

The exercises are in `exercises/07-data-types/`. Check your work with:

```
composer exercise 07 beginner
```

1. **Beginner, `types.php`:** store one book in five variables, one of each type, and show them with `var_dump`.
2. **Intermediate, `fix-types.php`:** every value of a book was typed in quotes. Give each the right type, so that `var_dump` shows an int, a float, a bool and null.
3. **Advanced, `conversions.php`:** predict what seven conversions print, in `prediction.txt`, before you run the file.

## Summary

- Every value has a type: `int`, `float`, `string`, `bool` or `null`.
- `var_dump` shows the type and the value; `get_debug_type` returns the type's name. `echo` hides it: `false` and `null` print nothing.
- A cast such as `(int)` converts on purpose. `(int) 3.9` is 3, and `(bool) 'false'` is true.
- PHP converts some values by itself. Text that is only partly a number gives a warning, and text that is no number gives a `TypeError`.

## Further reading

- PHP manual, Types: https://www.php.net/manual/en/language.types.php
- PHP manual, Type juggling: https://www.php.net/manual/en/language.types.type-juggling.php
- PHP manual, `var_dump`: https://www.php.net/manual/en/function.var-dump.php
- PHP manual, `get_debug_type`: https://www.php.net/manual/en/function.get-debug-type.php
