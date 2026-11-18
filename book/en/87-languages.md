# 87. Shelf in three languages

## Goals

- You will be able to write a text once for every count, in every language, with ICU's MessageFormat.
- You will be able to show dates and numbers in the order and words of the reader's language.
- You will be able to translate a Symfony app: catalogs, `|trans` in Twig, `t()` in PHP, and an address for each language.
- You will be able to make an exception say its message in the language of the page that shows it, and keep it English for the logs.
- You will be able to turn a page around for Arabic with `dir="rtl"`, and send each member's emails in their own language.

## Before you start

- Chapter 26: exceptions; chapter 33: dates.
- Chapter 72: Symfony's routes; chapter 73: Twig.
- Chapter 80: the stylesheet, written with logical properties; chapter 85: the emails sent by the worker.

## The idea

*Shelf* speaks English. A German reader should get German pages, an Arabic reader Arabic ones, read from the right. Every text the app shows must have its translation, and some texts aren't plain words: dates, numbers and counts.

**Sentences, not pieces.** `$count . ' ' . ($count === 1 ? 'copy' : 'copies')` works in English only. German puts words in other places. Arabic has six forms for a count: zero, one, two, a few (3 to 10), many (11 to 99) and the rest. The unit of translation is the whole sentence, with its values as placeholders: `{count, plural, one {# copy} other {# copies}}`. This is **ICU MessageFormat**, which PHP's `intl` extension formats (`MessageFormatter`), and each language writes the cases it needs. ICU knows each language's rules for which number takes which case.

**Dates and numbers belong to the language.** "Monday, March 16, 2026", "Montag, 16. März 2026" and "الاثنين، 16 مارس 2026" are the same day. 1,234 is 1.234 in German. ICU formats them too: `{day, date, full}` in a message, `|format_date` in Twig.

