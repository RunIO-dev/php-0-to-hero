# 22. Reading and writing files

## Goals

- You will be able to read a whole file, or its lines, and write and append text.
- You will be able to read and write CSV files, the format of spreadsheets.
- You will be able to build file paths that work from any folder.
- You will be able to handle a file that is missing, instead of going on with nothing.

## Before you start

- Chapter 19: you can throw and catch exceptions, and use `finally`.
- Chapter 13: you know lists and maps.

## The idea

So far, every program forgot everything when it ended. *Shelf*'s books were written into the code, and a book lent in one run was back on the shelf in the next. To remember, a program writes its data to a **file** and reads it back the next time.

A file is a named piece of data on the disk. Two kinds are enough for now:

- A **text file**, such as a list of titles, one per line.
- A **CSV file** ("comma-separated values"): a table as text. Each line is a row, and commas separate the columns. Every spreadsheet program can open and save it.

Reading a file can fail: the file may be missing, or your program may not be allowed to read it. That is a normal situation, not a bug, and the code handles it, with what you learned in chapter 19.

## In code

### Reading a whole file

<!-- include: code/22-files/read-all.php -->

- `file_get_contents` reads the whole file into one string, line breaks included.
- `__DIR__` is the folder of the PHP file itself. `__DIR__ . '/titles.txt'` is the file next to this script, from whatever folder you start it.

### Reading lines

<!-- include: code/22-files/lines.php -->

`file()` reads a file into a list of lines. The two flags, combined with `|` as in chapter 14, remove the line breaks (`FILE_IGNORE_NEW_LINES`) and skip empty lines (`FILE_SKIP_EMPTY_LINES`).

### Writing and appending

<!-- include: code/22-files/write.php -->

- `file_put_contents($path, $text)` creates the file, or replaces everything in it.
- With `FILE_APPEND`, the text is added to the end instead: the way to keep a log.
- `sys_get_temp_dir()` is the system's folder for temporary files, and `unlink` deletes a file. The example cleans up after itself.

### Reading a CSV file

<!-- include: code/22-files/csv.php -->

`books.csv` holds the table that `csv.php` reads:

<!-- include: code/22-files/books.csv -->

- `fopen($path, 'r')` opens the file for reading (`'r'`) and returns a **handle**: a value that stands for the open file.
- `fgetcsv($file, escape: '')` reads one line and splits it into a list of fields. A field in quotes may contain a comma, as the last title does. At the end of the file, it returns `false`.
- The first line is the **header**, the names of the columns. `array_combine($header, $row)` pairs them up into a map.
- `fclose` closes the file when you are done.

`escape: ''` is a named argument from chapter 17. It makes `fgetcsv` read standard CSV, and PHP asks you to pass it: its old default is going to change.

### Writing a CSV file

<!-- include: code/22-files/write-csv.php -->

`fputcsv` writes a list as one CSV line. It puts quotes around a field only where they are needed, here around "Ana, Jr.". Mode `'w'` opens a file for writing, and empties it first.

### Checking first

<!-- include: code/22-files/check-first.php -->

`is_file` answers whether the path is a file, and `is_readable` whether your program may read it. `readLines` checks both first and throws an exception with the path in its message. The caller can catch it, as here, or let the program stop with a clear message.

## Common mistakes

### A missing file

When `file_get_contents` can't read a file, PHP prints a warning such as "Failed to open stream: No such file or directory", and the function returns `false` instead of a string. If the program goes on, `false` is treated as an empty text: zero titles, an empty list, and no error that names the real problem.

**Fix:** check first and throw, as `readLines` does, or check the result: `if ($text === false)`.

### A path that depends on the current folder

`file_get_contents('titles.txt')` looks for the file in the folder where you **started** PHP, not in the folder of the script. It works when you run `php read-all.php` inside `code/22-files/`, and fails from the repository folder.

**Fix:** build paths from `__DIR__`: `__DIR__ . '/titles.txt'`.

### Splitting CSV lines with `explode`

`explode(',', $line)` splits "Kitchen, and Moonlight Shadow" into two fields, and keeps the quotes. CSV has rules for quotes and commas inside fields.

**Fix:** use `fgetcsv` and `fputcsv`, which know these rules.

## Principle check

**Secure by default:** a file is input from outside your program. Check that it is there, convert its fields to the types you need, and fail with a clear message when it doesn't fit. **KISS:** for a list of a few hundred books, a CSV file is enough. A database comes in Part 8, when the data needs it.

## Security

Never build a file path from input without checking it. A "file name" such as `../../config/secrets.php` climbs out of the folder you meant, and a program that reads or deletes whatever path it is given can be made to read or delete anything it is allowed to. Use a fixed list of allowed files, or keep only the name with `basename()` and look for it in one folder of your choice.

## Shelf

*Shelf*'s books move out of the code into `shelf/books.csv`. `loadBooks` reads them, checks that the file is readable, and converts the year and the copies to `int`, because everything in a file is text. The output stays the same. Create `shelf/books.csv`:

<!-- include: code/22-files/shelf/books.csv -->

And change `shelf/shelf.php` to:

<!-- include: code/22-files/shelf/shelf.php -->

`[$title, $author, $year, $copies] = $row;` is new: it takes the items of a list, in order, into four variables at once. This is called **destructuring**.

Run it with `php shelf/shelf.php`, then add a book to `books.csv` and run it again. *Shelf* reads its books now, but doesn't write them yet: lending changes only the list in memory. Chapter 24 adds commands that save.

## Exercises

The exercises are in `exercises/22-files/`. Check your work with:

```
composer exercise 22 beginner
```

1. **Beginner, `count.php`:** count the titles in a text file, without empty lines.
2. **Intermediate, `log.php`:** add a line to a log of returns, keeping the earlier lines.
3. **Advanced, `members.php`:** read members from a CSV file, and throw when it can't be read.

## Summary

- `file_get_contents` reads a whole file; `file()` reads its lines; `file_put_contents` writes, with `FILE_APPEND` it adds.
- `fopen`, `fgetcsv`/`fputcsv` with `escape: ''`, and `fclose` read and write CSV, quotes included.
- Build paths from `__DIR__`, so they don't depend on the folder you start PHP in.
- A failed read gives a warning and `false`; check first and throw a clear exception.
- Never use input as a path without checking it.

## Further reading

- PHP manual, `file_get_contents`: https://www.php.net/manual/en/function.file-get-contents.php
- PHP manual, `file`: https://www.php.net/manual/en/function.file.php
- PHP manual, `file_put_contents`: https://www.php.net/manual/en/function.file-put-contents.php
- PHP manual, `fgetcsv`: https://www.php.net/manual/en/function.fgetcsv.php
- PHP manual, `fputcsv`: https://www.php.net/manual/en/function.fputcsv.php
