# 75. Forms and validation in Symfony

## Goals

- You will be able to write rules for input as attributes, and have Symfony's validator check every one.
- You will be able to write a rule of your own: a constraint and its validator.
- You will be able to build a form with Symfony's Form component: its fields, the class it fills, its CSRF token.
- You will be able to check a form and an API's JSON with the same rules.

## Before you start

- Chapter 47: forms, and checking them on the server.
- Chapter 51: CSRF tokens.
- Chapter 68: problem details with a list of errors.
- Chapter 73: controllers and Twig.

## The idea

*Shelf*'s add form checks its fields in `Library::add()`, and stops at the first mistake: "A book needs a title, an author and a year." A visitor who left three fields empty learns about them one at a time. The API checks the same fields again, with code of its own. And every new form needs the same work: read each field, trim it, turn the year into a number, check it, show the form again with what was typed and why it was refused.

Symfony splits this into two components:

- **The validator** (`symfony/validator`) checks an object against **constraints**: rules written as attributes on its properties, `#[Assert\NotBlank]`, `#[Assert\Range(min: 1, max: 2100)]`, `#[Assert\Email]`. It checks every rule, and returns every **violation**: the property, and its message. It doesn't care where the object came from: a form, JSON, a file.
- **The Form component** (`symfony/form`) reads a request into an object. A **form type** lists the fields and their types; the form takes what the browser sent, which is always text, turns each field into its type (the year into a number), puts it into the object, and has the validator check it. In the template, it draws each field, with its value and its mistakes.

The object a form fills is often a small class of its own, a **data class** (DTO, data transfer object), not the entity: every field of it may be missing or wrong, which an entity should never be. When it is valid, the controller makes the entity from it.

A rule that Symfony doesn't have, such as "a valid ISBN-13", is a **constraint of our own**: a class that names the rule and its message, and a validator class that checks it.

The browser's own checks, `required` and `maxlength` in the HTML, are a convenience for the visitor. They can be switched off in a second; the server checks everything again.

## In code

The examples are a Composer project of their own (`code/75-forms/composer.json`). A review of a book, with its rules, `code/75-forms/Review.php`:

<!-- include: code/75-forms/Review.php -->

- Every property may be `null`: the object holds what was sent, before anyone checked it.
- `NotBlank` refuses `null`, `''` and only spaces; `NotNull` only `null`, for a number.
- `{{ min }}`, `{{ max }}` and `{{ limit }}` in a message are filled in from the rule; `{{ value }}` with the value, in quotes.

### The validator

<!-- include: code/75-forms/validate.php -->

- `enableAttributeMapping()` reads the rules from the attributes.
- `validate()` checks every rule, and returns every violation: all three mistakes at once.
- `text` is empty, and has no `NotBlank`, so it may stay empty; `Length` checks it only when there is something to check.

### A form

<!-- include: code/75-forms/form.php -->

- `submit()` takes what was sent; in a controller, `handleRequest($request)` does it from the request.
- `stars: 'five'` can't be turned into a number: the form reports its `invalid_message`, before the validator even sees it.
- A field the form doesn't have, `admin`, is refused: "This form should not contain extra fields." Nobody can set a property the form doesn't offer.
- After a valid submit, `getData()` is the `Review`, with `stars` as a number.

## Common mistakes

### Every field of the request into the object

<!-- include: code/75-forms/assign.bad.php -->

✗ The form shows name and email; the request can carry anything. `role=staff` makes a reader staff. This is called **mass assignment**, and it is how many accounts get rights they shouldn't.

**Fix:** a form with only the fields the visitor may change, as `form.php` shows: extra fields are refused. Or set each property by hand, from a checked value.

### Rules only in the HTML

`<input required maxlength="200">` and no rule on the server: a request sent with `curl`, or a browser with the attribute removed, stores an empty title, or a title of a megabyte.

**Fix:** every rule as a constraint, checked on the server. The HTML attributes may stay, for the visitor's convenience.

### The entity as the form's data

A form that fills the `Book` entity directly needs a `Book` with an empty title while the visitor types, and puts every property the form has into the database the moment something calls `flush()`.

**Fix:** a data class for the form, as *Shelf*'s `BookData`; the entity is made from it once it is valid, by the code that knows the rules.

## Principle check

**DRY:** the rules are written once, on `BookData`, and checked for the form and the API. **Secure by default:** the form refuses fields it doesn't have, checks its CSRF token, and every rule runs on the server. **KISS:** a rule of its own needs two small classes; the rule itself stays in `Isbn`.

## Security

- **CSRF.** Every Symfony form has a token field, and refuses a submission without the right token. *Shelf*'s form uses the same token as its other forms, so `CheckCsrf` and the form agree.
- **Uploads.** `Assert\Image` checks the type and the size of an upload, by its content. *Shelf*'s `Covers` checks again before it stores the file: two locks are better than one, when a file from a stranger is stored on the server.
- **Messages hold input.** `"{{ value }}" is not a valid ISBN-13` puts the visitor's text into the page; Twig escapes it, as every value.

## Shelf

