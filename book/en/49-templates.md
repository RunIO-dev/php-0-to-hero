# 49. Templates: HTML apart from logic

## Goals

- You will be able to keep HTML in template files, apart from the code that decides what to show.
- You will be able to write a template as a function with typed parameters, and render it into a string.
- You will be able to explain why a wrong name or type in a template's data should stop the page, not leave a gap.
- You will be able to wrap every page of *Shelf* in one layout, and give every page a design with one stylesheet.

## Before you start

- Chapter 48: the front controller, `App`, `ob_start()` and `ob_get_clean()`.
- Chapter 17: named arguments.
- Chapter 46: escaping with `e()`.

## The idea

In chapter 48, `App` does two jobs: it decides what to answer, and it holds the HTML of every page. Each page repeats the same frame, `<!doctype html>`, `<head>`, `<title>`, and a change to the design means searching through PHP code.

A **template** is a file that holds the HTML of one page or one part of a page, with spots for values. The code decides *what* to show and hands the values over; the template decides *how* it looks. A **layout** is the template of the frame that every page shares.

The question is how the values get into the template. The common old way is `extract($data)`, which turns every key of an array into a variable. It is short, and it has two problems: a variable that the data doesn't contain is a warning and an empty spot on the page, and data from outside can overwrite any variable the template uses. This course forbids `extract`.

The way this course uses is plain PHP: **a template file returns a function**. Its parameters, with types, are everything the template needs. Rendering calls that function with the data as named arguments. A wrong name or a wrong type is an error at once, with the name in the message, as with any function call. PHPStan checks templates like any other code.

## In code

### A template

<!-- include: code/49-templates/templates/book.php -->

`return function (...) { ?> ... <?php };` is a function whose body is HTML. Inside it, `?>` switches to HTML and `<?php` back, as in a page; `<?=` prints a value, escaped. Nothing is printed when the file is loaded: only when the function is called.

### Rendering it

<!-- include: code/49-templates/render.php -->

- `require` runs the file and gives back what it returns: the function.
- `$template(...$data)` calls it. `...` spreads the array into arguments; a string key becomes a **named argument**: `['title' => 'Tom & Jerry']` is the argument `title: 'Tom & Jerry'`.
- `ob_start()` and `ob_get_clean()` catch what the template prints, and return it as a string. The caller decides what to do with it: send it, put it into a layout, or test it.

### A wrong name, a wrong type

<!-- include: code/49-templates/typo.error.php -->

<!-- include: code/49-templates/wrong-type.error.php -->

⚠ Both stop with a message that says what is wrong and where. With `extract`, the first would show a page with a missing title and a warning in the log, which nobody reads.

### What belongs in a template

A template shows data: it escapes values, loops over lists, and shows or hides a part with `if`. It doesn't load files, query a database, check a form or decide a status code. That is the job of the code that renders it, here `App`. When a template needs a value, it gets it as a parameter.

## Common mistakes

### Printing instead of returning

A template file that has HTML outside the function prints it as soon as it is loaded, before the headers are set, and before the layout.

**Fix:** the file contains nothing but `declare`, `use` lines and `return function (...) { ... };`. All HTML is inside the function.

### A template that fetches its own data

`<?php foreach ($library->books() as $book): ?>` inside a template works, and hides the work a page does in its HTML. The page can no longer be tested with other data.

**Fix:** pass the books as a parameter. The template gets data, not the objects that fetch it.

### Escaping the content of the layout

The layout prints `$content`, the HTML of the page inside it. Escaped with `e()`, the page would show its own tags as text.

**Fix:** escape values, not HTML that a template has rendered. The layout's `$content` is the one value printed as it is, and its name says so. Every value inside it was escaped by its own template.

## Principle check

**DRY:** the frame of every page is written once, in the layout. **KISS:** a template is a PHP function; no new language, no library. **Secure by default:** values are escaped in the template, every time; no `extract`, so data can't create or overwrite variables.

## Security

Templates are where XSS is stopped: every value is escaped at the moment it is printed, for the place it is printed to (chapter 46). Keep that rule with a simple habit: inside a template, every `<?=` has `e(...)`, except a number and the layout's `$content`. Part 11 uses Twig, Symfony's template language, which escapes every value on its own unless told otherwise; the ideas of this chapter are the same there.

## Shelf

*Shelf*'s HTML moves out of `App` into a new folder, `shelf/templates/`, next to `src/` and outside `public/`. And since every page now shares one layout, this is the moment *Shelf* gets a design: one stylesheet, linked once in the layout, styles every page.

**The stylesheet.** This course teaches PHP, not CSS, so the stylesheet is ready to copy: download `style.css` from the course repository, https://github.com/RunIO-dev/php-0-to-hero/blob/main/code/49-templates/shelf/public/style.css, into `shelf/public/style.css`. It is plain CSS, with no framework and no build step:

