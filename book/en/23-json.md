# 23. JSON

## Goals

- You will be able to turn arrays into JSON text with `json_encode`, for programs and for people.
- You will be able to read JSON back into arrays with `json_decode`.
- You will be able to make broken JSON throw an exception with `JSON_THROW_ON_ERROR`, instead of failing silently.
- You will be able to check data you read, before you use it.

## Before you start

- Chapter 22: you can read and write files.
- Chapter 19: you can throw and catch exceptions.

## The idea

CSV is a table: every row has the same columns, and every field is text. Data is often shaped differently. A book has a list of tags, a member has a list of loans, and a number should stay a number.

**JSON** (JavaScript Object Notation) is a text format for exactly that shape. It has the same building blocks as PHP: lists in square brackets, maps (JSON calls them **objects**) in curly braces, strings in double quotes, numbers, `true`, `false` and `null`. Nearly every language and web service reads and writes it, so it is how programs exchange data, and how *Shelf* will store its books from now on.

PHP converts between the two with one function in each direction: `json_encode` turns an array into JSON text, and `json_decode` turns JSON text back into an array.

## In code

### From PHP to JSON

<!-- include: code/23-json/encode.php -->

- `json_encode` turns the map into a JSON object. The int stays a number, and `false` stays `false`.
- By default, letters such as "ö" are written as codes (`\u00f6`), and everything is on one line. That's fine for programs.
- `JSON_PRETTY_PRINT` indents the result, and `JSON_UNESCAPED_UNICODE` keeps the letters as they are. Use both for files that people read.
- `JSON_THROW_ON_ERROR` makes `json_encode` throw a `JsonException` when something can't be encoded. Without it, it returns `false`.

### From JSON to PHP

<!-- include: code/23-json/decode.php -->

- The second argument, `true`, turns JSON objects into maps. The JSON list of tags becomes a list.
- `flags: JSON_THROW_ON_ERROR` makes broken JSON throw. It is a named argument, so the parameter in between, `depth`, keeps its default.
- The types survive: the year comes back as an int, not as text.

### Saving and loading

<!-- include: code/23-json/round-trip.php -->

Together with chapter 22, that is all it takes to keep data between runs: `json_encode` and `file_put_contents` to save, `file_get_contents` and `json_decode` to load. `$loaded === $books` is `true`: the round trip lost nothing.

## Common mistakes

### Broken JSON

<!-- include: code/23-json/invalid.error.php -->

⚠ JSON is stricter than PHP: there may be no comma after the last item. `json_decode` throws a `JsonException`, and the program stops with a message that names the problem and, from PHP 8.6 on, the place: line 1, character 35. PHP 8.5 says only "Syntax error".

**Fix:** correct the JSON. Where the JSON comes from outside, catch the `JsonException` and tell the user that the data is broken.

### Forgetting `JSON_THROW_ON_ERROR`

<!-- include: code/23-json/silent.php -->

Without the flag, broken JSON gives `null` and no message at all. The program goes on with `null` instead of a book, and the real problem shows up somewhere else, if at all. `json_last_error_msg()` could tell you, but only if you remember to ask.

**Fix:** always pass `JSON_THROW_ON_ERROR`, to `json_decode` and to `json_encode`.

### An object instead of a map

<!-- include: code/23-json/object.error.php -->

⚠ Without `true`, `json_decode` turns a JSON object into a PHP object of the class `stdClass`, and objects don't use square brackets: "Cannot use object of type stdClass as array".

**Fix:** pass `true` as the second argument. This course works with maps until Part 5, where objects come with real classes.

## Principle check

**Secure by default:** `JSON_THROW_ON_ERROR` turns a silent `null` into a loud exception, the same idea as `strict_types`. **KISS:** JSON is text you can read and edit by hand, which makes it a good first format for saved data.

## Security

JSON from outside is input, even when it decodes without an error. `{"copies": "lots"}` is valid JSON, but not a valid book. After decoding, check that every value you need is there and has the right type, as *Shelf*'s `toBook` does below, and throw when it doesn't fit. Then the rest of the program can rely on the data.

## Shelf

*Shelf*'s books move from `books.csv` to `shelf/books.json`. Delete `shelf/books.csv`, and create `shelf/books.json`:

<!-- include: code/23-json/shelf/books.json -->

Then change `shelf/shelf.php` to:

<!-- include: code/23-json/shelf/shelf.php -->

Three things are new:

- `array_is_list($data)` answers whether an array is a list, with the keys 0, 1, 2. A JSON file that holds one object instead of a list is refused.
- `mixed` is the type for "any value". `toBook` gets whatever the file contained, and checks it before it trusts it.
- `toBook` checks every key and its type. If one is wrong, it throws, and the message shows the item as JSON, so you can find it in the file.

Run it with `php shelf/shelf.php`. Then change `"copies": 1` to `"copies": "one"` in the file, run it again, and read the message. Change it back afterwards.

## Exercises

The exercises are in `exercises/23-json/`. Check your work with:

```
composer exercise 23 beginner
```

1. **Beginner, `export.php`:** turn books into JSON for people, and make failures throw.
2. **Intermediate, `import.php`:** read the titles from a JSON list, and throw on broken JSON.
3. **Advanced, `settings.php`:** load settings from a JSON file, with defaults and type checks.

## Summary

- `json_encode` turns arrays into JSON; add `JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE` for people.
- `json_decode($json, true, flags: JSON_THROW_ON_ERROR)` gives maps and lists, and throws on broken JSON.
- Always pass `JSON_THROW_ON_ERROR`; without it, failures are a silent `null` or `false`.
- Valid JSON is not yet valid data: check keys and types after decoding.
- Saving is encode and write; loading is read and decode.

## Further reading

- PHP manual, `json_encode`: https://www.php.net/manual/en/function.json-encode.php
- PHP manual, `json_decode`: https://www.php.net/manual/en/function.json-decode.php
- PHP manual, JSON constants: https://www.php.net/manual/en/json.constants.php
- The JSON format, RFC 8259: https://www.rfc-editor.org/rfc/rfc8259