The add form becomes a Symfony form, with every mistake next to its field. The API's `POST /api/books` checks its JSON with the same rules, and names every wrong field in `errors`, as chapter 68's example did.

```
cd shelf
composer require symfony/form symfony/validator symfony/mime
```

`symfony/mime` lets `Assert\Image` read a file's type from its content. The recipes added three files to `config/packages/`; two of them are removed, as Symfony's defaults are enough. The third, `csrf.yaml`, switches forms to **stateless** CSRF tokens, which need JavaScript to work. *Shelf* has none, and keeps its session's tokens of chapter 73, so it goes too. `shelf/composer.json`:

<!-- include: code/75-forms/shelf/composer.json -->

### The rules

What the form and the API send, `shelf/src/Form/BookData.php`:

<!-- include: code/75-forms/shelf/src/Form/BookData.php -->

The ISBN's rule is a constraint of our own, `shelf/src/Validator/ValidIsbn.php`:

<!-- include: code/75-forms/shelf/src/Validator/ValidIsbn.php -->

Its validator uses `Isbn`, so the rule stays in one place. By its name, `ValidIsbn` + `Validator`, Symfony finds it. `shelf/src/Validator/ValidIsbnValidator.php`:

<!-- include: code/75-forms/shelf/src/Validator/ValidIsbnValidator.php -->

### The form

`shelf/src/Form/BookType.php`:

<!-- include: code/75-forms/shelf/src/Form/BookType.php -->

- `data_class`: the form fills a `BookData`.
- `csrf_token_id` and `csrf_field_name` give it the token of every other *Shelf* form, in the field `_token`.
- `getBlockPrefix()` returns `''`, so the form has no name: its fields are sent as `title` and `year`, as before, and `web.http` sends the same requests.

The controller, `shelf/src/Controller/BookController.php`:

<!-- include: code/75-forms/shelf/src/Controller/BookController.php -->

- `handleRequest()` reads a `POST`, and does nothing for a `GET`.
- `isSubmitted() && isValid()`: only then is the book added. `Library` still checks its own rules, such as a title only once; its message goes onto the form, as a `FormError`.
- `render()` sees a form that was sent with mistakes, and answers `422` on its own.

The template draws each field, with `form_widget()`, and its mistakes, with `form_errors()`. `form_end()` adds the hidden token, `shelf/templates/add.html.twig`:

<!-- include: code/75-forms/shelf/templates/add.html.twig -->

A **form theme** changes how Symfony draws a part of every form. *Shelf*'s changes only the mistakes, into the paragraphs its stylesheet knows, `shelf/templates/form/shelf.html.twig`:

<!-- include: code/75-forms/shelf/templates/form/shelf.html.twig -->

### The API

`POST /api/books` fills a `BookData` from the JSON, notes fields of the wrong JSON type, and has the validator check the rest: one mistake per field, all at once, `shelf/src/Controller/ApiController.php`:

<!-- include: code/75-forms/shelf/src/Controller/ApiController.php lines 99-136, 194-218 -->

### The tests

The rules, `shelf/tests/Form/BookDataTest.php`:

<!-- include: code/75-forms/shelf/tests/Form/BookDataTest.php lines 14-60 -->

The constraint, with Symfony's own test case for validators, `shelf/tests/Validator/ValidIsbnValidatorTest.php`:

<!-- include: code/75-forms/shelf/tests/Validator/ValidIsbnValidatorTest.php -->

### The check

`shelf/web.http` sends the add form's requests as before, and one more to the API, with three mistakes:

<!-- include: code/75-forms/shelf/web.http requests 12, 38 -->

The empty form now names all three missing fields, each next to its field; the year 10000 gets "The year is from 1 to 2100."; the cover that is PHP code is refused by `Assert\Image`, before `Covers` sees it.

## Exercises

The exercises are in `exercises/75-forms/`. Check your work with:

```
composer exercise 75 beginner
```

1. **Beginner, `NewMember.php`:** the rules of a sign-up: a name, an email address, a long password, a role from a list.
2. **Intermediate, `Reservation.php`:** a rule about two fields together, with `Assert\Callback`: the end after the start, at most 14 days.
3. **Advanced, `MemberType.php`:** a sign-up form whose role field exists only for staff, so a visitor can't send one.

## Summary

- Constraints are rules as attributes; the validator checks every one, and names every violation.
- A form reads the request into a data class, turns each field into its type, refuses extra fields, and checks its CSRF token.
- The entity is made from the data class once it is valid, never filled from the request.
- A rule of your own is a constraint and its validator; the rule itself can stay in your class.
- The same rules check a form and an API.

## Further reading

- Symfony documentation, Validation: https://symfony.com/doc/7.4/validation.html
- Symfony documentation, Forms: https://symfony.com/doc/7.4/forms.html
- Symfony documentation, How to create a custom validation constraint: https://symfony.com/doc/7.4/validation/custom_constraint.html
- Symfony documentation, Validation constraints reference: https://symfony.com/doc/7.4/reference/constraints.html
- OWASP, Mass Assignment Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Mass_Assignment_Cheat_Sheet.html
