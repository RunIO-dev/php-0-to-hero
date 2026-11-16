# 24. Input on the command line

## Goals

- You will be able to read the words typed after a script's name, with `$argv`.
- You will be able to read lines that a person types, or that come from a file, with `fgets(STDIN)`.
- You will be able to validate input with `filter_var`, and refuse what doesn't fit.
- You will be able to write a small command with a usage message, errors on `STDERR`, and an exit code.

## Before you start

- Chapter 23: *Shelf* reads its books from a JSON file.
- Chapter 16: you know why input arrives as text, and must be converted on purpose.

## The idea

Until now, every value came from the code itself. A real program gets its input from outside: the words you type after its name, the lines you type while it runs, a file, later a form on a website.

On the command line, a program gets input in two ways:

- **Arguments:** the words after the script's name, as in `php shelf.php lend Kindred`. PHP puts them into the list `$argv`.
- **Standard input** (`STDIN`): lines that you type while the program runs, or that come from a file with `<`, as in `php count.php < titles.txt`.

And it answers in two ways too: **standard output** (`STDOUT`, what `echo` prints) for results, and **standard error** (`STDERR`) for messages about problems. Both appear in the terminal, but they can be told apart, for example when the output is saved to a file.

All input has one thing in common: it can be anything. Someone types a title where a number belongs, leaves a word out, or adds a space. This is the course's first security rule, and the most important one: **never trust input**. Check it where it comes in, and refuse what doesn't fit.

## In code

From this chapter on, an example's output shows the command that ran it, with its arguments.

### Arguments: `$argv`

<!-- include: code/24-command-line-input/argv.php -->

- `$argv` is the list of words. Position 0 is the script's name, so the first real argument is `$argv[1]`.
- `$argc` is the number of words, the name included.
- Quotes keep words together: `"Things Fall Apart"` is one argument, not three.

### A usage message and an exit code

<!-- include: code/24-command-line-input/usage.php -->

- Without a title, the script can't work, so it says how to use it, and stops.
- `fwrite(STDERR, ...)` writes the message to standard error.
- `exit(1)` ends the program with the **exit code** 1. By convention, 0 means success and anything else means failure. Scripts and tools that run your program read it; in a terminal, `echo $?` shows the last one.

### Reading a typed line

<!-- include: code/24-command-line-input/read-line.php -->

`fgets(STDIN)` waits until the person types a line and presses Enter, and returns it with the line break at its end. `trim` removes it, and any spaces around the text.

In the output above, the input came from the file `name.txt` with `<`, so the typed name doesn't appear. In a terminal, you would see `Your name: Amira` and then the greeting on the next line.

### Reading lines until the input ends

<!-- include: code/24-command-line-input/read-lines.php -->

At the end of the input, `fgets` returns `false`, which ends the loop. With `< titles.txt`, that's the end of the file. When you type the lines yourself, end the input with `Ctrl+D` (on Windows: `Ctrl+Z`, then Enter).

### Validating input

<!-- include: code/24-command-line-input/validate.php -->

`filter_var($input, FILTER_VALIDATE_INT, ...)` returns the input as an int if it is a whole number, and `false` if it isn't. With `min_range` and `max_range`, a number outside the range is refused too. Spaces around the number are allowed, so `" 12"` is accepted as 12.

This is an **allow-list**: you describe exactly what is allowed, a whole number from 0 to 365, and refuse everything else. That is safer than trying to list everything that is wrong.

## Common mistakes

### Converting instead of validating

<!-- include: code/24-command-line-input/cast.php -->

`(int)` never refuses anything. A word becomes 0, "7 days" becomes 7, and "1e3" becomes 1000. A program that converts input with `(int)` silently goes on with numbers nobody typed.

**Fix:** validate with `filter_var`, refuse `false`, and only then use the number.

### A missing argument

<!-- include: code/24-command-line-input/missing.error.php -->

⚠ Started without a title, `$argv[1]` doesn't exist. PHP warns and goes on with `null`, which prints as nothing.

**Fix:** check `$argc` first and print a usage message, as in `usage.php`, or use a default: `$argv[1] ?? 'list'`.

### Comparing a typed line without `trim`

<!-- include: code/24-command-line-input/newline.php -->

The typed answer is `"yes\n"`, with the Enter key at its end, so it is not `===` `"yes"`.

**Fix:** `trim` every line you read before you compare or store it.

## Principle check

**Secure by default:** never trust input. Validate it with an allow-list where it enters the program, and from then on, the rest of the code can rely on it. **KISS:** a command line with a few clear commands and one usage message is enough for *Shelf*. There is no need for a menu system.

## Security

Input must never decide what code runs, beyond a fixed list of choices. *Shelf* below accepts exactly three commands, and anything else is refused with the usage message. Never pass input to `eval` or to a shell command: an argument such as `Kindred; rm -rf ~` would run the second part. `tools/verify` refuses `eval` in this course for that reason.

The same rules hold in Part 7, where the input comes from forms on a website, and from people you don't know.

## Shelf

*Shelf* becomes a command. `list` prints the books, `search <text>` searches the titles, optionally by an author, and `lend <title>` lends a copy and **saves** the books to `books.json`. Anything else prints the usage message to `STDERR` and exits with 1. Change `shelf/shelf.php` to:

<!-- include: code/24-command-line-input/shelf/shelf.php -->

Some details:

- All the work happens in `run`, which gets the arguments, the path and today's date as parameters, and returns the text to print. It doesn't read `$argv` or the clock itself, so it can be tested.
- `run` checks the command against a fixed list first, then that a title or search text is there.
- `<<<TXT ... TXT;` is a **heredoc**: a string over several lines, without quotes inside. The indentation before the closing `TXT` is removed from every line.
- Lending uses the real date now: `new DateTimeImmutable('today')`.

Try it:

```
php shelf/shelf.php
php shelf/shelf.php search ki
php shelf/shelf.php lend Kitchen
php shelf/shelf.php lend Kitchen
php shelf/shelf.php fly
```

The first `lend` saves; the second finds no copy left. Open `shelf/books.json` to see the change. To undo it, set Kitchen's `copies` back to 1.

## Exercises

The exercises are in `exercises/24-command-line-input/`. Check your work with:

```
composer exercise 24 beginner
```

1. **Beginner, `greet.php`:** greet the name given as an argument, or print a usage message.
2. **Intermediate, `plan.php`:** a reading plan from two numbers, validated with `filter_var`.
3. **Advanced, `titles.php`:** read titles from the input, clean them, remove duplicates, and sort them.

## Summary

- `$argv` holds the words after `php`, with the script's name at 0; `$argc` counts them.
- `fgets(STDIN)` reads one typed line, with its line break; at the end of the input, it returns `false`.
- Results go to `STDOUT`, problems to `STDERR`, and `exit(1)` reports a failure.
- Never trust input: validate it with an allow-list (`filter_var`, a fixed list of commands), and `trim` typed lines.
- `(int)` converts anything without complaint; use it only after validation.

## Further reading

- PHP manual, `$argv`: https://www.php.net/manual/en/reserved.variables.argv.php
- PHP manual, Command line usage: https://www.php.net/manual/en/features.commandline.php
- PHP manual, `fgets`: https://www.php.net/manual/en/function.fgets.php
- PHP manual, `filter_var`: https://www.php.net/manual/en/function.filter-var.php
- PHP manual, Validate filters: https://www.php.net/manual/en/filter.constants.php
