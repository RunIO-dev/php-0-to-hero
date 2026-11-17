# 46. The first web page, and escaping output

## Goals

- You will be able to write a web page that mixes HTML with PHP, with `<?=` and the alternative syntax for loops.
- You will be able to explain cross-site scripting (XSS), and show how one unescaped value lets a stranger run code in a visitor's browser.
- You will be able to escape every value you put into HTML with `htmlspecialchars`, between tags and in attributes.
- You will be able to show *Shelf*'s books in the browser.

## Before you start

- Chapter 44: requests and responses, `$_GET`.
- Chapter 45: `docker compose up web`, or `php -S`.
- Chapter 8: strings.

## The idea

A web page is HTML: text with **tags** such as `<p>` and `<li>` that the browser turns into paragraphs and lists. PHP writes the HTML, and fills in values: a title, a name, the text someone searched for.

Here is the danger. The browser can't tell which parts of the HTML you wrote and which came from data. If a book's title, or a visitor's search, contains `<script>`, the browser runs it as a program. That is called **cross-site scripting**, or **XSS**: someone puts code into your page, and it runs in the browser of everyone who views it, with their login, their cookies, their data. It is among the most common security bugs on the web.

The fix is **escaping**: before a value goes into HTML, the characters that mean something in HTML are replaced by codes that only *look* like them. `<` becomes `&lt;`, which the browser shows as `<` but never reads as a tag. PHP's function for it is `htmlspecialchars`.

The rule this course follows from today: **every value that goes into HTML is escaped**, always, whether it came from a visitor, a file or a database. Then nobody has to decide, for each value, whether it might be dangerous: in a year, a title that was harmless may come from a form.

## In code

### PHP inside HTML

A file can switch between HTML and PHP as often as it needs. Outside `<?php ... ?>`, everything is sent as it is. Two short forms make templates readable:

- `<?= $value ?>` prints a value: it is short for `<?php echo $value; ?>`;
- `foreach (...):` with `endforeach;`, the **alternative syntax**, is easier to read between HTML lines than `{` and `}`; `if`, `for` and `while` have it too.

### What goes wrong

<!-- include: code/46-first-web-page/search.bad.php -->

✗ The first request is harmless. The second is a link someone sends to a visitor, with a script in the search text. The page puts it into the HTML as it is, and the visitor's browser runs `steal(document.cookie)`: here a made-up function, in a real attack one that sends the visitor's login cookie to the attacker. The page is yours, so the browser trusts the script as much as your own code.

### Escaping

<!-- include: code/46-first-web-page/search.php -->

The only change is `htmlspecialchars($search)`. The same attack now arrives as `&lt;script&gt;`, and the browser shows the text the visitor typed, harmlessly. What `htmlspecialchars` changes:

<!-- include: code/46-first-web-page/escape.php -->

Five characters: `&`, `<`, `>`, `"` and `'`. Everything else stays. Since PHP 8.1, single quotes are escaped by default too; older code passes `ENT_QUOTES` for it.

### In an attribute

<!-- include: code/46-first-web-page/attribute.php -->

Inside an attribute such as `value="..."`, a `"` in the data would end the value early, and the rest would become new attributes, such as `onmouseover="alert(1)"`, which run code. Escaped, the quote becomes `&quot;` and stays part of the value. Always put attribute values in quotes, so that escaping can protect them.

### Other places in a page

`htmlspecialchars` is for HTML: text between tags, and attribute values in quotes. Other places in a page need other escaping, and some need none at all from user data:

- inside `<script>` or a `style` attribute: don't put data there; Part 10 gives JavaScript its data as JSON;
- in a URL, such as `href="/search.php?q=..."`: `urlencode()` for the value, then `htmlspecialchars()` for the attribute;
- in `href` itself: check that it starts with `https://` or `/`, because `javascript:` is a valid link that runs code.

## Common mistakes

### Escaping only what looks dangerous

"This title comes from our own file, not from a visitor" is true today. Next month, an `add` form writes into that file.

**Fix:** escape every value at the moment it goes into HTML, wherever it came from. A helper with a short name, such as *Shelf*'s `e()` below, makes that the easy way.

### Escaping twice, or too early

Escaping a title before saving it stores `Tom &amp; Jerry` in the data. Then the command line shows `&amp;`, and the page, escaping again, shows `&amp;amp;`.

**Fix:** store data as it is. Escape only when you output it, for the place you output it to.

### An attribute without quotes

`<input value=<?= htmlspecialchars($title) ?>>` is broken by a single space in the title, without any special character, and the rest becomes an attribute.

**Fix:** always quote attribute values: `value="<?= e($title) ?>"`.

## Principle check

**Secure by default:** every value is escaped when it is output, with no exceptions to remember. **DRY and KISS:** one helper, `e()`, holds the escaping rules; every page calls it.

## Security

XSS is the reason this chapter exists. The rule, escape every value for the place it goes, stops it on your pages. Two more layers come later: cookies that scripts can't read (chapter 50), and a Content-Security-Policy header that tells the browser not to run scripts from anywhere else (Part 12). They help when a mistake slips through; they don't replace escaping.

## Shelf

*Shelf*'s home page now lists the books. Two changes.

**An escaping helper.** `e()` in `shelf/src/functions.php` calls `htmlspecialchars` with all its settings written out: both quote styles, `ENT_SUBSTITUTE`, which replaces broken UTF-8 instead of returning an empty string, and UTF-8. Add it above `tidy()`:

<!-- include: code/46-first-web-page/shelf/src/functions.php -->

**The page.** Change `shelf/public/index.php` to:

<!-- include: code/46-first-web-page/shelf/public/index.php -->

- It loads the autoloader and creates the `Library` as `shelf.php` does, from the files one folder up, outside `public/`.
- Every title and author goes through `e()`. The year is an `int`, which can't contain HTML.
- `use function Shelf\e;` lets the page write `e(...)` instead of `\Shelf\e(...)`.

Start the server (`docker compose up web`) and open `http://localhost:8000`: the same books as `php shelf/shelf.php list`. The request in `shelf/web.http` stays `GET /`, and the book's checks now see the list:

<!-- include: code/46-first-web-page/shelf/web.http -->

PHPStan checks the pages too: add `public` to the paths in `shelf/phpstan.neon.dist`:

<!-- include: code/46-first-web-page/shelf/phpstan.neon.dist -->

## Exercises

The exercises are in `exercises/46-first-web-page/`. Check your work with:

```
composer exercise 46 beginner
```

1. **Beginner, `greet.php`:** greet a visitor by the name in the address, safely.
2. **Intermediate, `table.php`:** show books in an HTML table, with titles in an attribute too.
3. **Advanced, `link.php`:** build a link from untrusted data: only safe addresses, encoded and escaped.

## Summary

- `<?= ?>` prints a value; `foreach (...):` and `endforeach;` keep loops readable between HTML.
- Unescaped data in HTML lets strangers run scripts in your visitors' browsers: XSS.
- Escape every value when it goes into HTML: `htmlspecialchars`, or *Shelf*'s `e()`.
- Quote every attribute; URLs and scripts need their own rules.
- Store data as it is; escape on output, for the place it goes.

## Further reading

- PHP manual, `htmlspecialchars`: https://www.php.net/manual/en/function.htmlspecialchars.php
- PHP manual, Escaping from HTML (the `<?=` tag and alternative syntax): https://www.php.net/manual/en/language.basic-syntax.phpmode.php
- PHP manual, Alternative syntax for control structures: https://www.php.net/manual/en/control-structures.alternative-syntax.php
- OWASP, Cross Site Scripting Prevention Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html
