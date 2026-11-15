# 5. The first program

## Goals

- You will be able to write a PHP file that prints text, and run it.
- You will be able to explain the opening tag `<?php`, the semicolon, and `"\n"`.
- You will be able to write comments that PHP skips.
- You will be able to read a parse error calmly: what it says, and where to look.

## Before you start

- Chapter 4: you can create a file in your editor and run it in the terminal with `php file.php`.

## The idea

A PHP program is a text file whose name ends in `.php`. PHP reads it from top to bottom and does what each **statement** says. A statement is one instruction, like a sentence, and it ends with a semicolon.

The first statement you need is `echo`: it prints text. With `echo` alone you can already write a program that does something visible, and that is enough to learn how files, quotes, comments and error messages work. Everything later builds on these.

## In code

### Hello, world

<!-- include: code/05-first-program/hello.php -->

Save it as `hello.php` and run it:

```
$ php hello.php
```

- `<?php` is the **opening tag**. Everything after it is PHP code. It must be the very first thing in the file.
- `echo` prints what follows it.
- The text is a **string**: a piece of text between quotes.
- `\n` inside the quotes is a new line. Without it, the terminal's prompt would continue on the same line as your text.
- `;` ends the statement.

A file that contains only PHP has no closing tag (`?>`) at the end. It is allowed, but it is left out on purpose: anything after a closing tag, even an empty line, would be printed too.

### Several lines

<!-- include: code/05-first-program/lines.php -->

PHP runs the statements in order, from top to bottom. Each `echo` prints its line, and each `\n` starts the next one.

### Single and double quotes

<!-- include: code/05-first-program/quotes.php -->

Both kinds of quotes make a string, but they differ:

- In **double quotes**, `\n` becomes a new line.
- In **single quotes**, `\n` stays two plain characters: a backslash and an `n`.

So when you want a new line, use double quotes. Chapter 8 shows the other differences.

### Comments

<!-- include: code/05-first-program/comments.php -->

A **comment** is text for people; PHP skips it. `//` and `#` start a comment that runs to the end of the line. `/*` starts a comment that runs until `*/`, over as many lines as you like.

Good comments explain *why* the code does something. What it does, the code itself should show.

## Common mistakes

### A misspelled word

<!-- include: code/05-first-program/misspelled.error.php -->

`ecko` is not a word PHP knows, so it reads `ecko` as a name, and then finds a string right after it, which makes no sense. The message names the thing it did not expect (the string) and the line.

A **parse error** means PHP could not read the file at all. Nothing in it runs, not even the lines before the mistake.

**Fix:** look at the line the message names, and read it word by word.

### A quote that is never closed

<!-- include: code/05-first-program/unclosed-quote.error.php -->

The first string has no closing `"`. PHP doesn't know that: it reads on until the next `"`, at the start of line 4, and takes everything up to there as one string. Then it finds `Bye`, which makes no sense after a string. So the message points to line 4, while the mistake is on line 3.

**Fix:** when the line in the message looks fine, look at the line above it. Your editor helps here: when strings are coloured, an unclosed quote colours everything after it.

## Principle check

**KISS (keep it simple):** one statement per line, one idea per file. Short, plain programs are easier to read, and a mistake in them is easier to find.

## Security

Nothing new here. Your programs only print text you wrote yourself.

## Shelf

Nothing yet: *Shelf* starts in the next chapter, with its first book.

## Exercises

The exercises are in `exercises/05-first-program/`. Each task is at the top of its file. Check your work with:

```
composer exercise 05 beginner
```

1. **Beginner, `hello.php`:** print `Hello, PHP!` on its own line.
2. **Intermediate, `broken.error.php`:** a program with two mistakes. Fix it so that it prints its two lines.
3. **Advanced, `receipt.php`:** print a small loan receipt, exactly as shown in the file, with at least one comment and no closing tag.

## Summary

- A PHP file starts with `<?php`; a file of pure PHP has no closing tag.
- `echo` prints a string; each statement ends with `;`.
- `"\n"` in double quotes is a new line; in single quotes it stays `\n`.
- `//`, `#` and `/* */` start comments, which PHP skips.
- A parse error stops the whole file: read the named line, then the line above it.

## Further reading

- PHP manual, Basic syntax: https://www.php.net/manual/en/language.basic-syntax.php
- PHP manual, Comments: https://www.php.net/manual/en/language.basic-syntax.comments.php
- PHP manual, `echo`: https://www.php.net/manual/en/function.echo.php
