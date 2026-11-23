# 36. Namespaces and autoloading with Composer

## Goals

- You will be able to put classes into a namespace, and use them with their full name or with `use`.
- You will be able to explain how PHP finds the file of a class with an autoloader, and the PSR-4 rule that connects class names to file paths.
- You will be able to write a `composer.json` for your own project, and let Composer write the autoloader.
- You will be able to organise a project as one class per file in `src/`.

## Before you start

- Chapter 35: *Shelf* has a dozen classes in two files.
- Chapter 26: splitting a program into files with `require_once`.
- Chapter 3: Composer is installed (`composer --version`).

## The idea

*Shelf* has grown to a dozen classes in two files, plus a script. Two problems come with that size.

**Names.** Every class name is global: there can be only one `Book` in a program. When *Shelf* uses a package from someone else, in Part 6, that package may have a `Book` too. A **namespace** is a family name for classes: `Shelf\Book` and `Shop\Book` are two different classes. It works like folders for files.

**Loading.** In chapter 33, `data.php` had to be loaded after `library.php`, or PHP didn't know the interface yet. With one file per class, a list of `require_once` lines in the right order would grow with every class. An **autoloader** solves this: a function that PHP calls the first time it meets a class it doesn't know, with the class's full name. The autoloader finds the file and loads it. Nothing is loaded that isn't used, and the order no longer matters.

