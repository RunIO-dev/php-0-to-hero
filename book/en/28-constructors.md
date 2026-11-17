# 28. Constructors and constructor property promotion

## Goals

- You will be able to give every object its values at the moment it is created, with a constructor.
- You will be able to declare and set properties in one place, with constructor property promotion.
- You will be able to refuse invalid values in the constructor, so that every object is valid from the start.
- You will be able to build objects out of other objects, such as a loan made of a book and a member.

## Before you start

- Chapter 27: classes, properties, methods and `$this`.
- Chapter 17: default values and named arguments.
- Chapter 19: throwing exceptions.

## The idea

In chapter 27, a new book was made in six lines: `new Book()`, then one line per property. Forget one line, and the book has a property without a value, which fails later, when it is read.

A **constructor** is a method named `__construct` that PHP calls inside `new`. Its parameters are the values the object needs, so `new Book('Kindred', 'Octavia E. Butler', 1979)` can't be written without them. The object is complete from its first moment, or it isn't made at all.

The constructor is also the right place to refuse values that make no sense: a member without a name, a negative number of copies. If the constructor checks them, every object of the class is valid, and no other code has to check again.

## In code

### A constructor

<!-- include: code/28-constructors/constructor.php -->

- `__construct` starts with two underscores. Methods with such names are called by PHP itself, at a special moment; for the constructor, that moment is `new`.
- The arguments of `new Book(...)` go to the constructor's parameters.
- `$this->title = $title;` copies each parameter into its property. The names are the same on purpose, and `$this->` tells them apart.

### Constructor property promotion

<!-- include: code/28-constructors/promotion.php -->

Every property above was written three times: as a property, as a parameter, and in an assignment. **Constructor property promotion** says it once: a constructor parameter with `public` in front of it declares the property and sets it. The body of the constructor is empty, `{}`.

The parameters work like any function's parameters from chapter 17:

- `public int $copies = 1` has a default value, so `copies` can be left out.
- Named arguments, as in the third book, make a long call readable, in any order.
- A comma after the last parameter is allowed, and makes adding one later a one-line change.

Promote the properties that the constructor only stores. Write them out, as in the previous example, when the constructor changes a value before it stores it.

### Every object valid

<!-- include: code/28-constructors/valid.php -->

The constructor checks the name before the object exists. A `Member` with an empty name is never made: `new` throws, and `$nobody` is never set. Every `Member` in the program has a name, so no function that receives one has to check it again.

### Objects made of objects

<!-- include: code/28-constructors/together.php -->

A `Loan` holds a `Book`, a `Member` and a `DateTimeImmutable`, which you know from chapter 21: it is an object too. Building objects out of other objects is called **composition**. Arrows chain: `$this->book->title` is the title of the loan's book.

In a double-quoted string, `{...}` around an expression lets PHP read more than one arrow, or a method call: `"{$this->book->title}"`.

## Common mistakes

### Too few arguments

<!-- include: code/28-constructors/too-few.error.php -->

⚠ The constructor needs three values. With one, PHP stops with an `ArgumentCountError`, before a half-made book can exist. This is the point of a constructor: the mistake is found where it is made.

**Fix:** pass every value without a default. Named arguments make it easy to see which one is missing.

### Forgetting `public` on a promoted parameter

<!-- include: code/28-constructors/no-public.error.php -->

⚠ Without `public`, `$author` is an ordinary parameter. It exists while the constructor runs, and then it is gone, so the book has no `author` property: "Undefined property".

**Fix:** write the visibility (`public` for now; chapter 29 adds `private`) in front of every parameter that should be a property.

### Declaring a property twice

<!-- include: code/28-constructors/twice.error.php -->

⚠ A promoted parameter already declares the property. Declaring it again above the constructor is an error: "Cannot redeclare Book::$title".

**Fix:** declare each property once, either above the constructor or promoted in it.

## Principle check

**DRY:** promotion writes each property once instead of three times. **Secure by default:** a constructor that refuses invalid values means the rest of the program never sees an invalid object. **KISS:** the constructor only checks and stores. Work such as reading a file belongs in a function or method that is called on purpose.

## Security

A constructor check is a second line of defence, not the first. Input from outside is still checked where it enters, as in chapter 24, with a message for the user. The constructor makes sure that a mistake in your own code can't create an object that breaks the rules.

## Shelf

*Shelf* gets three classes in `library.php`:

- `Book`, now with a constructor: `new Book($title, $author, $year, isbn: $isbn)` replaces six lines.
- `Member`, someone who may borrow books.
- `Loan`: a book, a member and a due date. Its `line()` method describes the loan.

Lending now needs a member: `lend <title> <member>`. The members are in a new file, `shelf/members.json`, and a name that isn't there is refused with `MemberNotFound`. `lend` returns a `Loan`.

Reading two JSON files would repeat the reading code, so it moves into `readList`, which both `loadBooks` and `loadMembers` use. The file now holds more than books: rename `shelf/books.php` to `shelf/data.php`, and change it to:

<!-- include: code/28-constructors/shelf/data.php -->

Create `shelf/members.json`:

<!-- include: code/28-constructors/shelf/members.json -->

Change `shelf/library.php` to:

<!-- include: code/28-constructors/shelf/library.php -->

And change `shelf/shelf.php` to:

<!-- include: code/28-constructors/shelf/shelf.php -->

`run` gets the folder of the data files now, instead of the path of one file. Try it:

```
php shelf/shelf.php lend kitchen amira
php shelf/shelf.php lend kitchen zoe
php shelf/shelf.php lend kitchen
```

The first lends Kitchen to Amira; the second is refused, because there is no member named Zoe; the third prints the usage message. Set Kitchen's `copies` in `books.json` back to 1 afterwards.

## Exercises

The exercises are in `exercises/28-constructors/`. Check your work with:

```
composer exercise 28 beginner
```

1. **Beginner, `book.php`:** a `Book` with a promoted constructor, a default number of copies, and a label.
2. **Intermediate, `member.php`:** a `Member` whose constructor refuses an empty name and an impossible loan limit.
3. **Advanced, `loan.php`:** a `Loan` that calculates its due date, and how many days late it is.

## Summary

- `__construct` runs inside `new`; its parameters are what an object needs to exist.
- A promoted parameter (`public string $title`) declares and sets a property in one place.
- A constructor that refuses invalid values makes every object of its class valid.
- Objects can hold other objects: a `Loan` holds a `Book` and a `Member`.
- In a string, `{$this->book->title}` reads a chained property.

## Further reading

- PHP manual, Constructors and destructors: https://www.php.net/manual/en/language.oop5.decon.php
- PHP manual, Constructor promotion: https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion
- PHP manual, Properties: https://www.php.net/manual/en/language.oop5.properties.php
