# 2. Installing PHP

## Goals

- You will be able to install PHP 8.5 or newer on Windows, macOS or Linux, from official sources.
- You will be able to check which PHP version you have with `php -v`.
- You will be able to run the course's setup in Docker instead, if installing is not an option.
- You will be able to run a one-line PHP command in the terminal.

## Before you start

- Chapter 1: you know that PHP is a program that runs other programs.
- You need a terminal. On Windows it is called *Terminal* or *PowerShell*, on macOS *Terminal*, and on Linux it depends on your system. Chapter 4 explains the terminal properly; for now, open it and type the commands as they are written here.
- Download this course's files: on the repository page (github.com/RunIO-dev/php-0-to-hero), choose **Code**, then **Download ZIP**, and unpack it into a folder of your choice. Chapter 43 shows the better way, with Git.

## The idea

PHP is a program, like any other program on your computer. It reads your code and runs it. To run PHP code, you need PHP installed.

There are two ways to get it:

1. **Install PHP directly** on your computer. You type `php` in the terminal, and it runs.
2. **Use Docker.** Docker runs PHP inside a small, separate environment called a **container**, so nothing is installed on your computer except Docker itself. The course's examples are tested in exactly these containers.

Both are fine. Installing directly is the usual way, and the first parts of the course assume it. Docker is the fallback when your system only offers an older PHP, or when an installation doesn't work. Chapter 45 explains Docker in depth.

This course needs **PHP 8.5 or newer**. Older versions are missing features the course uses, and they stop getting security fixes sooner.

## In code

The commands below go into the terminal. Type them without the `$`: it only marks the start of a command.

### Windows

1. Open the official download page: https://windows.php.net/download/
2. Under the newest PHP version, download the zip for **VS17 x64 Non Thread Safe**. "Non thread safe" is the right build for the command line.
3. Unpack it to `C:\php`.
4. In `C:\php`, copy the file `php.ini-development` and name the copy `php.ini`. It holds PHP's settings; the development version shows every error, which you want while learning.
5. Add `C:\php` to your **Path**, so that the terminal finds `php`: open the Start menu, search for "environment variables", choose *Edit the system environment variables*, then *Environment Variables*, select *Path*, *Edit*, *New*, and enter `C:\php`.
6. If PHP later complains about a missing `VCRUNTIME` file, install the *Visual C++ Redistributable for Visual Studio 2015–2022*, linked on the same download page.
7. Close the terminal and open a new one, so that it sees the new Path.

### macOS

macOS has no PHP of its own. The common way to install it is Homebrew, a free package manager for macOS (https://brew.sh). Once Homebrew is installed:

```
$ brew install php
```

### Linux

Linux distributions bring their own PHP packages. On Debian and Ubuntu:

```
$ sudo apt install php-cli php-mbstring php-sqlite3 php-xml
```

On Fedora:

```
$ sudo dnf install php-cli php-mbstring php-pdo php-xml
```

Distributions often ship an older PHP version. Check it with `php -v` below. If it shows anything older than 8.5, use Docker instead.

### Docker, on any system

Install Docker from https://docs.docker.com/get-started/get-docker/ (Docker Desktop is free for personal use and learning; on Linux, Docker Engine is free for everyone). Then, in the course folder:

```
$ docker compose run --rm php php -v
```

The first time, this downloads and builds the course's PHP image, which takes a few minutes. Afterwards, put `docker compose run --rm php` in front of any `php` command in this course, and it runs in the container.

### Check your version

```
$ php -v
```

The first line shows the version, for example `PHP 8.6.0 (cli)`. Anything from 8.5 on is fine.

Finally, run a file from the course folder to see that PHP works:

<!-- include: code/02-installing-php/works.php -->

```
$ php code/02-installing-php/works.php
```

## Common mistakes

### "php is not recognized" or "command not found"

The terminal can't find PHP. On Windows, `C:\php` is not on the Path yet, or the terminal was open before you changed the Path: open a new one. On macOS and Linux, the installation did not finish; run the install command again and read its last lines.

### The version is older than 8.5

Your system has an old PHP. On Linux, the distribution's packages are often older; use Docker. On Windows, check that you unpacked the newest version, and that no other, older PHP comes first on your Path (`where php` lists them all).

### "Could not open input file"

PHP can't find the file you asked it to run. You are probably in another folder. Chapter 4 shows how to move between folders; until then, open the terminal directly in the course folder.

## Principle check

**KISS (keep it simple):** one PHP for the whole course, installed one way. If you used Docker, stay with Docker; if you installed PHP, stay with that. Mixing them makes mistakes hard to find.

## Security

Download PHP only from the official places named in this chapter, or your operating system's own packages. A PHP from an unknown website could contain anything.

## Shelf

Nothing yet: *Shelf* starts in chapter 6.

## Exercises

The exercises are in `exercises/02-installing-php/`. Each folder has a `README.md` with the task, and a file `answer.txt` for your answer. Chapter 3 sets up the tool that checks your answers. Write your answers now, and check them after chapter 3 with `composer exercise 02 beginner` (and `intermediate`, `advanced`).

1. **Beginner:** run a one-line PHP command with `php -r` and write down what it printed.
2. **Intermediate:** ask PHP for its major version number with a one-line command.
3. **Advanced:** use PHP's built-in help (`php -h`) to find the option that checks a file for errors without running it.

## Summary

- The course needs PHP 8.5 or newer, installed from official sources, or run in Docker.
- `php -v` shows the version; `php file.php` runs a file; `php -r "..."` runs one line.
- On Windows, PHP must be on the Path, and a new terminal sees it.
- If your system's PHP is too old, use Docker: `docker compose run --rm php` before each command.

## Further reading

- PHP manual, Installation and configuration: https://www.php.net/manual/en/install.php
- PHP manual, Command line usage: https://www.php.net/manual/en/features.commandline.usage.php
- PHP for Windows, downloads: https://windows.php.net/download/
