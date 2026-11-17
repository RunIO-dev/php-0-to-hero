# 31. Enums

## Goals

- You will be able to define a fixed set of values with an enum, such as the roles of a member.
- You will be able to store enum values in files with a backed enum, and read them back with `from` and `tryFrom`.
- You will be able to give an enum methods, so that the rule for each case lives with the cases.
- You will be able to use `match` with enums, and know what happens when a case is missing.

## Before you start

- Chapter 30: value objects.
- Chapter 11: `match`.
- Chapter 23: reading JSON, and checking what you read.

## The idea

Some values come from a short, fixed list: a member is a reader or staff, a book is on time, due soon or overdue. Until now, such a value would be a string, `'staff'`. But a string can be anything: `'Staff'`, `'stafff'`, `'admin'`. Every function that receives it must check it, or guess.

An **enum** (short for enumeration) is a type with a fixed list of possible values, called **cases**. `Role::Staff` is not a string that happens to say "staff". It is one of exactly two values of the type `Role`, and PHP knows there are no others. A function that takes a `Role` can't get a typo.

Enums are objects, like value objects: each case exists once, never changes, and can have methods.

## In code

### An enum and its cases

<!-- include: code/31-enums/pure.php -->

- `enum Section { case Fiction; ... }` defines the type and all its values.
- `Section::History` is one case. `===` compares cases: each case exists only once.
- `->name` is the case's name as a string, for messages.
- `match` can list several cases in one arm, separated by commas.

### Backed enums: a value for files and forms

<!-- include: code/31-enums/backed.php -->

A file or a form holds text, not PHP objects. A **backed enum** gives each case a fixed string (or int) for that, after the `=`: `case Staff = 'staff'`.

- `->value` is the string, for saving.
- `Role::from('reader')` turns the string back into the case.
- `Role::tryFrom('admin')` returns `null` for a string that is no case, instead of throwing. Use it for input, where an unknown value is expected, and answer with a clear message.

### Methods on enums

<!-- include: code/31-enums/methods.php -->

`loanDays()` is a method of the enum. Inside it, `$this` is the case it was called on. The rule "staff borrow for 28 days" now lives next to the case `Staff`, not in some function far away.

`Role::cases()` returns all cases, in order: handy for lists and messages.

### An enum for a state

<!-- include: code/31-enums/loan-status.php -->

A loan is on time, due soon or overdue. `statusOf` decides which, from the dates; `label()` describes each case. A pure enum, without values, is enough here: the status is calculated, never saved.

## Common mistakes

### Strings instead of an enum

<!-- include: code/31-enums/strings.php -->

`'Staff'` and `'stafff'` are typos, but they are strings like any other, so they fall into `default` and get 14 days, without a message. With an enum, `Role::Stafff` is an error that PHPStan reports before the program runs.

**Fix:** turn input into an enum once, at the boundary, with `tryFrom`. Inside the program, pass the enum.

### `from` with input

<!-- include: code/31-enums/from.error.php -->

⚠ `from` throws a `ValueError` for a value that is no case. That is right for data that must be valid, and wrong for anything a person typed.

**Fix:** use `tryFrom` for input, check for `null`, and give a message that lists the allowed values.

### A case that `match` doesn't handle

<!-- include: code/31-enums/missing-case.error.php -->

⚠ `Guest` was added to the enum, but not to the `match`. PHP finds out only when a guest arrives: "Unhandled match case Role::Guest".

**Fix:** don't write a `default` arm for enums. Without it, PHPStan reports every `match` that misses a case as soon as you add one, and you can fix them all before any guest arrives.

## Principle check

**Secure by default:** an enum is an allow-list as a type: only the listed values exist. **KISS:** one enum replaces a set of string constants and the checks around them. **YAGNI:** add a case when it is needed, not "in case": every case must be handled wherever the enum is matched.

## Security

Roles decide what someone may do, so a role must never come straight from input. In *Shelf*, the role comes from `members.json`, which the library keeps, not from the command line. When roles decide access, in Part 7, they come from the logged-in user's record, never from a form field the user can change.

## Shelf

Staff borrow books for 28 days, readers for 14. `Role` is a backed enum in `library.php`, and its `loanDays()` replaces the constant `LOAN_DAYS` and the function `dueDate`. `Member` gets a `role`, and `lend` uses it for the due date.

`members.json` now stores each role as a string. Change it to:

<!-- include: code/31-enums/shelf/members.json -->

`toMember` turns the string into a `Role` with `tryFrom`, and refuses a member without a valid role. Change `shelf/data.php` to:

<!-- include: code/31-enums/shelf/data.php -->

Change `shelf/library.php` to:

<!-- include: code/31-enums/shelf/library.php -->

And change the comment at the top of `shelf/shelf.php`:

<!-- include: code/31-enums/shelf/shelf.php -->

Try it: lend a book to Amira and one to Jonas, and compare the due dates. Then change Jonas's role to `"boss"`, and lend again.

*Shelf* doesn't keep its loans yet, so it has nothing to give a `LoanStatus` to. When loans are stored, in Part 8, the enum from this chapter is ready.

## Exercises

The exercises are in `exercises/31-enums/`. Check your work with:

```
composer exercise 31 beginner
```

1. **Beginner, `format.php`:** a backed enum for the format of a book, with a method.
2. **Intermediate, `role.php`:** turn a typed role into a `Role`, with a message that lists the roles from the enum itself.
3. **Advanced, `status.php`:** the status of a loan, with labels and reminders.

## Summary

- An enum is a type with a fixed list of cases; there are no other values.
- A backed enum gives each case a string or int: `->value` to save, `from` and `tryFrom` to read.
- Use `tryFrom` for input and answer `null` with a clear message.
- Enums can have methods; the rule for each case lives with the cases.
- `match` over an enum without `default` lets PHPStan find a missing case.

## Further reading

- PHP manual, Enumerations: https://www.php.net/manual/en/language.enumerations.php
- PHP manual, Backed enumerations: https://www.php.net/manual/en/language.enumerations.backed.php
- PHP manual, Enumeration methods: https://www.php.net/manual/en/language.enumerations.methods.php
