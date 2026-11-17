# 37. SOLID in plain words

## Goals

- You will be able to explain each of the five SOLID principles in one sentence, with an example.
- You will be able to recognise them in code you already wrote.
- You will be able to tell when applying a principle simplifies code, and when it only adds names and files.

## Before you start

- Chapters 27 to 36: classes, interfaces, composition, namespaces.
- Chapter 14: the rule of three.

## The idea

**SOLID** is a set of five design principles for classes, collected by Robert C. Martin around the year 2000 and named after their first letters. You will meet the word in job interviews, code reviews and framework documentation. Each principle answers one question: what makes code easy to change later?

| Letter | Principle | In plain words |
|---|---|---|
| S | Single responsibility | A class has one reason to change. |
| O | Open/closed | Add new behaviour by adding code, not by editing code that works. |
| L | Liskov substitution | A class that implements an interface keeps all of its promises. |
| I | Interface segregation | Many small interfaces are better than one big one. |
| D | Dependency inversion | Rules depend on interfaces, not on details such as files or databases. |

You have applied all five in Part 5, mostly without the names. This chapter gives them names, shows one small example each, and draws the line this course keeps: a principle is a tool for making code simpler. When it makes code longer and harder to follow, don't apply it.

## In code

### S: one reason to change

<!-- include: code/37-solid/single.php -->

The wording of a reminder changes when the library rewrites its letters. How it is sent changes when it moves from the screen to email. Two reasons, so two classes: `ReminderText` and `ScreenSender`. Each can change, and be tested, without the other.

"Reason to change" is the key, not "does one thing". `Library` in *Shelf* has four public methods, but one reason to change: the rules of lending.

### O: open for new rules, closed for changes

<!-- include: code/37-solid/open-closed.php -->

`Checkout` applies whatever discounts it is given. A new kind of discount, such as `Cap`, is a new class; `Checkout` doesn't change, and nothing that already works can break. The tool behind it is an interface (chapter 33).

Without it, every new rule would be another `if` inside `total()`, and every change would risk the rules that are already there.

### L: keep every promise of the interface

<!-- include: code/37-solid/substitution.php -->

✗ `DisplayCase` says it is a `Rack`, but `add()` throws. `stock()` was written for any `Rack`, and fails with this one. Code that receives an interface must be able to trust it, whatever class is behind it: that is what "substitution" means.

The fix is never "check which class it is" (`instanceof DisplayCase`). Either the class can keep the promise, or it shouldn't claim the interface, which leads to the next letter.

### I: small interfaces

<!-- include: code/37-solid/segregation.php -->

Reading and writing are two interfaces. `Archive` can be read but not written, so it implements only `BookReader`, and keeps every promise it makes. `catalog()` only reads, so it asks only for a `BookReader`, and works with archives and with writable shelves alike.

### D: rules depend on interfaces

<!-- include: code/37-solid/inversion.php -->

`Overdue` is a rule. It needs today's date, but it doesn't create a clock itself; it asks for a `Clock` in its constructor. The rule decides what it needs (the interface), and the details (a fixed clock in a test, the system clock in the program) are plugged in from outside. You did this in the advanced exercise of chapter 33. Passing what a class needs into its constructor is called **dependency injection**; Symfony, in Part 11, does it for you.

## Common mistakes

### Applying SOLID everywhere

<!-- include: code/37-solid/too-much.bad.php -->

✗ An interface, an implementation and a comparator class for what *Shelf* does in one function, `sameTitle`. Nothing here will ever have a second implementation. The "principles" add three names, and the reader has to open three places to learn that titles are compared in any case.

**Fix:** apply a principle when it removes a real problem you have now, or will have soon: a second implementation, a test that needs a fake, a class that changes for two reasons. Otherwise, keep the function. KISS and YAGNI come first.

### Checking the class behind an interface

`if ($rack instanceof DisplayCase) { ... }` in code that should work with any `Rack` is a sign that a class breaks the interface's promises (L). Every new class then needs another `if`, which also breaks O.

**Fix:** split the interface (I), so each class claims only what it can keep.

### Creating dependencies inside a class

`new JsonBookRepository('books.json')` inside `Library` would tie the rules to one file forever, and a test could never give it other books.

**Fix:** ask for the dependency in the constructor, typed by an interface (D), and create it where the program starts, as `shelf.php` does.

## Principle check

**KISS** and **YAGNI** decide when SOLID applies: each principle is worth its extra names only when it makes the code simpler to change. **DRY** and **S** work together: one rule in one place, and one place with one reason to change.

## Security

Dependency inversion helps security in a practical way: the code that touches the outside world (files, the database, the network) sits behind a few interfaces, in a few classes. Those are the places to review for injection and path problems, and to replace with safe versions, without touching the rules.

## Shelf

*Shelf* stays as it is: Part 5 already built it along these lines. Read it once more with the letters in mind:

- **S:** `Book` keeps a book's rules, `JsonBookRepository` the file, `Library` the lending rules, and `shelf.php` the command line. Each changes for its own reason.
- **O:** a new storage is a new class that implements `BookRepository`. `Library` won't change for the database in Part 8.
- **L:** `JsonBookRepository` keeps both promises of `BookRepository`, so `Library` can trust any repository it gets.
- **I:** `BookRepository` has two methods, and `Library` uses both. Splitting it would add names without removing a problem, so it stays one interface.
- **D:** `Library` asks for a `BookRepository` in its constructor; `shelf.php` decides which one.

Where *Shelf* doesn't use a principle, it is on purpose: `sameTitle` and `tidy` are functions, not classes behind interfaces, because nothing needs another version of them.

Part 5 is complete. *Shelf* is a small, tidy object-oriented program in a Composer project. Part 6 adds the professional tools around it: packages, code style, static analysis, tests and Git.

## Exercises

The exercises are in `exercises/37-solid/`. Check your work with:

```
composer exercise 37 beginner
```

1. **Beginner, `report.php`:** split a report class that both builds lines and writes a file (S).
2. **Intermediate, `discounts.php`:** add two discount rules without changing the checkout (O).
3. **Advanced, `books.php`:** split an interface so that no class has to break a promise (L and I).

## Summary

- S: one reason to change per class. O: add behaviour with new code, not edits.
- L: a class keeps every promise of the interface it implements; no `instanceof` checks for exceptions.
- I: small interfaces; ask only for what you use.
- D: rules depend on interfaces, given to their constructors; details are chosen where the program starts.
- Apply a principle only when it simplifies; KISS and YAGNI come first.

## Further reading

- PHP manual, Object interfaces: https://www.php.net/manual/en/language.oop5.interfaces.php
- Symfony docs, The DependencyInjection component: https://symfony.com/doc/current/components/dependency_injection.html