**The language is in the address.** `/desk` is English, `/de/desk` German, `/ar/desk` Arabic. An address can be shared and bookmarked, a search engine finds each language, and each response has one language (unlike guessing from the browser's `Accept-Language`, which would give two people the same address in two languages). Symfony gives every route a prefix per language and sets the request's *locale* from it; `path()` keeps the locale in every link.

**The key is the English text.** A catalog per language maps each English text to its translation: `translations/messages+intl-icu.de.yaml`. The English text is the key, so templates stay readable, and a text without a translation falls back to English, never to a key like `desk.lend.title`.

**Refusals are texts too.** `Library` throws `"Kindred" is on loan, so it can't be removed.` A page shows that message, and now it must show it in German. But `Library` doesn't know who reads it: the same exception goes to a log, a test or the console in English. So a refusal carries its English text and its values; `getMessage()` is English, and a page translates the exception itself.

**Emails have no page.** The worker sends reminders at night, when no request has a language. So each member's language is stored, and chosen again whenever they log in.

## In code

### One message, every count

<!-- include: code/87-languages/plural.php -->

<!-- include: code/87-languages/plural.out -->

- English uses `one` and `other`; Arabic uses all six. A language that has no `two` case ignores it.
- `#` is the count, in the language's digits and separators; `=0` would be the exact number 0, before the rules.
- The last lines: the date in each language's words, and 1,234 as German writes it, 1.234. The English words around them would come from the catalog.

### Symfony's translator

<!-- include: code/87-languages/translator.php -->

<!-- include: code/87-languages/translator.out -->

- The domain `messages+intl-icu` says the texts are ICU messages, and gives the values to `MessageFormatter`.
- German has no "Log out" here: the translator falls back to English.

## Common mistakes

### A sentence made of pieces

<!-- include: code/87-languages/concat.bad.php -->

✗ The translator gets "copies", "of" and "on the shelf" on their own, and can't put them in German order, or give Arabic its six forms.

**Fix:** one message with placeholders: `{count, plural, one {# copy} other {# copies}} of "{title}" on the shelf`.

### The year 1,979

A number given to an ICU message is formatted as a number: the year 1979 becomes "1,979", an ID 1234 "1,234".

**Fix:** pass years and IDs as strings, `['year' => (string) $year]`.

### A translated exception message

Translating in the exception, `throw new Refused($translator->trans(...))`, makes the domain depend on a translator and on the request, and the log gets German one day and Arabic the next.

**Fix:** the exception keeps its English text and its values, and the page translates it where it is shown.

## Principle check

**Single responsibility:** `Library` says what is refused; the page says it in a language. **DRY:** the English text is the key, written once, in the code where it is used. **Secure by default:** a translation is escaped like any other text; the few that hold a link are marked `|raw` in the template, and the catalogs are part of the code, never input.

## Security

- **Translations are code.** A catalog's text reaches the page with `|raw` where it holds HTML (a link). Translations come from the project, reviewed like code; a text a visitor typed is never a key or a catalog entry.
- **The language comes from a list.** The route's prefix accepts `en`, `de` and `ar` only, and the member's language is checked against `Member::LANGUAGES`: nothing from the address reaches a file name or a query.
- **Values stay values.** `{name}` in a message is filled by `MessageFormatter`, and Twig escapes the result: a member called `<b>` is shown as text in every language.

## Shelf

### An address for each language

`shelf/config/routes.yaml`:

<!-- include: code/87-languages/shelf/config/routes.yaml -->

Each route is there three times, named `desk.en`, `desk.de` and `desk.ar`. `path('desk')` picks the one of the request's locale. The API keeps one English address: programs read its fields, not its words. Logging out is now a route of `AccountController` too, so `/de/logout` exists, and `access_control` accepts the prefixes: `^/((de|ar)/)?loans$`.

The translator, `shelf/config/packages/translation.yaml`:

<!-- include: code/87-languages/shelf/config/packages/translation.yaml -->

Twig's `|format_date` and `|format_number` come from `twig/intl-extra`, and `symfony/translation` brings the translator. Both need PHP's `intl` extension, which the Dockerfiles now install.

### The catalogs

`shelf/translations/` holds `messages+intl-icu.en.yaml`, `.de.yaml` and `.ar.yaml`, one line per text:

```yaml
"Lend {title} to {member}": "أعِر {title} إلى {member}"
"{count, plural, one {# copy} other {# copies}}": "{count, plural, zero {لا نسخ} one {نسخة واحدة} two {نسختان} few {# نسخ} many {# نسخة} other {# نسخة}}"
```

The English catalog maps each text to itself. It looks pointless, but without it the translator would not treat English texts as ICU messages, and a plural would show its braces. `validators.de.yaml` and `.ar.yaml` translate the messages of the form's rules; Symfony's own validation messages come translated with the validator.

### Templates

Every text goes through `|trans`. The 404 page, `shelf/templates/missing.html.twig`:

<!-- include: code/87-languages/shelf/templates/missing.html.twig -->

The frame sets the page's language and direction, and links each page to itself in the other languages, `shelf/templates/base.html.twig` (in the snapshot):

```twig
<html lang="{{ locale }}" dir="{{ locale == 'ar' ? 'rtl' : 'ltr' }}">
```

`dir="rtl"` turns the whole layout around: chapter 80's stylesheet uses `margin-inline-start` and its kin, never `left` or `right`. The arrows are part of the texts ("← All books", "→ كل الكتب"), so each language points its own way. A text in a table that only screen readers get (`.visually-hidden`) could make a phone's page wider than the screen; `.table-wrap` is now `position: relative`, which keeps it inside.

Dates are `|format_date('full', timezone: false)`: a due date is a day, not a moment, so no time zone moves it to the day before.

### Messages after a change

A flash message is a `TranslatableMessage`, made by `t()`, and translated when it is shown, in `shelf/templates/_flash.html.twig`:

<!-- include: code/87-languages/shelf/templates/_flash.html.twig -->

```php
$this->addFlash('notice', t('Removed "{title}".', ['title' => $title]));
```

### Refusals in any language

A refusal carries its text and values, `shelf/src/Says.php`:

<!-- include: code/87-languages/shelf/src/Says.php -->

`Refused` replaces the `InvalidArgumentException`s of `Library`, `Loan`, `Hold` and `Accounts`, and is one, so every `catch` keeps working, `shelf/src/Refused.php`:

<!-- include: code/87-languages/shelf/src/Refused.php -->

`ShelfException` (the "not found" family of chapter 34) uses `Says` too. A controller passes what it caught through `Said::of()` to the template, which shows `{{ error|trans }}`, `shelf/src/Web/Said.php`:

<!-- include: code/87-languages/shelf/src/Web/Said.php -->

### The member's language

`Member` gets a `language` column, `en` for everyone so far, `shelf/migrations/Version20261122090000.php`:

<!-- include: code/87-languages/shelf/migrations/Version20261122090000.php -->

Signing up stores the language of the page; logging in stores it again (`LoginMessages`), so a member who switches to German gets German emails from then on. The emails are `TemplatedEmail`s with `->locale($member->language)`: their templates, their subjects and the links in them are in that language (`ReminderMail`, `AccountMail` and `AccountLinks`, in the snapshot).

A page that doesn't exist has no route, and so no locale: `ErrorPages` reads `/de/` or `/ar/` from the address, so `/de/nothing` gets a German 404.

### Tests

`shelf/tests/TranslationsTest.php` finds every text in the templates (`'…'|trans`) and in `src/` (`t()`, `TranslatableMessage`, and the classes that are `TranslatableInterface`), and checks that each catalog has it, with the same placeholders, and that ICU can read it. A new text without its German and Arabic fails here, not on a page. `LanguagesTest` checks the pages, `shelf/tests/Web/LanguagesTest.php`:

<!-- include: code/87-languages/shelf/tests/Web/LanguagesTest.php -->

`AccessibilityTest` checks German and Arabic pages too: `lang`, `dir`, and a name for each menu. English dates now follow ICU's English order, "Monday, March 16, 2026", and the tests that read them were updated.

## Exercises

The exercises are in `exercises/87-languages/`. Check your work with:

```
composer exercise 87 beginner
```

1. **Beginner, `reservations.php`:** "no reservations", "1 reservation" and "7 Reservierungen", each language as one ICU message.
2. **Intermediate, `language.php`:** the language at the start of an address, and the rest of it.
3. **Advanced, `Catalogs.php`:** the check behind `TranslationsTest`: missing translations, and translations whose placeholders differ.

## Summary

- Translate whole sentences, with placeholders; ICU's `plural` gives each language its own forms.
- `MessageFormatter` and Twig's `|format_date` write dates and numbers as each language does.
- The language is part of the address: a route prefix per language sets the locale, and `path()` keeps it.
- The English text is the key; a missing translation falls back to English.
- An exception keeps its English text and its values; the page translates it.
- `dir="rtl"` and logical properties turn a layout around; a stored language is for the emails no page sends.

## Further reading

- Symfony documentation, translations: https://symfony.com/doc/current/translation.html
- Symfony documentation, how to translate messages using the ICU MessageFormat: https://symfony.com/doc/current/reference/formats/message_format.html
- Symfony documentation, localized routes: https://symfony.com/doc/current/routing.html#localized-routes-i18n
- PHP manual, MessageFormatter: https://www.php.net/manual/en/class.messageformatter.php
- Unicode CLDR, language plural rules: https://www.unicode.org/cldr/charts/latest/supplemental/language_plural_rules.html
- W3C, structural markup and right-to-left text in HTML: https://www.w3.org/International/questions/qa-html-dir
