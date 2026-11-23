# 52. File uploads, done safely

## Goals

- You will be able to send a file with a form, and read it in PHP with `$_FILES`.
- You will be able to check an upload by what it contains, not by its name or the type the browser claims.
- You will be able to store uploads outside `public/`, under names you choose, and send them back safely.
- You will be able to add book covers to *Shelf*.

## Before you start

- Chapter 47: forms and checks on the server.
- Chapter 22: files and folders.
- Chapter 51: CSRF tokens, which upload forms need too.

## The idea

A form can send files: a cover, a photo, a document. In the form, it takes `enctype="multipart/form-data"` and an `<input type="file">`. PHP receives the file, keeps it in a temporary folder until the request ends, and describes it in `$_FILES`: the name the file had on the visitor's computer, the type the browser claims, the size, an error code, and `tmp_name`, where PHP keeps it.

An upload is the most dangerous input there is. The visitor chooses everything about it: the content, the name, the claimed type. A file named `cover.png` can contain PHP code. If the site keeps it under its own name in a folder the web server serves, a request for `/uploads/cover.php` runs that code on your server, with your server's rights. That one mistake gives a stranger the whole site.

So every upload goes through five rules:

1. **Check the error code**: only `UPLOAD_ERR_OK` means a whole file arrived.
2. **Limit the size**, in your code and in PHP's settings (`upload_max_filesize`, `post_max_size`).
3. **Check what the file is by its content**, with `finfo`, against a short list of types you accept. Never trust the name or the browser's type.
4. **Choose the name yourself**: random, or derived from your own data, with an extension from the type you found. The visitor's file name is never used for storing.
5. **Store it outside `public/`**, and send it back through a script, with the right `Content-Type` and `X-Content-Type-Options: nosniff`.

## In code

### A safe upload

`code/52-file-uploads/covers/` is a small site: `public/` is served, `storage/` is not.

<!-- include: code/52-file-uploads/covers/public/upload.php -->

- `enctype="multipart/form-data"` makes the browser send the file. Without it, only the file's name arrives.
- `problem()` checks the five rules in order, and returns a reason for the visitor, or `null`.
- `is_uploaded_file()` confirms that PHP received this file in this request, so a value in `$_FILES` can't point to another file on the server.
- `finfo` reads the first bytes of the file, which say what it is: a PNG starts with `\x89PNG`, a JPEG with `\xFF\xD8\xFF`. `TYPES` lists the two that are accepted, and gives the extension. `getimagesize()` confirms that the image can be read.
- The new name is 32 random hexadecimal characters with that extension. `move_uploaded_file()` moves the file from PHP's temporary folder into `storage/`.

The page that sends a stored cover back:

<!-- include: code/52-file-uploads/covers/public/cover.php -->

The name must have exactly the shape `upload.php` gives: 32 hexadecimal characters, a dot and `png` or `jpg`. `../public/upload.php` doesn't, and gets `404`. `X-Content-Type-Options: nosniff` tells the browser to believe the `Content-Type`; without it, some browsers look at the content and might treat a file as a page with scripts.

<!-- include: code/52-file-uploads/covers/covers.http -->

- In a multipart body, each field is a part between the boundary lines. The line `< samples/kindred.png` stands for the bytes of that file; in the book's checks, the file is sent as a browser would send it. A binary answer, such as an image, is shown as its size.
- The fourth request sends PHP code, named `cover.png` and claimed to be `image/png`. `finfo` finds text, and the upload is refused with `422`.

## Common mistakes

### Keeping the visitor's name, inside `public/`

<!-- include: code/52-file-uploads/covers/public/upload.bad.php -->

<!-- include: code/52-file-uploads/covers/bad.http -->

✗ A "cover" named `note.php` is saved as `public/uploads/note.php`, and the next request runs it. Here, it prints a number; a real attacker's file would read your data, or replace your pages.

**Fix:** all five rules, as `upload.php` follows them. Any one of them alone would have stopped this file; together they stop the files no single rule catches.

### Trusting the type the browser sends

`$_FILES['cover']['type']` is `image/png` because the request says so. The visitor writes the request.

**Fix:** `finfo` on the file itself: `(new finfo(FILEINFO_MIME_TYPE))->file($tmp)`.

### Accepting every image type

SVG is an image format made of text, and it can contain `<script>`. A stored SVG, opened directly, runs that script on your site, as if it were XSS.

**Fix:** accept a short list of types you need, such as PNG and JPEG, and refuse everything else.

## Principle check

**Secure by default:** nothing the visitor chose decides where, under which name or as which type a file is stored; stored files are outside `public/` and come back with `nosniff`. **KISS:** two types, one size limit, one folder. **DRY:** *Shelf*'s checks live in one class, used by the form and by the tests.

## Security

