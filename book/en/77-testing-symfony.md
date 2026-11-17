# 77. Testing a Symfony app

## Goals

- You will be able to run tests in Symfony's test environment, with settings and a database of their own.
- You will be able to test pages through a browser for tests: requests, redirects, forms, the HTML.
- You will be able to test an API, a command and a service from the container.
- You will be able to keep every test independent, and the whole suite fast.

## Before you start

- Chapters 41 and 42: PHPUnit and tests that catch bugs.
- Chapter 60: testing with a database.
- Chapter 71: an API's contract.
- Chapters 72 to 76: *Shelf* as a Symfony app.

## The idea

Chapter 73 removed *Shelf*'s `AppTest` and `ApiContractTest`, with the classes they tested. Since then only `web.http` has checked the pages, from the outside, with a real server. It catches a lot, but it is slow to run, and when it fails it shows a difference in a transcript, not the reason. This chapter brings the tests back, the Symfony way.

Symfony's tests use three tools:

- **The test environment.** `APP_ENV=test` reads `.env.test` after `.env`, and the configuration under `when@test`. The tests get their own database, log and covers folder, so they never touch the data you work with, and their own settings, such as a fast password hash.
- **`KernelTestCase`** boots the kernel and gives a test the **container**, with every service, even the ones the application keeps private. A test of a service, a repository or a command takes it from there, configured as in the application.
- **`WebTestCase`** adds a **browser for tests** (`KernelBrowser`, from `symfony/browser-kit`). It sends each request straight to the kernel's `handle()`, as `public/index.php` does, without a server. It keeps cookies, follows redirects when asked, finds a form by its button, fills it in and sends it, with its hidden fields. The answer's HTML comes back as a **crawler** (`symfony/dom-crawler`), which finds elements with CSS selectors.

A test that writes to the database changes the next test's start. Two ways out: make the tables again for every test, which is slow with a real file; or run each test inside a **transaction** and roll it back at its end, so nothing it wrote is ever stored. `dama/doctrine-test-bundle` does the second for every test, on its own.

What to test where:

- **Unit tests** for the rules: `Library`, `Isbn`, the voters. No kernel, milliseconds each.
- **Kernel tests** for what needs the configuration: repositories against the real database, commands.
- **Browser tests** for what a visitor does: log in, send a form, get refused. They go through the routing, the firewall, the controllers and Twig, so they catch what unit tests can't: a wrong route, a missing `#[IsGranted]`, a template that breaks.
- **`web.http`**, a few real requests to a real server: the front controller, the cookies, the headers.

## In code

The examples are a Composer project of their own (`code/77-testing-symfony/composer.json`):

<!-- include: code/77-testing-symfony/composer.json -->

A very small application to test, `code/77-testing-symfony/Guestbook.php`:

<!-- include: code/77-testing-symfony/Guestbook.php -->

### A browser for tests

<!-- include: code/77-testing-symfony/browser.php -->

<!-- include: code/77-testing-symfony/browser.out -->

- `request()` returns the page as a crawler; `getResponse()` is the whole answer, with its status and headers.
- `submitForm('Sign', [...])` finds the form of the button "Sign", fills in its fields, and sends it with its method, to its address.
- A redirect is not followed until `followRedirect()`: a test can check the `303` and its `Location` first.

### Reading HTML

<!-- include: code/77-testing-symfony/crawler.php -->

<!-- include: code/77-testing-symfony/crawler.out -->

- `filter()` takes a CSS selector; `text()` is the text of the first match, `count()` the number of matches.
- `selectLink()` and `selectButton()` find a link and a button by their text, as a visitor would.
- Addresses come back whole, against the page's address: the browser sends them as they are.

## Common mistakes

### Tests that need each other

<!-- include: code/77-testing-symfony/shared.bad.php -->

✗ The second test passes only after the first one, in the same run. Run it alone, in another order, or in parallel, and it fails; and the first test leaves *Sula* in the database for every test after it.

**Fix:** each test makes what it needs, and leaves nothing behind: fixtures in `setUp()`, and a transaction rolled back at the end of each test, as *Shelf*'s tests do.

### The tests on the dev database

A suite that runs with `APP_ENV=dev` deletes and adds the books you are working with, and a test that passes on your data fails on someone else's.

**Fix:** `APP_ENV=test` with `force="true"` in `phpunit.xml.dist`, so a shell that sets `APP_ENV` changes nothing; a database per environment, as *Shelf*'s `DATABASE_URL` names it.

### Comparing the whole page

`assertSame($expectedHtml, $client->getResponse()->getContent())` fails when a space moves or the navigation gains a link, and says nothing about what broke.

**Fix:** check what the test is about: the status, one heading, one message, the number of rows, with selectors. Symfony's assertions name the selector and what they found when they fail.

## Principle check

**DRY:** what every browser test needs, a client and three rows, is in one base class. **KISS:** a test reads like what a visitor does: open the page, fill in the form, press the button. **Secure by default:** every refusal has a test: no session, the wrong role, no token, a wrong token.

## Security

- **Test the refusals through the whole stack.** A voter's unit test proves the rule; a browser test proves that the rule is asked: a controller that forgets `#[IsGranted]` passes the first and fails the second.
- **Tests never use real secrets.** `.env.test` is in Git: its `APP_SECRET` is for the tests only, and the passwords in the tests are made up.
- **Secure cookies in tests.** *Shelf*'s session cookie is `Secure`, so the browser for tests sends it only over HTTPS: the tests ask for `https://` addresses, as a real visitor's browser does.

## Shelf

*Shelf* gets tests for its pages, its API and its commands again. Writing them found two problems:

