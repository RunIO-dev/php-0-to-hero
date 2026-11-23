# 27. Why objects: from arrays to a `Book` class

## Goals

- You will be able to explain what an array shape can't promise, and what a class can.
- You will be able to write a class with typed properties and methods, and create objects of it with `new`.
- You will be able to use `$this` inside a method.
- You will be able to predict when two variables hold the same object, and make a real copy with `clone`.

## Before you start

- Chapter 26: *Shelf* repeats the shape of a book in every PHPDoc comment.
- Chapter 16: you know types, and `strict_types`.
- Chapter 19: you have used exception classes such as `BookNotFound`.

## The idea

Since chapter 13, a book in *Shelf* has been a map: `['title' => 'Kindred', 'author' => ..., 'year' => 1979, 'copies' => 2]`. It works, but nothing in PHP knows that it is a book. A map with a misspelled key, or a year written as text, is a map just the same, and the mistake shows up later, wherever the missing key is read. The PHPDoc comments describe the shape for PHPStan, and they had to be repeated above every function.

A **class** is a description of a kind of thing, written once: what every book *has* (its **properties**: title, author, year, copies) and what every book can *do* (its **methods**: answer whether it is available, describe itself). Each real book is an **object** of that class, also called an **instance**. The class is the form; the objects are the filled-in copies.

With a class, PHP checks what the array couldn't: a property has a type, a misspelled name is noticed, and a function can ask for a `Book` and get nothing else. This way of organising a program around objects is called **object-oriented programming** (OOP). Part 5 teaches it in small steps, always with *Shelf*.

## In code

### What an array can't promise

<!-- include: code/27-why-objects/arrays.php -->

The second book has a typo, `autor`, and its year is a string. PHP accepts both, and `describe` quietly prints "(no author)". Nothing stops a wrong map from being built, or passed on.

### A class and its objects

<!-- include: code/27-why-objects/class.php -->

- `final class Book { ... }` defines the class. Class names start with a capital letter. `final` means no other class can be built on this one; chapter 34 explains why that is a good default.
- `public string $title;` declares a **property** with a type. `public` means code outside the class may read and change it; chapter 29 shows why that is often too much.
- `new Book()` creates an object. `$book->title` reads or sets one of its properties: the arrow `->` is for objects what `['...']` is for arrays.
- `var_dump` shows an object with its class, `Book`, a number that tells objects apart (`#1`), and every property with its value.

### Methods and `$this`

<!-- include: code/27-why-objects/methods.php -->

A **method** is a function written inside the class. You call it on an object: `$kindred->status()`. Inside the method, `$this` is the object it was called on, so the same method answers differently for `$kindred` and `$kitchen`. A method can call another method of the same object: `$this->isAvailable()`.

The data and the code that works with it now live in one place. Before, `statusOf($book)` was a function somewhere else that had to know the shape of the array.

### A class is a type

<!-- include: code/27-why-objects/type.error.php -->

⚠ `function age(Book $book, ...)` accepts only `Book` objects. An array with the same keys is refused at the call, with a `TypeError` that names the problem. The check happens where the mistake is made, not later.

### The same object, or a copy

<!-- include: code/27-why-objects/handles.php -->

Arrays are copied when you pass them to a function or assign them (chapter 18). Objects are not. A variable holds a **handle** to the object, a way to reach it, and passing or assigning copies only the handle:

- `lendOne($book)` changes the caller's book: the function got the same object.
- `$same = $book;` gives a second name for the same object, so `$same->copies = 5` changes `$book` too.
- `clone $book` makes a new, separate object with the same property values. Changing the clone leaves the original alone.

This is the most important difference between arrays and objects. It is useful, because a function can change a book without returning it, and dangerous, because a change made in one place is seen everywhere. Chapter 18 said that the functions in this course don't change their arguments. With objects, some will, on purpose, and their name says so: `lendOne`, `lend`.

## Common mistakes

### A misspelled property

