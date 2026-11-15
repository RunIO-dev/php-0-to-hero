# 4. Your editor and the terminal

## Goals

- You will be able to choose a code editor and set it up for PHP in a few minutes.
- You will be able to find out in the terminal which folder you are in, and list what is in it.
- You will be able to move between folders with `cd`.
- You will be able to write a file in the editor and run it from the terminal.

## Before you start

- Chapters 2 and 3: PHP works, and `php tools/check.php` shows no problems.

## The idea

Programming needs two tools, side by side:

- An **editor**, where you write code. It is a text editor made for code: it colours the code so that you can read it, numbers the lines, and warns about some mistakes while you type.
- A **terminal**, where you run the code. You type a command, press Enter, and the computer answers with text.

You will switch between them all the time: write in the editor, save, run in the terminal, read the result, back to the editor.

### Choosing an editor

Any code editor works for this course, and good ones are free. Look for these, which most offer:

- **PHP support:** colours for PHP code. Many editors need a PHP extension for it; install the one they suggest.
- **Line numbers:** error messages name a line, so you need to find it.
- **A built-in terminal:** optional, but handy. You can keep the code and the terminal in one window.

A word processor (the kind for letters and reports) does not work: it adds formatting that PHP can't read.

### The terminal

The terminal always works *in* a folder, the **current folder**. When you type `php hello.php`, PHP looks for `hello.php` there. Most "file not found" problems come from being in another folder than you think.

Three commands are enough for this course. They differ a little between systems:

| What | macOS and Linux | Windows (PowerShell) |
|---|---|---|
| Show the current folder | `pwd` | `pwd` (or `cd` in the older Command Prompt) |
| List what is in it | `ls` | `ls` (or `dir`) |
| Go into a folder | `cd foldername` | `cd foldername` |
| Go up one folder | `cd ..` | `cd ..` |

`..` always means "the folder above this one".

## In code

### A short terminal session

Start in the course folder, look around, and go into the code of this chapter:

```
$ pwd
/home/kenji/php-0-to-hero
$ ls
README.md  book  code  composer.json  exercises  shelf  solutions  tools  ...
$ cd code/04-editor-and-terminal
$ ls
hello.out  hello.php
$ cd ../..
$ pwd
/home/kenji/php-0-to-hero
```

- `pwd` prints the full path of the current folder. Yours will show your own folder.
- `cd code/04-editor-and-terminal` goes two folders down at once.
- `cd ../..` goes up two folders, back to where you started.

On Windows, paths use backslashes (`C:\Users\kenji\php-0-to-hero`). PowerShell also accepts forward slashes in `cd`.

### From the editor to the terminal

Open the course folder in your editor. Create a new file `my-first.php` in it, type this, and save:

<!-- include: code/04-editor-and-terminal/hello.php -->

Then, in the terminal, in the same folder:

```
$ php my-first.php
```

Change the text, save, and run it again. That loop (change, save, run, read) is how you will work through the whole course.

## Common mistakes

### "Could not open input file: my-first.php"

PHP looked in the current folder and didn't find the file. Run `pwd` and `ls`: either you are in another folder (`cd` there), or the file was saved somewhere else (check where your editor saved it).

### Nothing changed after editing

The file was not saved. Most editors show a dot or a star on the tab of an unsaved file. Save, then run again.

### The file is called `my-first.php.txt`

Some systems hide file endings and add `.txt` to new files. Turn on "show file name extensions" in your file manager, and rename the file.

## Principle check

**KISS (keep it simple):** two tools and four commands are all you need. Editors can do much more; learn those features when you miss them, not before.

## Security

The terminal does exactly what you type, without asking. Only run commands you understand, or that come from a source you trust, such as this course.

## Shelf

Nothing yet: *Shelf* starts in chapter 6.

## Exercises

Each folder in `exercises/04-editor-and-terminal/` has a `README.md` with the task. Write each command into `answer.txt`, then check:

```
composer exercise 04 beginner
```

1. **Beginner:** the command that shows the current folder.
2. **Intermediate:** the command that lists what is in the current folder.
3. **Advanced:** two commands, one per line: go from the course folder into `exercises/04-editor-and-terminal`, then back up to the course folder.

The commands of every system count as correct.

## Summary

- Write code in a code editor; run it in the terminal.
- The terminal works in a current folder: `pwd` shows it, `ls` lists it, `cd` moves.
- `..` is the folder above; `cd ../..` goes up two.
- Change, save, run, read: that is the loop for the whole course.

## Further reading

- PHP manual, Executing PHP files: https://www.php.net/manual/en/features.commandline.usage.php
