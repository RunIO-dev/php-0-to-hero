# 33. Interfaces

## Goals

- You will be able to describe what an object can do, without saying how, with an interface.
- You will be able to write several classes that implement the same interface, and code that works with any of them.
- You will be able to use PHP's own interfaces, such as `Countable` and `JsonSerializable`.
- You will be able to hide where data is kept behind a repository interface.

## Before you start

- Chapter 32: classes, static methods, named constructors.
- Chapter 27: a class is a type.

## The idea

A function that sends a reminder needs something that can send a message. It doesn't care whether the message goes to the screen, into a log, or later, by email. Until now, a parameter's type was a class, and a class decides both *what* an object does and *how*.

An **interface** describes only the *what*: a list of methods, with their parameters and return types, but no code. A class that **implements** the interface promises to have all those methods. A parameter typed with the interface accepts an object of any class that keeps the promise.

`interface Notifier { public function send(string $name, string $message): void; }` says: "a notifier can send a message to someone". `ScreenNotifier` prints it, `CollectingNotifier` collects it. `remind(Notifier $notifier, ...)` works with both, and with any notifier written later, without a change.

*Shelf* uses this for a bigger question: where are the books kept? Today in a JSON file, in Part 8 in a database. If the rules only know an interface, "something that gives me all books and saves them", the storage can change without touching the rules.

## In code

### An interface and two classes

<!-- include: code/33-interfaces/interface.php -->

- `interface Notifier { ... }` lists the method with its signature and a `;` instead of a body.
- `final class ScreenNotifier implements Notifier` promises to have that method, with the same signature.
- `remind(Notifier $notifier, ...)` asks only for the interface. Which class it gets is decided by the caller, not by `remind`.

### A repository

<!-- include: code/33-interfaces/repository.php -->

A **repository** is an object that stores and returns objects of one kind, such as books, and hides how. Code that needs books asks the `BookRepository` interface for them. `InMemoryBookRepository` keeps them in an array, which is useful for tests and examples; a `JsonBookRepository` keeps them in a file; a database version comes in Part 8. `titleList` works with all of them.

The PHPDoc `@return list<Book>` is written once, on the interface. PHPStan applies it to every class that implements it.

### PHP's own interfaces

<!-- include: code/33-interfaces/built-in.php -->

PHP has interfaces of its own, and some of its functions use them:

- A class that implements `Countable` can be passed to `count()`, which calls its `count()` method.
- `JsonSerializable` lets a class decide what `json_encode` writes, through `jsonSerialize()`. Here, the private list of titles is written, which `json_encode` would otherwise leave out (chapter 29).

A class can implement several interfaces, separated by commas.

## Common mistakes

### A method missing

<!-- include: code/33-interfaces/missing.error.php -->

⚠ `ScreenNotifier` promises to be a `Notifier`, but has no `send` method. PHP refuses the class as soon as the file is loaded: it "must ... implement the remaining method (Notifier::send)". The word "abstract" in the message is explained in chapter 35.

**Fix:** write every method the interface lists.

### A different signature

<!-- include: code/33-interfaces/signature.error.php -->

⚠ The method exists, but with other parameters, so code that calls `send($name, $message)` would break. PHP refuses it: "must be compatible with Notifier::send(string $name, string $message): void".

**Fix:** copy the signature from the interface.

### `new` with an interface

<!-- include: code/33-interfaces/new.error.php -->

⚠ An interface has no code, so there is nothing to create: "Cannot instantiate interface Notifier".

**Fix:** create an object of a class that implements it: `new ScreenNotifier()`.

### An interface for everything

An interface for a class that will only ever have one implementation adds a file and a name, and saves nothing.

**Fix:** write an interface when there are two implementations, or when you know a second is coming, as with *Shelf*'s database in Part 8. Tests are often the second one: an in-memory repository instead of a file.

## Principle check

**KISS:** code that depends on an interface knows less, so it is simpler, and easier to test. **YAGNI:** add an interface when a second implementation exists or is planned, not for every class. **DRY:** the PHPDoc types live on the interface, once.

## Security

An interface describes what a class does, not that it does it safely. Every implementation is checked on its own: in Part 8, the database repository uses prepared statements, whatever the interface says. In return, an interface makes it easy to swap in a safe implementation everywhere at once.

## Shelf

*Shelf*'s rules get an interface for storage, `BookRepository`, with two methods: `all()` returns the books, `save()` stores them. It is defined in `library.php`, next to the rules that use it, because the rules decide what they need from storage.

`loadBooks` and `saveBooks` become one class in `data.php`, `JsonBookRepository`, which keeps the books in `books.json`. Its constructor takes the path, so the path is written once, where the program starts.

`run` gets a `BookRepository` instead of a folder. It doesn't know about files any more: in Part 8, the same `run` will work with a database.

`data.php` now implements an interface from `library.php`, so `library.php` must be loaded first. Change `shelf/library.php` to:

<!-- include: code/33-interfaces/shelf/library.php -->

Change `shelf/data.php` to:

<!-- include: code/33-interfaces/shelf/data.php -->

And change `shelf/shelf.php` to:

<!-- include: code/33-interfaces/shelf/shelf.php -->

Run `php shelf/shelf.php` and the commands from chapter 31: the output stays the same. The order of `require_once` now matters, which is easy to get wrong in a growing program; chapter 36 replaces it with autoloading.

## Exercises

The exercises are in `exercises/33-interfaces/`. Check your work with:

```
composer exercise 33 beginner
```

1. **Beginner, `greeting.php`:** two classes for one interface, and a function that works with any of them.
2. **Intermediate, `members.php`:** a member repository that keeps its members in memory.
3. **Advanced, `clock.php`:** a clock that can be fixed, so that code that needs "now" can be tested.

## Summary

- An interface lists methods without code; a class `implements` it and must have them all, with the same signatures.
- A parameter typed with an interface accepts any class that implements it.
- A repository hides where objects are kept: a file, memory, or a database.
- PHP's own interfaces, such as `Countable` and `JsonSerializable`, connect your classes to its functions.
- Write an interface when there is, or will be, a second implementation.

## Further reading

- PHP manual, Object interfaces: https://www.php.net/manual/en/language.oop5.interfaces.php
- PHP manual, `Countable`: https://www.php.net/manual/en/class.countable.php
- PHP manual, `JsonSerializable`: https://www.php.net/manual/en/class.jsonserializable.php