<!-- include: code/27-why-objects/typo.error.php -->

⚠ Two messages. First, PHP warns that `titel` creates a new **dynamic property**, one that the class doesn't declare. This is deprecated: a future PHP version will refuse it. Then the program stops, because `title` was never set: "Typed property Book::$title must not be accessed before initialization". A typed property without a default has no value at all until you give it one, not even `null`.

**Fix:** spell the property as declared. Read the deprecation warnings: each one is a mistake that PHP still lets pass. PHPStan reports the misspelled name before you run the code.

### Forgetting `$this->`

<!-- include: code/27-why-objects/this.error.php -->

⚠ Inside a method, `$copies` is a local variable, and there is none, so PHP warns and uses `null`. The property is `$this->copies`.

**Fix:** reach properties and other methods through `$this->`.

### Expecting a copy

`$backup = $book;` before a change looks like a backup, but both names lead to the same object, so the "backup" changes too.

**Fix:** use `clone` when you need a separate object. Most of the time you don't: chapter 30 shows objects that can't be changed at all, which need no copies.

## Principle check

**KISS:** a class gives the shape of a book one name, in one place, instead of a PHPDoc comment above every function. **DRY:** the rules about a book, such as when it is available, move into the class, next to its data.

## Security

Nothing new here. The class checks types, but not values: a `Book` can still get a year of -5 or a title of spaces. Values are checked where input arrives, as in chapter 24, and chapters 28 to 30 move those checks into the classes themselves.

## Shelf

A book in *Shelf* is now an object of the class `Book`. Its properties replace the array keys, and two functions become methods: `statusOf` and `bookLine` are now `isAvailable()` and `line()`. Every PHPDoc comment that repeated the shape now says `list<Book>`.

Because the books are objects, `lend` changes the book in the list itself, and returns the book, so "Lent:" shows its title without searching again.

Change `shelf/library.php`: the class `Book` takes the place of `statusOf` and `bookLine`, every PHPDoc comment says `list<Book>`, and the functions read `$book->title` where they read `$book['title']`:

<!-- include: code/27-why-objects/shelf/library.php lines 13-33, 64-81, 88-102, 150-162 -->

Change `shelf/books.php` to:

<!-- include: code/27-why-objects/shelf/books.php -->

And change `shelf/shelf.php` to:

<!-- include: code/27-why-objects/shelf/shelf.php -->

`toBook` and `addBook` now set five properties, one line each, right after `new Book()`. That is clumsy, and easy to get wrong: forget one line, and the book has a property without a value. Chapter 28 replaces those lines with a constructor.

Run `php shelf/shelf.php` and the commands from chapter 25: everything prints what it printed before. `json_encode` writes an object's public properties like the keys of a map, so `books.json` keeps its format.

## Exercises

The exercises are in `exercises/27-why-objects/`. Check your work with:

```
composer exercise 27 beginner
```

1. **Beginner, `member.php`:** a `Member` class with a name, a number of loans, and a method that says whether they can borrow more.
2. **Intermediate, `books.php`:** turn rows of data into `Book` objects, and find the oldest.
3. **Advanced, `loans.php`:** change the caller's books in a function, and keep a separate copy of one.

## Summary

- A class describes a kind of thing once: typed properties for what it has, methods for what it does.
- `new Book()` creates an object; `->` reaches its properties and methods; `$this` is the object inside a method.
- A class is a type: `function age(Book $book)` accepts only books.
- Variables hold handles to objects: passing or assigning shares the object; `clone` makes a separate one.
- A typed property without a default must be set before it is read.

## Further reading

- PHP manual, Classes and objects, the basics: https://www.php.net/manual/en/language.oop5.basic.php
- PHP manual, Properties: https://www.php.net/manual/en/language.oop5.properties.php
- PHP manual, Objects and references: https://www.php.net/manual/en/language.oop5.references.php
- PHP manual, Object cloning: https://www.php.net/manual/en/language.oop5.cloning.php