How does the autoloader know where `Shelf\Library` lives? By a rule that all PHP projects share, **PSR-4**: the namespace prefix `Shelf\` stands for the folder `src/`, and the rest of the name is the path. `Shelf\Library` is `src/Library.php`; `Shelf\Storage\Json` would be `src/Storage/Json.php`. PSR-4 is one of the standards of PHP-FIG, a group of PHP projects that agree on such rules.

**Composer** reads the rule from a file, `composer.json`, and writes the autoloader into `vendor/autoload.php`. You have used Composer since chapter 3 to install the course's tools; now *Shelf* gets a `composer.json` of its own.

## In code

### A namespace

<!-- include: code/36-namespaces-and-autoloading/namespace.php -->

- `namespace Shelf;` at the top of the file puts everything below it into the namespace `Shelf`. It comes right after `declare`.
- `Book` inside the namespace means `Shelf\Book`. `Book::class` gives the full name as a string, and so does `$book::class`.
- By convention, the namespace starts with the project's name, in the same spelling as a class.

### `use`

<!-- include: code/36-namespaces-and-autoloading/use.php -->

Two classes called `Book` live side by side, in two namespaces. (A file usually has one namespace; the braces `namespace X { ... }` let this example show three in one file.)

- `use Library\Catalog\Book;` lets the code below write `Book` for the full name.
- `use Shop\Book as ShopBook;` gives a second class a different short name, so both can be used in one file.
- `use` only shortens names. It loads nothing.

### PHP's own classes and functions

<!-- include: code/36-namespaces-and-autoloading/global.php -->

PHP's own classes, such as `DateTimeImmutable` and the exceptions, live in the **global namespace**, the one without a name. Inside `namespace Shelf`, `DateTimeImmutable` would mean `Shelf\DateTimeImmutable`, so you write `use DateTimeImmutable;` at the top, or `\DateTimeImmutable` with a leading backslash, which means "from the global namespace".

Functions are different: if the namespace has no `strlen`, PHP falls back to the global one. That is why PHP's functions work in a namespace without a `use`.

### `composer.json` and the autoloader

*Shelf*'s `composer.json`, which the Shelf section below creates:

<!-- include: code/36-namespaces-and-autoloading/shelf/composer.json -->

- `name` is the project's name, in the form `vendor/project`.
- `require` lists what the project needs. For now, only PHP itself, version 8.5 or newer. Part 6 adds packages here.
- `autoload` → `psr-4` → `"Shelf\\": "src/"` is the rule: classes whose names start with `Shelf\` are in `src/`. In JSON, a backslash is written twice.
- `autoload` → `files` lists files that are loaded on every run. PSR-4 is for classes; functions can't be autoloaded, so `src/functions.php`, with *Shelf*'s four functions, is listed here.

`composer dump-autoload`, run in the folder of `composer.json`, writes `vendor/autoload.php`. A script loads it once, with `require __DIR__ . '/vendor/autoload.php';`, and from then on every class of the project is found when it is first used.

## Common mistakes

### A global class without `use`

<!-- include: code/36-namespaces-and-autoloading/missing-use.error.php -->

⚠ Inside `namespace Shelf`, `DateTimeImmutable` means `Shelf\DateTimeImmutable`, which doesn't exist: 'Class "Shelf\DateTimeImmutable" not found'.

**Fix:** add `use DateTimeImmutable;` at the top of the file. PHPStan and editors report the missing `use` before you run the code.

### A file name that doesn't match the class

The autoloader looks for `Shelf\Library` in `src/Library.php`, exactly. A class in `src/library.php` (a small letter), or a file whose namespace doesn't match its folder, is found on one computer and not on another: Linux tells upper and lower case apart, Windows and macOS usually don't. The error is "Class ... not found".

**Fix:** one class per file, the file named exactly like the class, the folders exactly like the namespace.

### Forgetting the autoloader

A new project, or a fresh copy of it, has no `vendor/` folder: it is generated, so it is not kept in git. `require __DIR__ . '/vendor/autoload.php'` then fails with "Failed opening required".

**Fix:** run `composer dump-autoload` once in the folder with `composer.json`. With packages, from Part 6, `composer install` writes it too. Run it again after changing the `autoload` section.

## Principle check

**KISS:** one class per file, named like the class, and one rule for all files: anybody can find a class without searching. **DRY:** the `require_once` lines, and their order, are gone; `composer.json` says it once.

## Security

`vendor/` holds code that runs with your program, so keep it out of a folder that a web server shows to the world. In Part 7, only one folder, `public/`, is visible on the web, and `vendor/` sits next to it, not inside. And never commit `vendor/`: it is generated, and Part 6 shows how `composer.lock` records exactly what was installed.

## Shelf

*Shelf* becomes a Composer project, with the namespace `Shelf`:

- Every class and enum moves into its own file in `shelf/src/`, named like the class. The code inside doesn't change; each file starts with `namespace Shelf;` and a `use` line for each of PHP's own classes it needs.
- The four functions move into `shelf/src/functions.php`.
- `shelf/library.php` and `shelf/data.php` are gone. `shelf/shelf.php` loads `vendor/autoload.php` instead of the two files, and names the classes it uses with `use`. `use function Shelf\loadMembers;` does for a function what `use` does for a class.

The output is the same as before: this chapter changes where the code lives, not what it does.

Create `shelf/composer.json` as shown above. Then create the folder `shelf/src/` and the files in it, and delete `shelf/library.php` and `shelf/data.php`.

`shelf/src/ShelfException.php`:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/ShelfException.php -->

`shelf/src/BookNotFound.php`:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/BookNotFound.php -->

`shelf/src/MemberNotFound.php`:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/MemberNotFound.php -->

`shelf/src/NoCopyLeft.php`:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/NoCopyLeft.php -->

`shelf/src/Role.php`:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/Role.php -->

`shelf/src/Isbn.php`:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/Isbn.php -->

`shelf/src/Book.php`:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/Book.php -->

`shelf/src/Member.php`:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/Member.php -->

`shelf/src/Loan.php`:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/Loan.php -->

`shelf/src/BookRepository.php`:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/BookRepository.php -->

`shelf/src/Library.php`, the class `Library` from `library.php`, unchanged, with its namespace and `use` lines:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/Library.php lines 1-14 -->

`shelf/src/JsonBookRepository.php`:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/JsonBookRepository.php -->

`shelf/src/functions.php`:

<!-- include: code/36-namespaces-and-autoloading/shelf/src/functions.php -->

And change `shelf/shelf.php` to:

<!-- include: code/36-namespaces-and-autoloading/shelf/shelf.php -->

Then write the autoloader, and run *Shelf* as before:

```
composer dump-autoload --working-dir=shelf
php shelf/shelf.php
```

`--working-dir=shelf` runs Composer as if you were in the folder `shelf/`. The repository's `.gitignore` already keeps `shelf/vendor/` out of git.

## Exercises

The exercises are in `exercises/36-namespaces-and-autoloading/`. Check your work with:

```
composer exercise 36 beginner
```

1. **Beginner, `catalog.php`:** put a class and a function into a namespace, and make PHP's own class work there.
2. **Intermediate, `psr4.php`:** turn a class name into a file path, the PSR-4 way.
3. **Advanced, `autoloader.php`:** write and register an autoloader of your own.

## Summary

- A namespace is a family name for classes: `Shelf\Book`. `namespace Shelf;` starts it; `use` shortens names, and loads nothing.
- PHP's own classes are global: `use DateTimeImmutable;` or `\DateTimeImmutable` inside a namespace.
- An autoloader loads a class's file the first time the class is used; PSR-4 maps `Prefix\Name` to `folder/Name.php`.
- `composer.json` holds the rule; `composer dump-autoload` writes `vendor/autoload.php`; never commit `vendor/`.
- One class per file, named exactly like the class.

## Further reading

- PHP manual, Namespaces: https://www.php.net/manual/en/language.namespaces.php
- PHP manual, Autoloading classes: https://www.php.net/manual/en/language.oop5.autoload.php
- PHP-FIG, PSR-4: Autoloader: https://www.php-fig.org/psr/psr-4/
- Composer, Autoloading: https://getcomposer.org/doc/01-basic-usage.md#autoloading
- Composer, The `composer.json` schema: https://getcomposer.org/doc/04-schema.md