- the books as cards in a grid, one column on a phone;
- a bar at the top of every page, with the way to add a book;
- forms with large fields, and messages in colour: green for success, red for a mistake;
- dark mode, when the visitor's system uses it, and visible outlines for keyboard users.

The file is in `public/`, so the built-in server sends it as it is, at `/style.css`, without running `index.php`: a path that is a file in `public/` is served directly (chapter 48).

**The layout,** `shelf/templates/layout.php`:

<!-- include: code/49-templates/shelf/templates/layout.php -->

- `<meta name="viewport">` tells phones to use their real width, instead of shrinking a desktop page.
- `<link rel="stylesheet" href="/style.css">` loads the stylesheet; `class="..."` attributes in the templates name the parts it styles.
- `<header>` and `<main>` say what each part of the page is, for screen readers too.

**The book list,** `shelf/templates/list.php`, shows each book as a card:

<!-- include: code/49-templates/shelf/templates/list.php -->

- An empty shelf says what to do next, instead of showing an empty list: a small piece of good **UX**, user experience. A page should never leave a visitor wondering what now.
- Each card shows the first letter of the title where a cover will go (chapter 52); `aria-hidden="true"` tells screen readers to skip it, since the title follows.

`Book ...$books` is a **variadic** parameter: `...` before it collects any number of arguments into an array, here each one checked to be a `Book`. Rendering spreads the list of books into it, one book per argument. A plain `array` parameter couldn't say what is in the array in a way PHPStan reads for a template function; the variadic says it in the type.

**The form,** `shelf/templates/add.php`:

<!-- include: code/49-templates/shelf/templates/add.php -->

Every field has its label, so a click on the label puts the cursor into the field. The optional field says so. The button says what it does, "Add the book", rather than "Submit". `role="alert"` lets screen readers announce a refused form at once.

**Rendering,** `shelf/src/Web/Templates.php`:

<!-- include: code/49-templates/shelf/src/Web/Templates.php -->

- `render()` is the function from above. It accepts only names of letters, digits and dashes, so no name can reach a file outside `templates/`, such as `../src/functions`. `finally` closes the output buffer even when the template fails.
- `page()` renders a template, and puts the result into the layout.

**The app** is shorter, and holds no HTML any more. `shelf/src/Web/App.php`:

<!-- include: code/49-templates/shelf/src/Web/App.php -->

`[...$fields, 'error' => $error]` spreads the four fields and adds the error: the five parameters of `add.php`.

**The front controller** creates the `Templates`. `shelf/public/index.php`:

<!-- include: code/49-templates/shelf/public/index.php -->

**The tests.** `AppTest` creates its `App` with the templates:

<!-- include: code/49-templates/shelf/tests/Web/AppTest.php -->

and `shelf/tests/Web/TemplatesTest.php` checks the layout, the empty shelf, a wrong name, and that only template names are loaded:

<!-- include: code/49-templates/shelf/tests/Web/TemplatesTest.php -->

Add `templates` to PHPStan's paths in `shelf/phpstan.neon.dist`:

<!-- include: code/49-templates/shelf/phpstan.neon.dist -->

`shelf/web.http` sends the same requests as in chapter 48. The answers have the same statuses and the same data, in the new layout:

<!-- include: code/49-templates/shelf/web.http -->

Start the server (`docker compose up web`) and open `http://localhost:8000`: *Shelf* now looks like an app. Try it on a phone-sized window too: the cards move into one column.

## Exercises

The exercises are in `exercises/49-templates/`. Check your work with:

```
composer exercise 49 beginner
```

1. **Beginner, `card.php`:** a template for a member's card, with typed parameters and every value escaped.
2. **Intermediate, `table.php`:** a template for a table of books that shows a message when there are none.
3. **Advanced, `view.php`:** a `View` class that renders a template inside a layout, and refuses names that aren't templates.

## Summary

- A template holds the HTML; the code that renders it decides what to show.
- In this course, a template file returns a function with typed parameters; rendering calls it with named arguments.
- A wrong name or type stops at once, with a clear message; `extract` is forbidden.
- `ob_start()` and `ob_get_clean()` turn what a template prints into a string.
- A layout holds the frame of every page; its `$content` is the one value not escaped.
- One stylesheet, linked in the layout, gives every page the same design; *Shelf*'s is ready to copy.

## Further reading

- PHP manual, Function arguments (named arguments, `...` spreading): https://www.php.net/manual/en/functions.arguments.php
- PHP manual, Anonymous functions: https://www.php.net/manual/en/functions.anonymous.php
- PHP manual, Output control (`ob_start`, `ob_get_clean`): https://www.php.net/manual/en/book.outcontrol.php
