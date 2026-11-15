# 1. What programming is, and what PHP is for

## Goals

- You will be able to say in one sentence what a program is.
- You will be able to explain what happens between a browser and a server when you open a web page.
- You will be able to say where PHP runs, and what the visitor of a website sees of it.
- You will be able to read a short PHP file and predict what it prints.

## Before you start

- Nothing to install yet. This chapter is about ideas; chapter 2 installs PHP.
- You can read the code here without running it. Once PHP is installed, come back and try the exercises.

## The idea

A **program** is a list of instructions that a computer follows, one after the other. The instructions are written in a **programming language**: a language with strict rules, so that the computer understands exactly one meaning.

People write programs for everything a computer does: a calculator, a game, a website. This course teaches one language, **PHP**, and how to build web applications with it.

### Client and server

When you open a web page, two computers talk to each other:

- Your **browser** asks for the page. The browser, and the computer it runs on, is the **client**: the side that asks.
- Another computer, somewhere on the internet, answers. It is the **server**: the side that serves.

The server sends back text in a language called HTML. HTML describes what is on the page: a heading, a paragraph, a link. Your browser reads the HTML and draws the page.

For a page that never changes, the server can send the same HTML file to everyone. Most websites change, though: a shop shows today's prices, a library shows which books are on loan right now. Someone has to write that HTML at the moment the page is asked for. That is where PHP comes in.

### Where PHP runs

PHP runs **on the server**. When a request comes in, the server runs a PHP program. The program writes the HTML for this one request, and the server sends it to the browser.

The visitor never sees the PHP code. They only see what the program printed. This matters later: your code, and the passwords it uses to reach a database, stay on the server.

PHP also runs on its own, without a browser, in a **terminal**: a window where you type commands. That is how this course starts, because it is the simplest way to see what a program does. Web pages come in Part 7.

PHP has been around since 1995, it is free and open source, and it runs a large share of the websites on the internet. The version this course teaches is PHP 8.6.

## In code

You don't have to run these yet. Read them, and compare them with the output below each one.

### A program that prints one line

<!-- include: code/01-what-is-php/hello.php -->

- `<?php` at the top says: PHP code starts here.
- `echo` prints the text that follows it.
- The text is in quotes. `\n` at the end means "start a new line".
- The `;` ends the instruction, like a full stop ends a sentence.

### A page that PHP fills in

<!-- include: code/01-what-is-php/page.php -->

This file is mostly HTML. Two small pieces are PHP, each between `<?php` and `?>`. When PHP runs the file, it copies the HTML as it is and replaces each PHP piece with what it prints: `3 + 4` becomes `7`.

The output is exactly what a browser would receive: plain HTML, with no PHP left in it.

## Common mistakes

None yet, because there is no code to run. Two misunderstandings are common, though:

- **"PHP runs in the browser."** It doesn't. The browser only ever receives what PHP printed. (The language that runs inside browsers is JavaScript; it is not part of this course.)
- **"I need a web server to learn PHP."** You don't. The terminal is enough for the first parts of this course.

## Principle check

**KISS (keep it simple):** we start in the terminal, without a browser or a web server. Fewer moving parts make it easier to see what your code does.

## Security

The visitor sees only what your program prints, never your code. That is the first line of defence for everything secret: keep it on the server, and never print it.

## Shelf

*Shelf* is the project of this course: a small lending library with books, members and loans. It starts in chapter 6.

## Exercises

These exercises show you a program and ask what it prints. Write your prediction into the file `prediction.txt` next to the program. Once PHP and the course tools are set up (chapters 2 and 3), check your predictions:

```
composer exercise 01 beginner
```

1. **Beginner, `greeting.php`:** a program with two `echo` lines.
2. **Intermediate, `sum.php`:** a program that prints a calculation.
3. **Advanced, `notice.php`:** an HTML page with PHP pieces. Predict the exact HTML the browser receives. Careful: everything outside `<?php` and `?>` is sent as it is, even an HTML comment.

## Summary

- A program is a list of instructions in a programming language.
- On the web, the client (the browser) asks, and the server answers with HTML.
- PHP runs on the server and writes HTML for each request; visitors see only the output.
- PHP also runs in the terminal, which is where this course starts.

## Further reading

- PHP manual, Introduction (what PHP is and what it can do): https://www.php.net/manual/en/introduction.php
- PHP manual, Getting started: https://www.php.net/manual/en/getting-started.php
