# 80. A design that looks finished

## Goals

- You will be able to build pages from small Twig components, each with one job, that get only what they need.
- You will be able to move what a page shows into PHP, as Twig filters you can test.
- You will be able to make every page usable with a keyboard and a screen reader, and test it.
- You will be able to serve a stylesheet that browsers keep for a year, and still see every change at once.

## Before you start

- Chapter 49: *Shelf*'s first layout and stylesheet.
- Chapter 73: Twig, layouts and escaping.
- Chapter 77: testing pages with a browser for tests.
- Chapter 78: *Shelf*'s production image and Caddy.

## The idea

Part 13 makes *Shelf* the app a real library could run, one chapter for each thing its readers and its staff expect. The first is how it looks: people judge an app in seconds, and an app that looks unfinished is trusted less, however good its code. Until now, *Shelf* had a list of titles, a page per book and a few forms. This chapter gives it a layout, a grid of books with covers, a page per book with its facts, a table of loans that says what is due, and a look that works on a phone, in dark mode and with a screen reader.

Three ideas carry the design:

- **Components.** A page is put together from small templates, each with one job: a book's card, a status badge, an empty list. A component gets exactly what it needs with `{% include '...' with {...} only %}`; `only` keeps the page's own variables out, so a component can't pick up a value by accident. `{% embed %}` is an include whose blocks the page fills in, for a component with a hole in it, such as the action under an empty list.
- **Display values in PHP.** "Due in 3 days" or "2 days overdue" depends on today's date, and the colour of a placeholder cover depends on the title. That is logic, so it belongs in a PHP class with tests, not in a template. A method with `#[AsTwigFilter]` becomes a filter: `{{ loan.due|due }}`.
- **Accessibility from the start.** A skip link, landmarks (`header`, `nav`, `main`, `footer`), one `h1` per page, a label for every field, `alt` on every image, the current page marked with `aria-current`, and a status always in words, never by colour alone. Each of these costs a line when a page is written, and a redesign when it is forgotten. A test checks all of them on every page.

**Versioned assets.** A browser that keeps a stylesheet for a year is fast, but it doesn't see a new one. **AssetMapper**, Symfony's component for CSS, images and JavaScript, names each file after its content: `app.css` is served as `app-FJGfM66.css`. A changed file gets a new name, so browsers may keep each name forever. `asset('styles/app.css')` in a template writes the current name. In `dev`, Symfony serves the files itself; for production, `asset-map:compile` writes them to `public/assets/` once, when the image is built.

*Shelf* still runs no JavaScript at all: its Content Security Policy (chapter 64) allows none, and nothing in this chapter needs any. The menu is a row of links that wraps on a phone, not a menu that opens with a script.

## In code

The examples are a Composer project of their own (`code/80-a-design-that-looks-finished/composer.json`):

<!-- include: code/80-a-design-that-looks-finished/composer.json -->

### Components

A badge, `code/80-a-design-that-looks-finished/templates/_badge.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/templates/_badge.html.twig -->

A card that uses it, `templates/_card.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/templates/_card.html.twig -->

An empty list with a block to fill, `templates/_empty.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/templates/_empty.html.twig -->

The page, `templates/shelf.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/templates/shelf.html.twig -->

<!-- include: code/80-a-design-that-looks-finished/components.php -->

<!-- include: code/80-a-design-that-looks-finished/components.out -->

- The card gives the badge two values, and the badge needs no other.
- `embed` keeps the empty list's frame and puts the page's link into its block.

### What a component sees

`templates/scope.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/templates/scope.html.twig -->

<!-- include: code/80-a-design-that-looks-finished/scope.php -->

<!-- include: code/80-a-design-that-looks-finished/scope.out -->

- Without `only`, the include forgot to pass `level`, and nothing said so: the badge took the page's `danger` and turned red.
- With `only` and `strict_variables` (on in `dev` and in tests), the same mistake is an error at once.

### Filters from attributes

<!-- include: code/80-a-design-that-looks-finished/filters.php -->

<!-- include: code/80-a-design-that-looks-finished/filters.out -->

- `mb_` functions get the first letter of "ébène" right; `substr()` would cut the É in half.
- What a filter returns is still escaped: the last title shows `&lt;`, not a `<b>` tag. A filter that returns HTML on purpose says so with `is_safe: ['html']`, and then escapes what it puts in itself.

## Common mistakes

### A status in colour alone

<!-- include: code/80-a-design-that-looks-finished/templates/badge.bad.html.twig -->

✗ A screen reader announces nothing, and about one man in twelve can't tell this green from this orange.

**Fix:** words first, colour as a second signal: `<span class="badge ok">Available</span>`.

### A component that sees everything

`{% include '_badge.html.twig' %}` without `with ... only` hands the component every variable of the page. It works until a page has a variable with the same name, as `scope.php` shows, and then it shows the wrong thing without an error.

**Fix:** `with {...} only` on every include of a component, and `strict_variables` in development and tests, so that a missing value fails loudly.

### A stylesheet that browsers keep too long, or never

`<link href="/style.css">` with a long cache time: visitors see the old design for days after a change. Without a cache time: every page loads the stylesheet again.

**Fix:** versioned names, from AssetMapper, and a cache time of a year for `/assets/`, as *Shelf*'s Caddyfile sets it.

## Principle check

**DRY:** a badge or a book's card is written once, and every page uses the same one. **KISS:** plain CSS with variables, no framework, no build tool beyond `asset-map:compile`, and no JavaScript. **Secure by default:** components get only what they are given; filters' output is escaped; the CSP still allows no script.

## Security

- **Escaping stays on.** A component, a filter and `embed` all escape by default. `|raw` is still only for HTML you made yourself (chapter 73).
- **No third-party fonts or scripts.** *Shelf* uses the fonts of the visitor's system: a font from another site would tell that site about every visit, and would need its own entry in the CSP.
- **Accessibility is part of quality.** In many countries public services must meet the Web Content Accessibility Guidelines (WCAG); a test that checks every page keeps *Shelf* there.

## Shelf

*Shelf* gets its new look: a layout with a skip link and landmarks, components, covers on the list, a table of loans, a page per book with its facts, and a panel for the staff. The CSS is long, and teaches CSS more than PHP, so copy `shelf/assets/styles/app.css` as it is. Its comments say what each part does; it uses logical properties (`margin-inline-start` instead of `margin-left`), so the Arabic edition in chapter 87 turns the layout around with `dir="rtl"` alone.

```
cd shelf
composer require symfony/asset-mapper:7.4.* symfony/asset:7.4.* symfony/clock:7.4.*
```

The recipe of `symfony/asset-mapper` also adds `assets/app.js` and `importmap.php`, for JavaScript. *Shelf* has none, so delete both. `shelf/composer.json`:

<!-- include: code/80-a-design-that-looks-finished/shelf/composer.json -->

`shelf/config/packages/asset_mapper.yaml`:

<!-- include: code/80-a-design-that-looks-finished/shelf/config/packages/asset_mapper.yaml -->

`public/style.css` moves to `assets/styles/app.css`. PHP's built-in server answers a missing file with a 404 without asking `index.php`, so `compose.yaml` now names `index.php` as the router, and Symfony serves the stylesheet while you work:

<!-- include: compose.yaml -->

### The frame

`shelf/templates/base.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/shelf/templates/base.html.twig -->

