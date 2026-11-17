# 38. Composer in depth: packages, versions, the lock file, and security advisories

## Goals

- You will be able to add a package to a project with `composer require`, and read what Composer wrote into `composer.json` and `composer.lock`.
- You will be able to read version numbers and constraints such as `^3.0`, and say which versions they allow.
- You will be able to tell `composer install` from `composer update`, and know which one to run when.
- You will be able to check a project for known security problems with `composer audit`.

## Before you start

- Chapter 36: Composer's autoloader and `composer.json`.
- Chapter 3: the setup check, where you ran `composer install` once.

## The idea

In chapter 36, Composer wrote the autoloader for *Shelf*. That is the smaller half of what it does. Its main job is **dependency management**: your project says which **packages** it needs, code that other people wrote and published, and Composer downloads them into `vendor/`, in versions that fit together.

A package has a name in two parts, `vendor/project`, such as `psr/log` or `phpunit/phpunit`. The first part is the person or group that publishes it. Most packages are listed on **Packagist** (packagist.org), the public directory that Composer searches by default. Packagist lists packages; it doesn't check their code.

Three files work together:

- `composer.json` is written by you. It says what the project needs, as **constraints**: "`psr/log`, any version from 3.0 up to, but not including, 4.0".
- `composer.lock` is written by Composer. It records the exact version it chose for every package, including the packages that your packages need.
- `vendor/` holds the downloaded code. It can always be made again from the lock file, so it is never committed.

The course itself is a Composer project: `composer.json` in the course folder needs PHPUnit, PHPStan and PHP-CS-Fixer, the tools of this part, and `composer.lock` pins their versions. That is why everyone who runs `composer install` gets the same tools, and why the checks behave the same on your computer and in CI.

## In code

### Version numbers

Almost every package uses **semantic versioning**: a version has three numbers, MAJOR.MINOR.PATCH, and each one makes a promise.

<!-- include: code/38-composer-in-depth/semver.php -->

- A **patch** release (3.0.1 to 3.0.2) only fixes bugs.
- A **minor** release (3.0.2 to 3.1.0) adds features, but code that worked with 3.0 keeps working.
- A **major** release (3.1.0 to 4.0.0) may change or remove things. Your code may need changes.

The promise is kept by people, so it is not perfect, but it is what makes automatic updates safe enough to use.

### Comparing versions

<!-- include: code/38-composer-in-depth/compare.php -->

As text, `1.9.0` comes after `1.10.0`, because the character `9` comes after `1`. `version_compare` reads each number as a number, so 10 is more than 9. `version_compare($a, $b)` returns a negative number, 0 or a positive number, exactly what `usort` wants from its comparison function (chapter 20). PHP uses it for its own version too: `version_compare(PHP_VERSION, '8.5.0', '>=')`.

### Constraints

`composer.json` rarely names one exact version. It names a range:

| Constraint | Allows | Use it for |
|---|---|---|
| `^3.0` | 3.0.0 up to, not including, 4.0.0 | almost always |
| `^1.2.3` | 1.2.3 up to, not including, 2.0.0 | when you need a fix from 1.2.3 |
| `^0.3` | 0.3.0 up to, not including, 0.4.0 | packages before 1.0, where every minor release may break |
| `~1.2.3` | 1.2.3 up to, not including, 1.3.0 | only patch releases |
| `3.0.2` | exactly 3.0.2 | almost never: you would get no bug fixes |
| `>=8.5` | 8.5 and everything newer | PHP itself, as in *Shelf* |
| `*` | anything | never for a package; fine for a PHP extension |

The caret, `^`, follows the semantic versioning promise: every update that should not break your code. That is what `composer require` writes by default. Here is what `^1.2` checks, written out:

<!-- include: code/38-composer-in-depth/caret.php -->

Composer does this for every package, and for the packages they need, and looks for one version of each that satisfies everyone. If none exists, it says so instead of installing something that may not work.

### A project's `composer.json`

<!-- include: code/38-composer-in-depth/application.json -->

- `require` lists what the project needs to run. `php` and `ext-mbstring` are **platform packages**: Composer doesn't install them, it checks that the PHP running it has them. `ext-*` stands for a PHP extension.
- `require-dev` lists what you need only while developing: tests and analysis. A production server installs without them, with `composer install --no-dev`.
- `config.sort-packages` keeps both lists sorted by name, so two people adding packages don't fight over the order.

You don't write the `require` lines by hand. You run:

```
composer require psr/log
composer require --dev phpunit/phpunit
```

Composer finds the newest version that fits your PHP, writes `"psr/log": "^3.0"` into `composer.json`, installs it, and updates the lock file.

### The lock file

`composer.lock` is long, and you never edit it. One entry, shortened:

<!-- include: code/38-composer-in-depth/lock-entry.json -->

The lock file says "3.0.2", not "^3.0", and `reference` names the exact commit of the package's code. Every `composer install` on any computer installs exactly this, until someone decides to update.

### `install` and `update`

The two commands are easy to mix up, and the difference matters:

| Command | Reads | Changes the lock file | Run it |
|---|---|---|---|
| `composer install` | `composer.lock` | no | after cloning a project, after pulling changes, in CI and on servers |
| `composer update` | `composer.json` | yes: newest versions inside the constraints | when you decide to update, then test and commit the lock file |
| `composer update psr/log` | `composer.json` | only for that package | to update one package |
| `composer require x` | `composer.json` | yes, for `x` | to add a package |

Three more commands help you look before you change anything:

