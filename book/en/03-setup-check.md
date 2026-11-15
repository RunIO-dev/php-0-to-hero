# 3. The setup check

## Goals

- You will be able to run the course's setup check and read its result.
- You will be able to follow a fix message from the check to a working setup.
- You will be able to install Composer and the course tools that check your exercises.
- You will be able to check your answers to an exercise with one command.

## Before you start

- Chapter 2: PHP 8.5 or newer is installed (or you use Docker), and you have the course folder.
- Open the terminal in the course folder. If you use Docker, put `docker compose run --rm php` in front of every command in this chapter.

## The idea

A setup that only half works is the most frustrating start there is: an example fails, and you can't tell whether your code is wrong or your computer.

So the course comes with a **setup check**: a small PHP program in `tools/check.php`. It looks at your PHP and lists what the course needs, each with `ok` or `FIX`. Under every `FIX`, it prints the exact fix for your system.

Run it whenever something seems wrong, before you search anywhere else. Every later chapter assumes that this check shows no problems.

## In code

### Running the check

```
$ php tools/check.php
```

On a computer where everything is ready, it prints:

```txt
PHP 0-to-hero setup check

  ok    PHP 8.6.0 (the course needs 8.5.0 or newer)
  ok    extension mbstring
  ok    extension pdo_sqlite
  ok    extension ctype
  ok    extension dom
  ok    extension tokenizer
  ok    extension xmlwriter
  ok    error messages are shown (display_errors)
  ok    Composer (it installs the tools that check your exercises)
  ok    the course tools are installed (vendor/)

Everything the course needs is ready.
```

What the lines mean:

- **PHP version:** at least 8.5, as in chapter 2.
- **Extensions:** parts of PHP that can be switched on and off. `mbstring` handles text in any language, `pdo_sqlite` is the database of Part 8, and the other four are needed by the tools that test your code.
- **display_errors:** whether PHP shows error messages. While learning, you want to see every one.
- **Composer and vendor/:** the tools that check your exercises (below).

### Reading a fix message

When something is missing, the line says `FIX`, and the fix follows right under it. For example, on Ubuntu without the database extension:

```txt
  FIX   extension pdo_sqlite
        Debian or Ubuntu: sudo apt install php-sqlite3
        Fedora: sudo dnf install php-pdo
```

Do what the line for your system says, then run the check again. Fix one thing at a time, and read the check's output each time: one fix can change what the next line says.

### Installing Composer and the course tools

**Composer** is PHP's tool for installing libraries: code that other people wrote and shared. The course uses it to install **PHPUnit**, the tool that tests your exercises. Chapter 36 explains Composer properly; here you only install it and use it once.

- **Windows:** download and run `Composer-Setup.exe` from https://getcomposer.org/download/. It finds your PHP and puts `composer` on the Path. Open a new terminal afterwards.
- **macOS:** `brew install composer`
- **Linux:** follow the command-line steps on https://getcomposer.org/download/, or use your distribution's package (`sudo apt install composer` or `sudo dnf install composer`).
- **Docker:** nothing to do; the course image has Composer.

Then, in the course folder, install the tools:

```
$ composer install
```

This reads the list of tools in `composer.json`, downloads them into the folder `vendor/`, and prints what it installed. Run the check again; the last two lines now say `ok`.

### Checking an exercise

Every chapter's exercises are checked with one command: the chapter number and the level.

```
$ composer exercise 01 beginner
```

It runs the exercise's tests and lists each one with ✔ or ✘. A ✘ comes with what was expected and what your code produced. When every line shows ✔, you're done.

## Common mistakes

### Changing php.ini has no effect

PHP reads `php.ini` when it starts. Most fixes for extensions and `display_errors` are a line in `php.ini`, so make sure you changed the file PHP really uses: `php --ini` shows its path under "Loaded Configuration File". If it says `(none)`, no `php.ini` is loaded; on Windows, that usually means the copy of `php.ini-development` from chapter 2 is missing.

### "composer: command not found"

Composer is not installed yet, or the terminal was opened before it was. Open a new terminal; if that doesn't help, install it again as described above.

### "Could not open input file: tools/check.php"

The terminal is in another folder. Chapter 4 shows how to find out where you are and how to move.

## Principle check

**Fail fast:** a problem that shows up at the start, with a clear message, costs a minute. The same problem showing up in chapter 20 as a strange error costs an evening.

## Security

`composer install` downloads code from the internet and runs it on your computer. Only run it in projects you trust, such as this course. Chapter 38 shows how Composer checks packages for known security problems.

## Shelf

Nothing yet: *Shelf* starts in chapter 6.

## Exercises

Each exercise folder in `exercises/03-setup-check/` has a check result from another learner in `check.txt`, and a `README.md` with their situation. Read the check, and write the exact fix into `answer.txt`. Then:

```
composer exercise 03 beginner
```

1. **Beginner:** Ana uses Ubuntu, and one extension is missing. Which command fixes it?
2. **Intermediate:** Kenji uses Windows, and one extension is missing. Which line must he change in his `php.ini`?
3. **Advanced:** Amira's Linux system only offers an old PHP, and three things are wrong. Which one command gives her a working setup without installing anything but Docker?

## Summary

- `php tools/check.php` lists what the course needs, with `ok` or `FIX`.
- Under each `FIX` is the exact fix for your system: do it, then run the check again.
- `php --ini` shows which `php.ini` PHP really uses.
- `composer install` installs the tools that check your exercises; `composer exercise NN level` runs them.

## Further reading

- PHP manual, The configuration file: https://www.php.net/manual/en/configuration.file.php
- Composer, Getting started: https://getcomposer.org/doc/00-intro.md
