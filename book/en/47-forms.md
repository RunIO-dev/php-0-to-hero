# 47. Forms: GET, POST and checks on the server

## Goals

- You will be able to write an HTML form, and choose between `GET` and `POST` for it.
- You will be able to read a form's fields on the server, and check every one of them there.
- You will be able to answer a form with mistakes with `422`, and show it again with what the visitor typed, escaped.
- You will be able to redirect after a successful `POST` (Post/Redirect/Get), so that reloading the page sends nothing twice.
- You will be able to add books to *Shelf* in the browser.

## Before you start

- Chapter 44: methods, status codes, `header('Location: ...')`.
- Chapter 46: escaping with `htmlspecialchars` and `e()`.
- Chapter 24: input is never trusted; `filter_var`.

## The idea

A **form** is the web's way of asking a visitor for data: fields to type into, and a button that sends them. The `<form>` tag says where to send them, `action`, and how, `method`.

- `method="get"` puts the fields into the address: `/search.php?q=ki`. The page can be bookmarked and shared, and reloading it does no harm. That fits searching and filtering: reading.
- `method="post"` sends the fields in the body of the request. That fits everything that changes data: adding a book, lending it, signing up. A `POST` is never repeated by a search engine following links, and the browser asks before sending it again.

Every field has a `name`; PHP puts its value into `$_GET` or `$_POST` under that name. The values are always strings, or arrays when the name ends in `[]`; a field that wasn't sent isn't there at all.

The most important rule of this chapter: **the server checks every field**. HTML can mark a field `required`, limit its length with `maxlength` or show a keyboard for numbers. That is help for a person, and it is good help. It protects nothing, because a request doesn't have to come from your form: anyone can send any request (chapter 44), with any fields, any lengths and any values.

## In code

### A form with `GET`

<!-- include: code/47-forms/search.php -->

The form sends `q` to `/search.php`, the page itself. The page reads it with `$_GET['q'] ?? ''`, and puts it back into the field, escaped, so that the visitor sees what they searched for and can change it.

### A form with `POST`, checked on the server

<!-- include: code/47-forms/join.php -->

The page has three answers, one per situation:

1. A `GET`: the empty form, `200`.
2. A `POST` with mistakes: `422 Unprocessable Content`, and the form again, with a reason for each mistake and what was typed. Nobody has to type everything again, and the status tells programs that the data was refused.
3. A `POST` without mistakes: a redirect with `303 See Other` to a page that shows the result.

<!-- include: code/47-forms/join.http -->

- The checks are written for each field: the name must have 1 to 50 characters, `mb_strlen` counts characters, not bytes; the year must be a whole number in a range, which `filter_var` with `FILTER_VALIDATE_INT` and `min_range`/`max_range` checks in one step. `19x9` fails, as do `1899` and `1990.5`.
- Every mistake is collected in `$errors`, and the form shows all of them at once. One message after another, each after a new try, wastes the visitor's time.
- In the third request, the name `Amira <b>` is valid: a name may contain any characters. It isn't escaped in the data; it's escaped when it is output, on the welcome page.
- PHP's built-in server doesn't know the name of status `422` and writes `Unknown Status Code` after it. Browsers and programs read the number; real web servers (Part 12) write the name.

### Post/Redirect/Get

Why the redirect? Without it, the answer to the `POST` is a page, and reloading that page sends the `POST` again: a second member, a second book, a second payment. The browser warns about it, and visitors click "Resend" anyway.

With `303 See Other`, the browser asks for the new address with a `GET`. Reloading repeats only that harmless `GET`. This pattern is called **Post/Redirect/Get**: every successful `POST` ends with a redirect. `303` is the right code for it, because it says "now use `GET` there", whatever the method was.

A mistake isn't redirected: the `422` page shows the form with the visitor's values at once. Nothing was saved, so resending it does no harm.

## Common mistakes

### Trusting the browser's checks

<!-- include: code/47-forms/required.bad.php -->