`app.current_route` is the name of the page's route, which marks the current link in the menu.

### The components

`shelf/templates/components/_badge.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/shelf/templates/components/_badge.html.twig -->

`shelf/templates/components/_cover.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/shelf/templates/components/_cover.html.twig -->

`shelf/templates/components/_book_card.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/shelf/templates/components/_book_card.html.twig -->

`shelf/templates/components/_empty.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/shelf/templates/components/_empty.html.twig -->

### The pages

`shelf/templates/list.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/shelf/templates/list.html.twig -->

`shelf/templates/book.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/shelf/templates/book.html.twig -->

`shelf/templates/loans.html.twig`:

<!-- include: code/80-a-design-that-looks-finished/shelf/templates/loans.html.twig -->

The other pages get the same frame and classes: `login.html.twig`, `add.html.twig`, `missing.html.twig` and `forbidden.html.twig`, in the chapter's snapshot.

### The filters

`shelf/src/Twig/ShelfExtension.php`:

<!-- include: code/80-a-design-that-looks-finished/shelf/src/Twig/ShelfExtension.php -->

`ClockInterface` is Symfony's clock: the real time in the app, and a time a test chooses with `mockTime()`. `shelf/tests/Twig/ShelfExtensionTest.php`:

<!-- include: code/80-a-design-that-looks-finished/shelf/tests/Twig/ShelfExtensionTest.php -->

### Tests for every page

`shelf/tests/Web/AccessibilityTest.php`:

<!-- include: code/80-a-design-that-looks-finished/shelf/tests/Web/AccessibilityTest.php -->

`PagesTest` checks the new markup, and the due date's badge on three days, `shelf/tests/Web/PagesTest.php`:

<!-- include: code/80-a-design-that-looks-finished/shelf/tests/Web/PagesTest.php -->

### In production

The image compiles the stylesheet, `shelf/Dockerfile`:

<!-- include: code/80-a-design-that-looks-finished/shelf/Dockerfile -->

Caddy serves it, and tells browsers to keep it, `shelf/docker/Caddyfile`:

<!-- include: code/80-a-design-that-looks-finished/shelf/docker/Caddyfile -->

`/public/assets/` is made again for each image, so Git and the image's build context leave it out (`.gitignore`, `.dockerignore`).

## Exercises

The exercises are in `exercises/80-a-design-that-looks-finished/`. Check your work with:

```
composer exercise 80 beginner
```

1. **Beginner, `availability.html.twig`:** a badge that says how many copies of a book are on the shelf, in words and colour.
2. **Intermediate, `DueLabel.php`:** how a due date reads today, "due tomorrow" or "3 days overdue", right across midnight, a new year and summer time.
3. **Advanced, `AccessibilityCheck.php`:** a checker for a whole page: its language, its headings, images, fields and links, with PHP's HTML5 parser.

## Summary

- Pages are put together from components; `with {...} only` gives each exactly what it needs, and `embed` fills its blocks.
- What a page shows, such as a due date's label, is worked out in PHP and tested; `#[AsTwigFilter]` makes it a filter.
- Every page has a skip link, landmarks, one `h1`, labels, `alt` texts and statuses in words; a test checks each page.
- AssetMapper names each file after its content, so browsers may keep it for a year.
- *Shelf* still runs no JavaScript, and its layout is ready to turn right to left.

## Further reading

- Twig documentation, include: https://twig.symfony.com/doc/3.x/tags/include.html
- Twig documentation, embed: https://twig.symfony.com/doc/3.x/tags/embed.html
- Symfony documentation, AssetMapper: https://symfony.com/doc/7.4/frontend/asset_mapper.html
- Symfony documentation, Twig extensions: https://symfony.com/doc/7.4/templates.html#writing-a-twig-extension
- W3C, Web Content Accessibility Guidelines (WCAG) 2.2: https://www.w3.org/TR/WCAG22/
- MDN Web Docs, CSS logical properties and values: https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_logical_properties_and_values
