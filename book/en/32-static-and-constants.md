# 32. Static members and constants, and when not to use static

## Goals

- You will be able to give a fixed value a name inside a class, with a class constant.
- You will be able to write a named constructor, a static method that creates objects, such as `Book::fromArray`.
- You will be able to tell `self::` from `$this->`.
- You will be able to explain why static properties that change are a hidden global, and avoid them.

## Before you start

- Chapter 30: value objects.
- Chapter 18: scope, constants with `const`, and why this course has no global variables.

## The idea

Everything in a class so far belonged to one object: each book has its own title, and `$this->title` reads it. Some things belong to the class as a whole instead:

- A **class constant**, such as the last year a book may have, is the same for every book. `Book::LAST_YEAR` names it, next to the class it is about.
- A **static method** is called on the class, not on an object: `Book::fromArray($data)`. It has no `$this`. Its most useful form is the **named constructor**: a method that makes an object from some input, and whose name says from what.

There is a third kind, the **static property**: a variable that belongs to the class and is shared by all its objects. When it can change, it is a global variable with a class name in front of it, with every problem of chapter 18. This chapter shows why, so that you recognise it.

## In code

### Class constants

<!-- include: code/32-static-and-constants/constants.php -->

- `public const int DAYS = 14;` defines a constant of the class, with a type. Outside the class, you reach it with the class name and `::`: `Loan::DAYS`.
- Inside the class, `self::` means "this class": `self::REMINDER_DAYS`.
- A constant can be `private`, like a method. Only the class uses `REMINDER_DAYS`, so nothing else should depend on it.

A named constant explains a number, and it lives in one place: changing the rule changes one line.

### Named constructors

<!-- include: code/32-static-and-constants/named-constructors.php -->

- `public static function zero(): self` is called on the class: `Money::zero()`. `self` as a type means "an object of this class".
- `new self(...)` creates an object of this class from inside it.
- The real constructor is `private`, so `new Money(...)` works only inside the class. Every `Money` is made by one of the named constructors, and each checks its own input.

Names say more than one `__construct` can: `Money::fromEuros('1.5')` and `Money::zero()` read like what they do. `DateTimeImmutable::createFromFormat` from the PHP library is a named constructor too.

### `self::` and `$this->`

`$this->title` is a property of one object, and exists only inside a method called on an object. `self::LAST_YEAR` and `self::fromArray()` belong to the class, and work in any method. A static method has no object, so it can't use `$this`, as the mistakes below show.

## Common mistakes

### `$this` in a static method

<!-- include: code/32-static-and-constants/this.error.php -->

⚠ "Using `$this` when not in object context". `Book::describe()` is called on the class, so there is no book whose title it could use.

**Fix:** if a method needs an object's data, don't make it static. Call it on the object: `$book->describe()`.

### `new` with a private constructor

<!-- include: code/32-static-and-constants/private-constructor.error.php -->

⚠ "Call to private Money::__construct() from global scope". The class decided that objects are made only through its named constructors.

**Fix:** use one of them, here `Money::zero()`.

### Static properties as shared state

<!-- include: code/32-static-and-constants/shared.php -->

The South branch has no books, but says it has 2. `private static array $titles` is one list for the whole class, so every branch adds to the same list. Worse, the list lives as long as the program, so in a test suite, one test's books show up in the next test. Nothing in `new Branch('South')` warns you that it depends on what other code did before.

**Fix:** keep data in the object (`$this->titles`). Use `static` for constants and for methods that need no changing data, such as named constructors. When something really is shared, such as the books of a library, make it an object and pass it to whoever needs it, as chapter 33 does.

## Principle check

**KISS:** class constants and named constructors make code say what it means. **Secure by default:** a private constructor with named constructors means there is no way to make an object without its checks. **YAGNI:** a static property that changes is never needed in this course; it only hides where data comes from.

## Security

Nothing new here. A constant is not a place for secrets such as passwords or API keys: it is in the code, and code is shared, copied and committed. Part 7 keeps secrets in environment variables.

## Shelf

Two things in *Shelf* move to where they belong:

- **Turning data into objects, and back.** `toBook` and `toMember` in `data.php` become named constructors: `Book::fromArray` and `Member::fromArray`. Their opposite, the row that `saveBooks` writes, becomes `Book::toArray`. A class now knows how it is written to a file and read back; `data.php` only reads and writes files.
- **The year range.** "From 1 to 2100" was written in `addBook` twice, in the check and in the message. It is now `Book::FIRST_YEAR` and `Book::LAST_YEAR`, and the constructor checks it too, so a book from the year 3000 can't come from `books.json` either.

Change `shelf/library.php` to:

<!-- include: code/32-static-and-constants/shelf/library.php -->

Change `shelf/data.php` to:

<!-- include: code/32-static-and-constants/shelf/data.php -->

And change the comment at the top of `shelf/shelf.php`:

<!-- include: code/32-static-and-constants/shelf/shelf.php -->

Run `php shelf/shelf.php` and the commands from chapter 31: the output stays the same.

## Exercises

The exercises are in `exercises/32-static-and-constants/`. Check your work with:

```
composer exercise 32 beginner
```

1. **Beginner, `fee.php`:** the late fee with named constants and a static method.
2. **Intermediate, `card.php`:** card numbers that can only be made through named constructors.
3. **Advanced, `branch.php`:** find and fix the bug of a static property that every branch shares.

## Summary

- A class constant (`public const int DAYS = 14;`) names a fixed value of the class: `Loan::DAYS`, or `self::DAYS` inside.
- A static method is called on the class and has no `$this`.
- A named constructor (`Money::fromEuros`) creates objects, and says from what; with a private constructor, it is the only way in.
- A static property that changes is shared global state: keep data in objects instead.
- Never keep secrets in constants.

## Further reading

- PHP manual, Class constants: https://www.php.net/manual/en/language.oop5.constants.php
- PHP manual, Static keyword: https://www.php.net/manual/en/language.oop5.static.php
- PHP manual, Scope resolution operator (`::`): https://www.php.net/manual/en/language.oop5.paamayim-nekudotayim.php
