# 35. Abstract classes and traits: what they are for, and their limits

## Goals

- You will be able to write an abstract class: a parent with gaps that each child must fill.
- You will be able to fix the steps of a task in a `final` method, and let children supply the details.
- You will be able to share methods between classes with a trait, and recognise when a value object would be clearer.
- You will be able to explain why *Shelf* needs neither.

## Before you start

- Chapter 34: inheritance, overriding, and why composition usually wins.
- Chapter 33: interfaces.

## The idea

You will meet two more tools in other people's code, and in frameworks such as Symfony. Both are about sharing code between classes, and both have limits worth knowing.

An **abstract class** is a parent class that can't be used on its own. It has normal methods, and **abstract methods**: a signature without a body, like in an interface. A child must write them, or it can't be created. It sits between an interface (only signatures) and a normal class (only code). It is useful when several classes share the same steps and differ only in details: the parent fixes the steps, the children fill the gaps.

A **trait** is a bundle of methods that PHP copies into each class that says `use TheTrait;`. It is reuse without inheritance: unrelated classes can use the same trait. But the copied methods are invisible where the class is read, and the class depends on the trait's details, as a child depends on its parent.

Both tools solve real problems, rarely. Most of the time, an interface plus composition, or a small value object, does the same job with fewer surprises.

## In code

### An abstract class with fixed steps

<!-- include: code/35-abstract-classes-and-traits/abstract.php -->

- `abstract class Notice` can't be created with `new`. Only its children can.
- `abstract protected function body(): string;` is the gap. Each child writes its own `body()`.
- `protected` is the third visibility: like `private`, but the children can use it too. The children here use the `name` property and fill the protected method `body()`.
- `text()` is `final`: the frame of every notice is the same, and no child can change it. This pattern, fixed steps in the parent and details in the children, is called a **template method**.

### A trait

<!-- include: code/35-abstract-classes-and-traits/trait.php -->

`use FormatsMoney;` inside the class copies the trait's `money()` method into it, as if it were written there. `Receipt` and `FeeNotice` don't share a parent, but they share the method.

### The same without a trait

<!-- include: code/35-abstract-classes-and-traits/instead.php -->

A value object does the job of the trait, and more: the amount and its format travel together, `Money` can check that an amount is never negative, and `Receipt`'s constructor shows that it works with money. Prefer this to a trait when the shared methods are about one kind of value.

### When to use which

| You want to | Use |
|---|---|
| say what several classes can do | an interface |
| fix the steps, and let classes fill in details | an abstract class with a `final` template method |
| share the code for one kind of value | a value object |
| share a few methods between unrelated classes, when nothing above fits | a trait |

## Common mistakes

### `new` with an abstract class

<!-- include: code/35-abstract-classes-and-traits/new-abstract.error.php -->

⚠ "Cannot instantiate abstract class Notice". An abstract class has gaps, so it can't be used as it is.

**Fix:** create one of its children.

### A child that doesn't fill the gap

<!-- include: code/35-abstract-classes-and-traits/missing.error.php -->

⚠ This is the message you saw in chapter 33, now explained: `Welcome` "contains 1 abstract method", inherited from `Notice`, "and must therefore be declared abstract or implement the remaining method". Implementing an interface works the same way: its methods are abstract until the class writes them.

**Fix:** write the method in the child.

### Two traits with the same method

<!-- include: code/35-abstract-classes-and-traits/collision.error.php -->

⚠ Both traits bring a `format` method, and a class can't have two. PHP refuses: "has not been applied ... because of collision". PHP has a syntax to pick one or rename one, which is a sign that the traits have become too clever.

**Fix:** give the methods clearer names, or replace the traits with value objects, `Money` and a date.

## Principle check

**KISS:** reach for an interface and composition first; they keep every part visible in the constructor. An abstract class is worth it when the shared steps are real and fixed. **YAGNI:** don't build an abstract parent for one child.

## Security

A trait's methods become part of every class that uses it, including public methods that the class never meant to offer. Read a trait before you use it, as you would read a parent class.

## Shelf

*Shelf* stays as it is in this chapter. It was a good test of the two tools, and it needs neither:

- **No abstract class.** *Shelf* has no family of classes with the same steps. Its only family, the exceptions, needs no gaps to fill: `ShelfException` is a normal parent.
- **No trait.** The only code that two places could share is a one-line comparison of text in any case, and a plain function such as `sameTitle` does that without a trait.

Keeping a program free of a tool it doesn't need is part of the design too. Your *Shelf* is still the one from chapter 34.

## Exercises

The exercises are in `exercises/35-abstract-classes-and-traits/`. Check your work with:

```
composer exercise 35 beginner
```

1. **Beginner, `fees.php`:** two children fill the gap of an abstract late fee.
2. **Intermediate, `report.php`:** an abstract report with a fixed frame, and two reports that fill it.
3. **Advanced, `money.php`:** replace a trait with a value object, without changing the output.

## Summary

- An abstract class can't be created; its abstract methods are gaps that every child must fill.
- A `final` method in an abstract class fixes the steps; children supply the details (template method).
- `protected` members can be used by the class and its children.
- A trait copies methods into classes with `use`; prefer a value object or composition when they fit.
- Choose the smallest tool: interface, then composition, then an abstract class, then a trait.

## Further reading

- PHP manual, Class abstraction: https://www.php.net/manual/en/language.oop5.abstract.php
- PHP manual, Traits: https://www.php.net/manual/en/language.oop5.traits.php
- PHP manual, Visibility (`protected`): https://www.php.net/manual/en/language.oop5.visibility.php
