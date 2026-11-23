# 34. Inheritance and composition

## Goals

- You will be able to build a class on another one with `extends`, and override its methods.
- You will be able to call the parent class's constructor and methods with `parent::`.
- You will be able to choose between "is a" (inheritance) and "has a" (composition), and explain why composition is usually the better choice.
- You will be able to explain why the classes of this course are `final`.

## Before you start

- Chapter 33: interfaces.
- Chapter 28: composition, a `Loan` made of a `Book` and a `Member`.
- Chapter 19: your own exceptions, which already used `extends`.

## The idea

There are two ways to make a class from another class.

**Inheritance:** `class Dvd extends Item`. A `Dvd` *is an* `Item`: it gets all of `Item`'s properties and methods, can add its own, and can **override** a method, replacing it with its own version. Wherever an `Item` is expected, a `Dvd` is accepted. `Item` is the **parent class**, `Dvd` the **child class**.

**Composition:** a class *has* other objects in its properties and uses them, as a `Loan` has a `Book`. You have used it since chapter 28.

Inheritance looks like the faster way to reuse code, and that is its trap. A child class depends on every detail of its parent: change the parent, and every child may break. And it gets *all* the parent's methods, including the ones it should never have. Composition takes only what it uses, through a small interface, and the part can be swapped.

The rule of thumb: **prefer composition**. Use inheritance only for a real "is a" with a parent that is designed for it. Exceptions are the classic case.

That is also why the classes in this course are `final`: a class that nobody may extend can be changed without breaking children it doesn't know about. Remove `final` when a class is designed to be a parent, not "just in case".

## In code

### `extends`, `parent::` and overriding

<!-- include: code/34-inheritance-and-composition/extends.php -->

- `Item` has no `final`, so other classes may extend it.
- `final class Dvd extends Item` gets `title` and `label()` from `Item`.
- Its constructor has an extra parameter, and calls `parent::__construct($title)` so that `Item` can set up its part.
- `label()` **overrides** `Item`'s version. `parent::label()` calls the parent's version, so the child only adds to it.
- `#[Override]` is an **attribute**: a note for PHP, written in `#[...]`. It says "this method replaces one of the parent". If it doesn't, because the parent's method was renamed or removed, PHP reports an error instead of silently keeping two methods.
- `shelfLabel(Item $item)` accepts the `Dvd` too, and calls its own `label()`.

### Where inheritance fits: exceptions

<!-- include: code/34-inheritance-and-composition/exceptions.php -->

`NoCopyLeft` *is a* `ShelfException`, which *is a* `RuntimeException`. Each child adds only a name, nothing to break. `catch (ShelfException $e)` catches every exception of the family, and nothing else. `$e::class` is the name of the exception's real class.

`ShelfException` has no `final`: it is made to be a parent. `never` as a return type says that the function never returns normally: it always throws.

### Composition

<!-- include: code/34-inheritance-and-composition/composition.php -->

`Reminders` has a `Notifier`, given to its constructor, and uses only its `send` method. It works with every notifier from chapter 33, and with the email notifier of a later part, without a change. It could have been `class Reminders extends ScreenNotifier`, but then it would print to the screen, forever, and be a notifier itself.

Composition with an interface as the type, as here, is the most flexible combination: the part can be any class that keeps the promise.

## Common mistakes

### Inheriting to reuse code

<!-- include: code/34-inheritance-and-composition/inherit-to-reuse.bad.php -->

✗ `Library` extends `JsonBookFile` to reuse `all()`. But a library is not a file. It now also has `delete()`, so any code that gets a `Library` can delete all the books, and the library can never use a database instead.

**Fix:** ask "is a `Library` a `JsonBookFile`?". If the answer is no, use composition: give `Library` a repository in its constructor, as *Shelf* does below.

### Extending a final class

<!-- include: code/34-inheritance-and-composition/final.error.php -->

⚠ "Class Ebook cannot extend final class Book". The author of `Book` decided it is not a parent.

**Fix:** usually composition, or an interface that both classes implement. Remove `final` only if the class is meant to be a parent, and then design it for that.

### Forgetting `parent::__construct`

<!-- include: code/34-inheritance-and-composition/parent.error.php -->

⚠ `Dvd` has its own constructor, so `Item`'s constructor never runs, and `title` is never set: "must not be accessed before initialization".

**Fix:** call `parent::__construct(...)` in the child's constructor.

## Principle check

**KISS:** composition keeps each class small and its parts visible in the constructor. **YAGNI:** don't build a parent class for children that may come one day, and keep classes `final` until a child is really needed.

## Security

Inheritance can hand out more than you meant to: every public method of the parent is a public method of the child, as `delete()` showed. With composition, a class exposes only the methods it writes itself, so what other code may do with it is visible in one place.

## Shelf

The rules of *Shelf* were loose functions that each got the list of books passed in. They become one class, `Library`, which *has* a `BookRepository` and the members, both given to its constructor:

- `books()`, `search()`, `lend()` and `add()` are the four things the commands need. `lend()` and `add()` save through the repository themselves.
- `find()`, `member()` and `newBook()` are private helpers. The old functions `findBook`, `findMember`, `sortedByTitle`, `searchTitles`, `lend` and `addBook` are gone.
- `shelf.php` creates the `Library` once, and `run` only translates commands into method calls. `lendAndSave` and `addAndSave` are no longer needed.
- `BookNotFound`, `MemberNotFound` and `NoCopyLeft` get a parent, `ShelfException`, as in the example above, so the `catch` at the end of `shelf.php` names the family instead of each member.

`Library` doesn't extend `JsonBookRepository`: a library is not a file. Because it only knows the `BookRepository` interface, Part 8 can give it a database instead.

Change `shelf/library.php`. Below are the new family of exceptions and `Library`'s public methods; its private helpers `newBook`, `find` and `member` are the checks of the old `addBook`, `findBook` and `findMember`, moved into the class, and the old functions go:

<!-- include: code/34-inheritance-and-composition/shelf/library.php lines 8-15, 185-244 -->

And change `shelf/shelf.php` to:

<!-- include: code/34-inheritance-and-composition/shelf/shelf.php -->

Run `php shelf/shelf.php` and the commands from chapter 31: the output stays the same.

## Exercises

The exercises are in `exercises/34-inheritance-and-composition/`. Check your work with:

```
composer exercise 34 beginner
```

1. **Beginner, `errors.php`:** a family of exceptions, caught with one `catch`.
2. **Intermediate, `lending.php`:** turn "is a log" into "has a log".
3. **Advanced, `items.php`:** a `Dvd` that extends `Item`, with its own loan period and label.

## Summary

- `class Dvd extends Item`: the child gets the parent's properties and methods, and can override them; `parent::` reaches the parent's version.
- `#[Override]` makes PHP check that a method really replaces one of the parent.
- Composition: a class has other objects, ideally typed by an interface, and uses them.
- Prefer composition; use inheritance for a real "is a", such as a family of exceptions.
- Keep classes `final` unless they are designed to be parents.

## Further reading

- PHP manual, Object inheritance: https://www.php.net/manual/en/language.oop5.inheritance.php
- PHP manual, The `final` keyword: https://www.php.net/manual/en/language.oop5.final.php
- PHP manual, The `Override` attribute: https://www.php.net/manual/en/class.override.php