Uploads need the protections of every other form too: the CSRF token (chapter 51) and a size limit. PHP's own limits come first: a file larger than `upload_max_filesize` (2 MB by default) arrives as `UPLOAD_ERR_INI_SIZE`, and a request larger than `post_max_size` (8 MB) arrives with empty `$_POST` and `$_FILES`. Production adds two more layers in Part 12: a web server that runs no PHP outside the front controller, and storage that isn't on the same disk as the code.

## Shelf

Books can now get a cover when they are added: a PNG or JPEG of up to 2 MB, shown small next to the title.

**An upload, as `Request` sees it.** `shelf/src/Web/Upload.php`:

<!-- include: code/52-file-uploads/shelf/src/Web/Upload.php -->

`Request::fromGlobals()` turns `$_FILES` into `Upload` objects, and drops a file that PHP didn't receive as an upload in this request. `query()` reads a field of the address. `shelf/src/Web/Request.php`:

<!-- include: code/52-file-uploads/shelf/src/Web/Request.php -->

**The covers.** `shelf/src/Web/Covers.php` follows the five rules:

<!-- include: code/52-file-uploads/shelf/src/Web/Covers.php lines 12-89 -->

- The file name is a SHA-256 hash of the title, in lower case and tidied: any title gives a safe name of 64 hexadecimal characters, and the same title always gives the same file. A new cover replaces the old one, even when one is a PNG and the other a JPEG.
- The folder is `shelf/storage/covers/`, outside `public/`. It is made when the first cover arrives. Covers are data, not code, so `shelf/.gitignore` leaves them out:

<!-- include: code/52-file-uploads/shelf/.gitignore -->

**The app.** `shelf/src/Web/App.php`:

<!-- include: code/52-file-uploads/shelf/src/Web/App.php lines 28-51, 66-107 -->

- The cover is checked *before* the book is added. A wrong cover refuses the whole form with `422`, and nothing is half saved.
- `GET /cover?title=...` sends the cover, with its type and `nosniff`, or `404`.
- The list passes the titles that have a cover to the template.
- An unknown address now gets a page, `missing`, instead of one line.

**The templates.** The form gets `enctype` and a file field, `shelf/templates/add.php`:

<!-- include: code/52-file-uploads/shelf/templates/add.php -->

The list shows the cover. Its address is built with `urlencode()` for the value, then `e()` for the attribute (chapter 46). `shelf/templates/list.php`:

<!-- include: code/52-file-uploads/shelf/templates/list.php -->

`shelf/templates/missing.php`:

<!-- include: code/52-file-uploads/shelf/templates/missing.php -->

**The front controller** passes a `Covers` for `storage/covers`. `shelf/public/index.php`:

<!-- include: code/52-file-uploads/shelf/public/index.php -->

**The tests.** Two small files in `shelf/tests/Web/` are their uploads: `cover.png`, a real 4 × 6 pixel image, and `not-a-cover.png`, PHP code with an image's name. `shelf/tests/Web/CoversTest.php`:

<!-- include: code/52-file-uploads/shelf/tests/Web/CoversTest.php -->

`AppTest` adds a book with a cover, a book with a wrong one, and asks for a cover that doesn't exist:

<!-- include: code/52-file-uploads/shelf/tests/Web/AppTest.php -->

`TemplatesTest` passes an empty list of covers:

<!-- include: code/52-file-uploads/shelf/tests/Web/TemplatesTest.php -->

`shelf/web.http` adds *Sula* with the code file as a cover, which is refused, then with the real cover, and asks for both covers:

<!-- include: code/52-file-uploads/shelf/web.http requests 8, 11-12 -->

## Exercises

The exercises are in `exercises/52-file-uploads/`. Check your work with:

```
composer exercise 52 beginner
```

1. **Beginner, `errors.php`:** turn PHP's upload error codes into messages for the visitor.
2. **Intermediate, `kind.php`:** find out what a file is by its content, and accept only safe image types.
3. **Advanced, `storage.php`:** store images under random names, and open only names the store made.

## Summary

- A file form needs `enctype="multipart/form-data"`; PHP describes the file in `$_FILES`.
- Everything about an upload is chosen by the visitor: content, name, claimed type.
- Check the error code and the size; find the type with `finfo`, from a short list.
- Store under your own name, outside `public/`, and send back with the right type and `nosniff`.
- Check everything before saving anything, so a refused form leaves nothing behind.

## Further reading

- PHP manual, Handling file uploads: https://www.php.net/manual/en/features.file-upload.php
- PHP manual, Error messages explained (`UPLOAD_ERR_*`): https://www.php.net/manual/en/features.file-upload.errors.php
- PHP manual, `finfo`: https://www.php.net/manual/en/class.finfo.php
- OWASP, File Upload Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html