✗ The form has `required` and `maxlength="50"`, and in a browser nobody can send it empty. A request sent without a browser, by a script or by `curl`, is saved anyway: an empty name here, or a million characters, or a year of `-5`.

**Fix:** check every field on the server, as `join.php` does. Keep `required` and `maxlength` too, as help for people, never as protection.

### A field that is an array

`name[]=x` in a request makes `$_POST['name']` an array. `trim($_POST['name'])` with `strict_types` throws a `TypeError`, and the visitor gets a `500` instead of a message.

**Fix:** accept a value only if it is a string, `is_string($value) ? trim($value) : ''`, and let the usual checks reject the empty result.

### Showing the success page after a `POST`

`echo 'Saved!'` at the end of the `POST` works until someone reloads the page.

**Fix:** redirect with `303` after every successful `POST`.

## Principle check

**Secure by default:** the server checks every field, whatever the form says; values are escaped when they come back. **KISS:** one page, three answers: the form, the form with reasons, a redirect. **DRY:** *Shelf*'s web page uses `Library::add`, the same checks as the command line, instead of writing them again.

## Security

Everything in a form is input: hidden fields, the value of a select box, the name of the button. Check what you use, and ignore the rest. Two dangers of forms come later, because they need more tools: a stranger's page that sends a form to your site in the name of your visitor, which chapter 51 stops with a token, and files sent with a form, in chapter 52.

## Shelf

*Shelf* gets a form to add a book: `shelf/public/add.php`.

<!-- include: code/47-forms/shelf/public/add.php -->

- It checks nothing itself: `Library::add` already refuses an empty title, a wrong year and a book that is on the shelf already, with an `InvalidArgumentException` and a message. The page catches it, answers `422` and shows the message, escaped.
- Every field is read as a trimmed string, or `''` if it isn't one. An empty ISBN is left out, because `Library::add` would check it.
- After a book is added, `303` sends the browser back to the list.

The list links to the form. Change the `<p>` at the end of `shelf/public/index.php`:

<!-- include: code/47-forms/shelf/public/index.php -->

`shelf/web.http` sends the requests a visitor's browser would: the empty form, three refused books and one good one, then the list with it:

<!-- include: code/47-forms/shelf/web.http requests 3, 5-7 -->

The book's checks run these requests on a copy of *Shelf*, so the real `books.json` stays as it is. In your browser, the books you add are saved for real.

`index.php` and `add.php` now both create the `Library` in the same three lines. With more pages, that would be repeated in each of them. Chapter 48 sends every request to one file that does it once.

## Exercises

The exercises are in `exercises/47-forms/`. Check your work with:

```
composer exercise 47 beginner
```

1. **Beginner, `subscribe.php`:** a newsletter form that checks the email address, redirects or answers `422`.
2. **Intermediate, `filter.php`:** a `GET` search form with two fields that keeps what was typed, and answers `400` for a wrong year.
3. **Advanced, `rules.php`:** the rules of a sign-up form: every field as a string, every mistake at once.

## Summary

- `GET` forms read: the fields go into the address. `POST` forms change data: the fields go into the body.
- PHP puts the fields into `$_GET` and `$_POST`, as strings or arrays; a field may be missing.
- Check every field on the server; `required` and `maxlength` only help people.
- A form with mistakes: `422`, every reason at once, and what was typed, escaped.
- After a successful `POST`, redirect with `303 See Other`: Post/Redirect/Get.

## Further reading

- PHP manual, Dealing with Forms: https://www.php.net/manual/en/tutorial.forms.php
- PHP manual, Validate filters (`FILTER_VALIDATE_INT`, `FILTER_VALIDATE_EMAIL`): https://www.php.net/manual/en/filter.constants.php
- RFC 9110, HTTP Semantics, section 15.4.4 (303 See Other) and 15.5.21 (422 Unprocessable Content): https://www.rfc-editor.org/rfc/rfc9110.html
