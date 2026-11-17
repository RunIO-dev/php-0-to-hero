# 29. Visibility and encapsulation: why `private` is the default

## Goals

- You will be able to decide what other code may read and change on an object, with `public`, `private` and `private(set)`.
- You will be able to keep a rule, such as "copies never go below 0", inside the class that owns it.
- You will be able to write private helper methods.
- You will be able to explain why a getter and a setter for every property protect nothing.

## Before you start

- Chapter 28: constructors and promoted properties.

## The idea

A `Book` in *Shelf* has a rule: its number of copies is never below 0. Since chapter 27, `lend` kept that rule by checking first. But `copies` is `public`, so any other line of code anywhere can write `$book->copies = -3`, and nothing stops it. A rule that every caller must remember is a rule that someone will forget.

**Visibility** says who may use a property or method:

- `public`: any code.
- `private`: only the methods of the class itself.
- `public private(set)`: any code may read it, but only the class may change it. This is called **asymmetric visibility**, and PHP has it since 8.4.

Keeping the data private and offering methods that keep its rules is called **encapsulation**. The rest of the program then can't break the rules, because the only way to change the data is through the class.

Start with `private` and open up only what other code really needs. Something private can be made public later without breaking anything. Something public that other code already uses can't easily be taken back.

## In code

### The problem with `public`

<!-- include: code/29-visibility/anyone.php -->

Two copies, three taken: the book has -1 copies, and nothing complained. The class can't defend its rule, because it isn't asked.

### `private`, and methods that keep the rule

<!-- include: code/29-visibility/private.php -->

- `private int $copies` can be used only inside `Book`. Outside, the only ways in are the two public methods.
- `lend()` checks the rule and changes the value in one step. No caller can skip the check.
- `copies()` returns the value, so other code can read it. A method like this, which only returns a property, is called a **getter**.

### `private(set)`: read from anywhere, change only inside

<!-- include: code/29-visibility/private-set.php -->

Reading the copies is harmless; changing them is what needs the rule. `public private(set) int $copies` says exactly that, without a getter: `$book->copies` works everywhere, and only `Book`'s methods can assign to it.

### Private methods

<!-- include: code/29-visibility/private-method.php -->

`loansText()` is a helper for `greeting()`. Making it private says: this is not part of what `Member` offers. You can rename it, split it or remove it later, and you only have to look inside the class to know nothing else breaks.

The public methods of a class are its **interface** in the everyday sense: what other code may rely on. Keep it small.

## Common mistakes

### Changing from outside

<!-- include: code/29-visibility/outside.error.php -->

⚠ "Cannot modify private(set) property Book::$copies from global scope". This error is what you want: the mistake stops at the line that made it. A `private` property gives a similar error even for reading: "Cannot access private property".

**Fix:** call the method that makes the change, such as `$book->lend()`. If no such method exists, ask whether the change should be allowed at all.

### A getter and a setter for everything

<!-- include: code/29-visibility/setters.bad.php -->

✗ The property is private, but `setCopies` lets any code set any value, so the book ends with -1 copies, as in the first example. Pairs of `getX` and `setX` for every property are common in older code. They look careful and protect nothing.

**Fix:** offer methods that say what happens, in the words of the problem: `lend()`, not `setCopies()`. Let each method keep the rules.

### Private data that a file needs

<!-- include: code/29-visibility/hidden.php -->

`json_encode` writes only what code outside the class may read, so the private `loans` is missing from the JSON, without a warning.

**Fix:** use `public private(set)` for data that may be read, as *Shelf* does below, or write a method that returns the data to save.

## Principle check

**Secure by default:** start with `private` and open up on purpose, the same idea as an allow-list in chapter 24. **KISS:** small public methods named after what they do (`lend`) are easier to use correctly than open data with rules in the documentation.

## Security

Encapsulation is not a security boundary against an attacker who can change your code. It protects against mistakes: a rule kept in one class holds everywhere, so a forgotten check in some new command can't put the data into an impossible state.

## Shelf

`Book` now guards its copies:

- `copies` is `public private(set)`: everyone reads it, only the book changes it.
- The constructor refuses a negative number of copies, also from `books.json`.
- `Book::lend()` takes a copy, or throws `NoCopyLeft`. The function `lend` asks the book to do it, and no longer changes `copies` itself.

`json_encode` still writes `copies`, because it can be read from outside, so `books.json` keeps its format.

Change `shelf/library.php` to:

<!-- include: code/29-visibility/shelf/library.php -->

And change the comment at the top of `shelf/shelf.php`:

<!-- include: code/29-visibility/shelf/shelf.php -->

Run `php shelf/shelf.php lend kitchen amira` twice: the second time, Kitchen has no copy left. Then set Kitchen's `copies` in `books.json` to `-1`, run `php shelf/shelf.php`, and read the message. Change it back to 1.

## Exercises

The exercises are in `exercises/29-visibility/`. Check your work with:

```
composer exercise 29 beginner
```

1. **Beginner, `card.php`:** a library card whose balance only its own methods can change.
2. **Intermediate, `member.php`:** a member's borrowed titles in a private list, with a limit of three.
3. **Advanced, `stock.php`:** a book whose copies on the shelf and on loan always add up.

## Summary

- `public` is for any code, `private` only for the class's own methods.
- `public private(set)` lets everyone read a property and only the class change it.
- Encapsulation: private data, and public methods that keep its rules.
- Start with `private`; open up only what other code needs.
- A setter for every property protects nothing; name methods after what they do.

## Further reading

- PHP manual, Visibility: https://www.php.net/manual/en/language.oop5.visibility.php
- PHP manual, Asymmetric property visibility: https://www.php.net/manual/en/language.oop5.visibility.php#language.oop5.visibility-members-aviz