- **Lending a book with an ISBN failed.** Doctrine's `refresh()` sets every column of the book again, and a new `Isbn` object, though equal, is another object, which a `readonly` property refuses. Chapter 74's tests lent only books without an ISBN; the first browser test that lent *Kindred* found it.
- **`shelf:password` couldn't be tested.** It read the password from `STDIN` itself, which a test can't fill. It now reads from its input's stream, which is `STDIN` when it runs, and the test's lines in a test.

```
cd shelf
composer require --dev symfony/browser-kit symfony/css-selector dama/doctrine-test-bundle
```

`shelf/composer.json`:

<!-- include: code/77-testing-symfony/shelf/composer.json -->

The bundle's recipe is not one of Symfony's own, so Flex leaves it out; the bundle is added by hand, for the test environment only, `shelf/config/bundles.php`:

<!-- include: code/77-testing-symfony/shelf/config/bundles.php -->

### The test environment

The settings of the tests, `shelf/.env.test`:

<!-- include: code/77-testing-symfony/shelf/.env.test -->

PHPUnit sets the environment, and the bundle's extension wraps each test in a transaction, `shelf/phpunit.xml.dist`:

<!-- include: code/77-testing-symfony/shelf/phpunit.xml.dist -->

Before the first test, the settings are read and the migrations bring the test database up to date, `shelf/tests/bootstrap.php`:

<!-- include: code/77-testing-symfony/shelf/tests/bootstrap.php -->

Symfony's own messages go into a file in the tests, not between PHPUnit's lines, `shelf/config/services.yaml`:

<!-- include: code/77-testing-symfony/shelf/config/services.yaml -->

### The pages

What every browser test needs, `shelf/tests/Web/ShelfWebTestCase.php`:

<!-- include: code/77-testing-symfony/shelf/tests/Web/ShelfWebTestCase.php -->

- `createClient()` boots the kernel in the test environment, and returns the browser.
- `loginUser()` logs a member in without the login form, for tests of what comes after it. The login form has tests of its own.
- `getContainer()` is the test container: it has `BookRepository` and the other services, though the application keeps them private.

The tests, `shelf/tests/Web/PagesTest.php`:

<!-- include: code/77-testing-symfony/shelf/tests/Web/PagesTest.php -->

- `assertResponseRedirects()`, `assertSelectorTextSame()` and `assertInputValueSame()` come with `WebTestCase`; when one fails, it says what it found.
- `submitForm()` sends the token the page put into the form; a request sent with `request()` alone has none, which is how the tests check the refusals.
- After adding *Sula*, the test asks the repository, not only the page: it is in the database.

### The API

Chapter 71's contract, through the kernel, `shelf/tests/Web/ApiContractTest.php`:

<!-- include: code/77-testing-symfony/shelf/tests/Web/ApiContractTest.php -->

The shapes, the mistakes and the addresses that need a token are chapter 71's. `send()` issues each member's token through `ApiTokens` once, and the token test also uses the one that `POST /api/tokens` returns. The browser's cookie jar stays empty: the API never starts a session.

### The commands

`CommandTester` runs a command as `bin/console` does, and keeps what it wrote, `shelf/tests/Command/CommandsTest.php`:

<!-- include: code/77-testing-symfony/shelf/tests/Command/CommandsTest.php -->

The password test gives the command its input with `setInputs()`. The command now reads it from its input's stream, `shelf/src/Command/PasswordCommand.php`:

<!-- include: code/77-testing-symfony/shelf/src/Command/PasswordCommand.php -->

### The fix for the ISBN

A new contract test lends a book with an ISBN, in memory and in the database, `shelf/tests/LoanRepositoryContract.php`:

<!-- include: code/77-testing-symfony/shelf/tests/LoanRepositoryContract.php -->

It failed with "Attempting to change readonly property Shelf\Book::$isbn". `isbn` becomes `private(set)`, as `copies` is: only `Book` can change it, and Doctrine may set it again, `shelf/src/Book.php`:

<!-- include: code/77-testing-symfony/shelf/src/Book.php -->

### Run them

```
vendor/bin/phpunit -c shelf
```

139 tests in about two seconds. `web.http` sends the same requests as in chapter 76, with the same answers.

## Exercises

The exercises are in `exercises/77-testing-symfony/`. This time you write the tests: each `bugs/` folder holds versions of the code with one mistake each, and your tests must pass on the right code and fail on every bug. Check your work with:

```
composer exercise 77 beginner
```

1. **Beginner, `LoanVoterTest.php`:** test a voter: who may return and extend which loan, a visitor, and the questions it leaves to other voters.
2. **Intermediate, `BookFinderTest.php`:** test a Doctrine repository against a real database: filters, order, pages, the count, the refusals.
3. **Advanced, `SuggestionsTest.php`:** test a page through a browser: the form, its mistakes, its token, and HTML that must show as text.

## Summary

- The test environment has its own settings and database; `force="true"` makes sure the tests use it.
- `KernelTestCase` gives a test the container; `WebTestCase` a browser that sends requests to the kernel.
- The crawler reads the page with CSS selectors; `submitForm()` sends a form as a browser does.
- Every test starts from the same data: fixtures in `setUp()`, and a transaction rolled back after each test.
- Test the rules alone, and the refusals through the whole stack.

## Further reading

- Symfony documentation, Testing: https://symfony.com/doc/7.4/testing.html
- Symfony documentation, The DomCrawler component: https://symfony.com/doc/7.4/components/dom_crawler.html
- Symfony documentation, The BrowserKit component: https://symfony.com/doc/7.4/components/browser_kit.html
- Symfony documentation, Console commands, Testing commands: https://symfony.com/doc/7.4/console.html#testing-commands
- DAMA DoctrineTestBundle: https://github.com/dmaicher/doctrine-test-bundle
