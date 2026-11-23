# 30. `readonly` properties and value objects

## Goals

- You will be able to make a property, or a whole class, unchangeable after construction, with `readonly`.
- You will be able to write a value object: a small object that is valid from the start, compared by its value, and never changed.
- You will be able to "change" a value object by returning a new one.
- You will be able to explain what `readonly` does not protect.

## Before you start

- Chapter 29: `private`, `private(set)`, and keeping rules inside a class.
- Chapter 21: `DateTimeImmutable`, and why its `modify` returns a new object.

## The idea

Some things about a book never change: its title, its author, the year it was published. Others do: the copies on the shelf. Until now, `title` could be changed by the class itself, and by mistake. `readonly` says what the design means: this property is set once, in the constructor, and never again.

Some values are best as small objects of their own. An ISBN is more than a string: only some strings are valid ISBNs, two ISBNs written with and without hyphens are the same ISBN, and an ISBN never changes. A **value object** is a class for such a value:

- it is checked in its constructor, so every object of it is valid;
- it is `readonly`, so it stays valid;
- two value objects are equal when their values are equal, whatever object they are.

You know one already: `DateTimeImmutable`. Its `modify('+14 days')` doesn't change the date; it returns a new one.

Once *Shelf* has an `Isbn` class, a function that receives an `Isbn` knows it is valid. The checks don't have to be repeated, or remembered.

## In code

### `readonly` properties

<!-- include: code/30-readonly-and-value-objects/readonly.php -->

`public readonly string $title` can be read by anyone and set only once, from inside the class: here, by the promoted constructor parameter. A readonly property needs a type.

### A readonly class

<!-- include: code/30-readonly-and-value-objects/readonly-class.php -->

`readonly` before `class` makes every property of the class readonly. Use it when nothing about the object should change after it is made, which is the case for a member's name and start date in this example.

### A value object

<!-- include: code/30-readonly-and-value-objects/money.php -->

`Money` follows the three rules:

- The constructor refuses a negative amount. Every `Money` is valid.
- The class is readonly. A valid `Money` stays valid.
- `add` doesn't change `$fee`. It returns a new `Money`, as `DateTimeImmutable::modify` returns a new date. `$fee` is still 1.50 EUR afterwards.

`equals` compares the values. That is what "the same amount" means, as the next example shows.

### Comparing objects

<!-- include: code/30-readonly-and-value-objects/same.php -->

- `===` asks whether both are the **same object**. Two objects made with two `new`s never are, even with the same values.
- `==` asks whether both are of the same class and all their properties are `==`. It works here, but `==` compares loosely (chapter 10), and it compares every property, even ones that don't matter for equality.

For value objects, write an `equals` method. It says exactly what "equal" means, and it is type-checked.

## Common mistakes

### Changing a readonly property

<!-- include: code/30-readonly-and-value-objects/readonly.error.php -->

⚠ "Cannot modify readonly property Money::$cents". A readonly property can't be assigned a second time, not even from inside the class.

**Fix:** return a new object with the new value, as `add` does.

### Expecting `readonly` to protect everything

<!-- include: code/30-readonly-and-value-objects/shallow.php -->

The loan is readonly, but its book isn't. `readonly` stops anyone from putting a *different* book into `$loan->book`. It doesn't stop changes *inside* the book it holds. This is called a **shallow** guarantee.

**Fix:** a value object should only hold values that can't change themselves: strings, ints, other value objects, `DateTimeImmutable`. Never `DateTime`, its changeable cousin. In *Shelf*, a `Loan` holds a `Book` on purpose: the loan is about that very book, whose copies do change.

### A value object without checks

A readonly class whose constructor accepts anything is unchangeable, but not valid: `new Money(-500)` would stay wrong forever.

**Fix:** check in the constructor, and throw. Then validity comes with the type.

## Principle check

**Secure by default:** a value checked once in its constructor can't be used unchecked anywhere. **DRY:** the ISBN rules live in one class, instead of in every function that handles ISBNs. **KISS:** objects that can't change can be passed around freely, without asking who else might change them.

## Security

Value objects are a good way to keep input checks honest. Once input is turned into an `Isbn` at the boundary, the rest of the program works with `Isbn`, not with strings, so no code path can use an unchecked ISBN by accident. The same pattern serves for email addresses, amounts of money and IDs in later parts.

## Shelf

*Shelf* gets its first value object, `Isbn`. The functions `isbnDigits` and `isIsbn13` move into it: its constructor removes hyphens and spaces and refuses an invalid ISBN, and `equals` compares two of them.

- `Book`'s title, author, year and ISBN are `readonly`. Only `copies` changes, through `lend()`.
- `Member` and `Loan` become readonly classes.
- `addBook` creates the `Isbn`, and also refuses a book whose ISBN is already on the shelf, under any title.

`json_encode` would now write the ISBN as an object, `{"digits": "978..."}`, and `loadBooks` expects a string. So `saveBooks` builds each row of the file itself: the format of the file is written in one place, next to `loadBooks`, and no longer depends on how the classes look. `$book->isbn?->digits` uses the **nullsafe operator** `?->`: if `isbn` is `null`, the result is `null`, instead of an error.

Change `shelf/library.php`: `Isbn` takes the place of `isbnDigits` and `isIsbn13`, and `Book`, `Member`, `Loan` and `addBook` change like this:

<!-- include: code/30-readonly-and-value-objects/shelf/library.php lines 15-28, 51-91, 196-214 -->

Change `shelf/data.php` to:

<!-- include: code/30-readonly-and-value-objects/shelf/data.php -->

And change the comment at the top of `shelf/shelf.php`:

<!-- include: code/30-readonly-and-value-objects/shelf/shelf.php -->

Try it:

```
php shelf/shelf.php add Sula "Toni Morrison" 1973 978-0-306-40615-7
php shelf/shelf.php add Beloved "Toni Morrison" 1987 9780306406157
```

The second is refused, "Sula" is already on the shelf: the same ISBN, written without hyphens. (The ISBN here is a well-known example number, not Sula's real one.)

## Exercises

The exercises are in `exercises/30-readonly-and-value-objects/`. Check your work with:

```
composer exercise 30 beginner
```

1. **Beginner, `fee.php`:** a readonly `Fee` that adds up into new fees.
2. **Intermediate, `period.php`:** a loan period that can be extended without changing it.
3. **Advanced, `shelf-code.php`:** a shelf code as a value object, cleaned and checked in its constructor.

## Summary

- `readonly` properties are set once, in the constructor; a `readonly class` makes them all readonly.
- A value object is checked in its constructor, readonly, and compared by value with `equals`.
- To "change" a value object, return a new one.
- `===` on objects asks "the same object?"; write `equals` for "the same value".
- `readonly` is shallow: it doesn't stop changes inside an object a property holds.

## Further reading

- PHP manual, Readonly properties: https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.readonly-properties
- PHP manual, Readonly classes: https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.readonly
- PHP manual, Comparing objects: https://www.php.net/manual/en/language.oop5.object-comparison.php
- PHP manual, Nullsafe operator: https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.nullsafe