- `composer show` lists the installed packages and their versions. `composer show --tree` shows who needs what.
- `composer outdated --direct` lists the packages in your `composer.json` that have newer versions.
- `composer validate` checks `composer.json` for mistakes.

### Security advisories

Sometimes a security problem is found in a package. The fix comes in a new release, and an **advisory** is published: which versions are affected, how serious it is, and which version fixes it. Composer checks your installed packages against these advisories:

```
composer audit
```

The answer you want is `No security vulnerability advisories found.` Otherwise, it lists each problem with the package, its severity, a title and a link. `composer update` and `composer require` run the same check at the end.

Composer also refuses, by default, to install a version with a known advisory. Asking for an old version of an HTTP client with known problems ends like this (shortened):

```
Your requirements could not be resolved to an installable set of packages.

  Problem 1
    - Root composer.json requires guzzlehttp/guzzle 7.4.4 (exact version match: 7.4.4 or 7.4.4.0),
      found guzzlehttp/guzzle[7.4.4] but these were not loaded, because they are affected by
      security advisories ("PKSA-cnw1-2ytm-cgr8", ...).
```

**Fix:** ask for a version that has the fixes, usually by allowing a range such as `^7.15`. The message also names settings that switch the check off. Use them only after reading the advisory and deciding, in writing, that it doesn't affect your project.

## Common mistakes

### Running the code before installing

<!-- include: code/38-composer-in-depth/no-install.error.php -->

⚠ "Failed opening required 'vendor/autoload.php'": the packages and the autoloader are not there. This is the first thing you see after cloning any PHP project, because `vendor/` is never committed.

**Fix:** run `composer install` in the project's folder.

### `update` when you meant `install`

After cloning a project, `composer update` installs the newest versions instead of the tested ones from the lock file, and changes the lock file for everyone who pulls your next commit.

**Fix:** `install` to get what the project uses; `update` only when you decide to update, followed by the tests.

### Not committing the lock file

Without `composer.lock` in git, each computer gets whatever is newest that day. The tests pass for you and fail in CI, and nobody knows why.

**Fix:** commit `composer.lock` for every application, such as *Shelf*. (A library that other projects install is the one exception: those projects have their own lock files.)

### A package for one line

Each package is code you didn't write and now run: it may have bugs, be abandoned, or, rarely, be taken over by someone with bad intentions. A package that pads a string or checks whether a number is even costs more than it saves.

**Fix:** before `composer require`, ask whether PHP can already do it (`str_pad`, `$n % 2`), and whether the package is maintained: recent releases, more than one maintainer, a clear licence.

## Principle check

**YAGNI** and **KISS** apply to packages twice: every package is code to update and trust, so add one when it saves real work. **Secure by default:** the lock file, `composer audit` and Composer's refusal to install known-bad versions make the safe path the normal one.

## Security

Your dependencies are part of your program, so their security problems are yours. Four habits cover most of it:

- Run `composer audit` regularly, and in CI (Part 12). Update with `composer update`, run the tests, commit the lock file.
- Commit `composer.lock`, so you run the code you tested.
- Composer **plugins** are packages that run code inside Composer itself, while it installs. Composer asks before it allows one (`allow-plugins` in `composer.json`). Say yes only to plugins you know.
- Check a package's name before you install it. A name one letter off from a well-known package is an old trick.

## Shelf

*Shelf* needs no packages: everything it does, PHP can do. Its `composer.json` gets two lines that make it honest and complete:

- `"ext-mbstring": "*"`: *Shelf* uses `mb_strtolower` (chapter 8), which comes from the mbstring extension. Now Composer checks that it is there, instead of *Shelf* failing in the middle of a search.
- `"license": "MIT"`: whoever reads the project knows what they may do with it, and `composer validate` no longer warns.

Change `shelf/composer.json` to:

<!-- include: code/38-composer-in-depth/shelf/composer.json -->

Then check it, and write the autoloader again:

```
composer validate --working-dir=shelf
composer dump-autoload --working-dir=shelf
```

The tools of this part, PHP-CS-Fixer, PHPStan and PHPUnit, are already installed in the course folder, so *Shelf* uses those instead of installing its own copy. In a project of your own, you would add them with `composer require --dev`.

## Exercises

The exercises are in `exercises/38-composer-in-depth/`. Check your work with:

```
composer exercise 38 beginner
```

1. **Beginner, `composer.json`:** complete the `composer.json` of a small project: PHP, an extension, and the autoloader.
2. **Intermediate, `constraints.php`:** write a function that says whether a constraint such as `^1.2` or `~1.2.3` allows a version.
3. **Advanced, `audit.php`:** read a lock file and list the packages that a list of advisories affects, like a small `composer audit`.

## Summary

- `composer.json` says what a project needs, `composer.lock` records the exact versions, and `vendor/` is made from the lock file and never committed.
- MAJOR.MINOR.PATCH: only a major release may break code; `^3.0` allows every release up to 4.0.
- `composer install` installs what the lock file says; `composer update` changes it.
- `composer audit` checks your packages against security advisories; Composer refuses known-bad versions by default.
- Add a package when it saves real work, and check its name and maintenance first.

## Further reading

- Composer docs, Basic usage: https://getcomposer.org/doc/01-basic-usage.md
- Composer docs, Versions and constraints: https://getcomposer.org/doc/articles/versions.md
- Composer docs, Command-line interface (`audit`): https://getcomposer.org/doc/03-cli.md#audit
- Semantic Versioning: https://semver.org/
