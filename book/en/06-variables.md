# 6. Variables and naming

## Goals

- You will be able to store a value in a variable and use it later.
- You will be able to change what a variable holds.
- You will be able to choose names that say what a variable holds.
- You will be able to read the three error messages that beginners meet most often with variables.

## Before you start

- Chapters 1 to 5: PHP is installed, `php tools/check.php` shows no problems, and you can run a file with `php file.php`.
- This is the chapter where the *Shelf* project starts. There is nothing to download for it yet: you create its first file below.

## The idea

A program works with values: the title of a book, a year, a number of copies. Writing a value once is quick. Using it again later, or changing it, needs a place to keep it.

A **variable** is a name for a value. You give the name a value once, and from then on you use the name wherever you need the value.

Think of a label on a box. The label is the name, and what is in the box is the value. You can look into the box as often as you like, and you can empty it and put something else in. The label stays the same.

In PHP, every variable name starts with a dollar sign: `$title`, `$year`, `$copies`. The `$` tells PHP "this is a variable", and the rest is the name you choose.

## In code

### Your first variable

<!-- include: code/06-variables/first-variable.php -->

- `$title = 'Frankenstein';` puts the text `Frankenstein` into the variable `$title`. The `=` here does not mean "is equal to", as in mathematics. It means "store the value on the right in the variable on the left". This is called an **assignment**.
- The text is in single quotes. Quotes tell PHP where a piece of text starts and ends. Chapter 8 shows more about text.
- `echo $title;` prints what the variable holds, not the word "title".
- `echo "\n";` starts a new line, as in chapter 5.

### Changing a value

<!-- include: code/06-variables/change.php -->

- The variable `$copies` first holds `3`. Numbers are written without quotes.
- `echo` can print several things in one line: separate them with commas. `echo $copies, "\n";` prints the value and then a new line.
- The second assignment replaces the old value. The `3` is gone, and `$copies` now holds `2`. A variable holds one value at a time.
- The line starting with `//` is a comment. PHP ignores it; it is there for the person reading the code.

### Several variables

<!-- include: code/06-variables/several.php -->

Each piece of information about the book gets its own variable. The year is a number, so it has no quotes. Chapter 7 explains the difference between text and numbers.

### Assignment copies the value

<!-- include: code/06-variables/copy.php -->

- `$copy = $original;` takes the value that `$original` holds at this moment and stores it in `$copy`.
- Changing `$original` afterwards does not change `$copy`. The two variables are not connected: `$copy` received a copy of the value, not a link to the other variable.

This matters more than it seems. The advanced exercise of this chapter builds on it.

### Rules and good names

<!-- include: code/06-variables/names.php -->

PHP's rules for a name are short:

- After the `$`, a name starts with a letter or an underscore (`_`), never with a digit.
- After that, it may have letters, digits and underscores.
- Names are case-sensitive: `$title` and `$Title` are two different variables, as the output shows.

The rules allow many names that are hard to read. These conventions keep code clear, and this course follows them:

- **Say what the variable holds.** `$publishedYear` tells you more than `$y` or `$data`. You read code far more often than you write it, so a longer clear name saves time.
- **Use English names in camelCase:** the first word in lower case, each following word with a capital letter (`$bookTitle`, `$dueDate`). Other styles exist, such as `$book_title`. Pick one per project and keep to it.
- **Don't rely on case to tell variables apart.** `$title` and `$Title` in the same file is legal, and confusing. The example only shows it so you know PHP treats them as different.

## Common mistakes

Every programmer makes these. The message tells you what went wrong and on which line.

### A typo in a name

<!-- include: code/06-variables/typo.error.php -->

PHP says it does not know a variable named `$titel`, because that is not the name you assigned (`$title`). It is a **warning**, not an error: the program goes on, and the `echo` prints nothing in place of the variable. Warnings deserve the same attention as errors, because the output is now wrong.

**Fix:** use exactly the name you assigned. Your editor can help: most editors suggest variable names as you type.

### A missing dollar sign

<!-- include: code/06-variables/missing-dollar.error.php -->

Without the `$`, `title` is not a variable to PHP, so the `=` that follows makes no sense to it. A **parse error** means PHP could not read the file at all, so nothing in it runs.

**Fix:** write `$title`.

### A missing semicolon

<!-- include: code/06-variables/missing-semicolon.error.php -->

Every statement ends with `;`. Here the first line has none, so PHP reads on into the next line and finds an `echo` where it expected the end of the statement. That is why the message points to line 4, while the mistake is at the end of line 3.

**Fix:** when a parse error points to a line that looks fine, look at the end of the line above it.

### A name that starts with a digit

<!-- include: code/06-variables/digit-first.error.php -->

A name may contain digits, but not at the start.

**Fix:** put the digit later, or use a word: `$firstBook`.

## Principle check

**KISS (keep it simple):** a clear name is the simplest documentation there is. `$copies = 2;` needs no comment to explain it; `$c = 2;` does.

## Security

Nothing new here. Variables only hold what your own code puts into them. From chapter 24 on, values also come from outside your program, and that is where the security notes start.

## Shelf

*Shelf* is the project that grows through this course: a small lending library with books, members and loans. In this chapter it starts with one book, kept in variables.

Create the file `shelf/shelf.php`:

<!-- include: shelf/shelf.php -->

Run it from the repository folder:

```
php shelf/shelf.php
```

The labels in quotes (`'Title:  '`) have extra spaces so that the values line up.

One book in four variables works. For a second book you would need four more variables, and for a hundred books, four hundred. Chapter 13 solves that with arrays.

## Exercises

Each exercise is a file in `exercises/06-variables/`. Open it, read the task at the top, write your code, and run the tests:

```
composer exercise 06 beginner
```

1. **Beginner, `book.php`:** store a book in three variables and print them. The tests check the output, the variable names, and that the title is written only once.
2. **Intermediate, `rename.php`:** a working script with names like `$a` and `$b`. Rename every variable so that it says what it holds, while the output stays exactly the same.
3. **Advanced, `swap.php`:** two books stand on a display. Swap them without typing either title again. Think about the example "Assignment copies the value".

The solutions are in `solutions/06-variables/`. Try first: the tests tell you when you are done.

## Summary

- A variable is a name for a value. Its name starts with `$`.
- `=` assigns: it stores the value on the right in the variable on the left, and replaces what was there.
- Assigning one variable to another copies the value; the two stay independent.
- Names start with a letter or `_`, are case-sensitive, and should say what they hold (`$publishedYear`, in camelCase).
- Read error messages for their line number, and for a parse error also check the line above.

## Further reading

- PHP manual, Variables, basics: https://www.php.net/manual/en/language.variables.basics.php
- PHP manual, `echo`: https://www.php.net/manual/en/function.echo.php
