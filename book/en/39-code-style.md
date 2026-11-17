# 39. Code style: PER Coding Style with PHP-CS-Fixer

## Goals

- You will be able to explain why a team agrees on one code style, and what PSR-1, PSR-12 and PER Coding Style are.
- You will be able to check and fix the style of your code with PHP-CS-Fixer.
- You will be able to read a configuration file for PHP-CS-Fixer, such as the course's.
- You will be able to follow the rules a tool can't fix: names, and files that either declare or do something.

## Before you start

- Chapter 38: Composer, and the tools installed in the course folder.
- Chapter 36: namespaces, and one class per file.

## The idea

PHP doesn't care where you put spaces, line breaks or braces. People do. Code is read far more often than it is written, and reading is faster when every file looks the same: you see the structure, not the author's habits. When a whole team, or a whole community, uses one style, a change to a file shows only what changed, not someone's spaces.

PHP has a shared standard, written by **PHP-FIG**, the PHP Framework Interop Group: people from many PHP projects who agree on rules that help code work together. You met one of their standards in chapter 36, PSR-4 for autoloading. For style, there are three:

- **PSR-1, Basic Coding Standard:** the rules about names and files, such as `StudlyCaps` for class names.
- **PSR-12, Extended Coding Style** (2019): the layout, such as where braces go. It is finished and doesn't change any more, so it knows nothing about newer PHP.
- **PER Coding Style** (PER-CS): the successor of PSR-12. A PER, a PHP Evolving Recommendation, gets new versions as PHP grows, such as rules for enums, `readonly` and property hooks.

This course follows the latest PER Coding Style, and so does every file you have seen. Nobody checks that by eye. A tool, **PHP-CS-Fixer**, reads the code, compares it with the rules and rewrites what doesn't fit. It changes only the layout, never what the code does.

## In code

### Messy, but it runs

<!-- include: code/39-code-style/messy.php -->

Everything is in it: a class, a constant, a method, an `if`. But you have to search for it, and the next person who edits this file will add a fourth style of their own.

### Checking it

From the course folder, `check` reports what would change, and `--diff` shows how:

```
vendor/bin/php-cs-fixer check --diff code/39-code-style/messy.php
```

The answer, shortened:

```
   1) code/39-code-style/messy.php
      ---------- begin diff ----------
 <?php
+
 declare(strict_types=1);
-final class LateFee{
-const CENTS_PER_DAY=20;
-    public function forDays( int $days ):int {
-      if($days<=0){return 0;}
+final class LateFee
+{
+    public const CENTS_PER_DAY = 20;
+    public function forDays(int $days): int
+    {
+        if ($days <= 0) {
+            return 0;
+        }
...
Found 1 of 1 files that can be fixed in 0.025 seconds, 18.00 MB memory used
```

Lines with `-` would go, lines with `+` would come. `check` changes nothing. It ends with an exit code that is not 0, so a script or CI can stop on it (chapter 24).

`fix` makes the changes:

```
vendor/bin/php-cs-fixer fix code/39-code-style/messy.php
```

### Tidy

This is the same code, after the fixer and two blank lines that a person added:

<!-- include: code/39-code-style/tidy.php -->

- One statement per line, four spaces per level, and an opening brace on its own line for classes and methods, but on the same line for `if`.
- Spaces around operators (`$days <= 0`), and none inside parentheses.
- `public` on the constant: every member says its visibility.
- `new LateFee()` with parentheses.
- The output is the same, `60`: style changes how code looks, never what it does.

The fixer doesn't do everything. It left the space in `CENTS_PER_DAY ;` and doesn't add blank lines between the parts of a method, because PER-CS doesn't require them. A blank line before `return` and between members is still the writer's job: it groups the code into steps, like paragraphs.

### Names: PSR-1

A tool can't rename things, because renaming changes code that uses the names. These are the rules, which every chapter has followed:

| What | Style | Example |
|---|---|---|
| class, interface, enum, trait | `StudlyCaps` | `JsonBookRepository` |
| method, function | `camelCase` | `isAvailable()` |
| class constant | `UPPER_CASE` with `_` | `LAST_YEAR` |
| enum case | `StudlyCaps` (this course, as the PHP manual does) | `Role::Reader` |
| variable, property | `camelCase` (this course; PSR-1 only asks for one style) | `$dueDate` |
| namespace | `StudlyCaps`, starting with the vendor | `Shelf` |

PSR-1 adds two rules about files: a file starts with `<?php` and is saved as UTF-8, and a file either **declares** things (classes, functions, constants) or **does** things (prints, reads files, changes settings), not both. The mistakes below show why.

### The configuration file

The fixer reads its rules from `.php-cs-fixer.dist.php` in the folder where it runs. Look at the course's file in the course folder. It is short:

- a **finder** lists the folders to check (`code`, `exercises`, `solutions`, `shelf`, `tools`, `tests`) and leaves some out, such as `vendor/`, the files that show an error on purpose and the messy example above;
- `setRules(['@PER-CS' => true])` chooses the rules: `@PER-CS` is a **rule set**, every rule of the latest PER Coding Style;
- `.dist` means "distributed": the file is committed and shared. A `.php-cs-fixer.php` without `.dist` would be a personal one, which git ignores.

Without a path, `vendor/bin/php-cs-fixer check` checks everything the finder lists. That is what `composer verify` and CI run on every push.

## Common mistakes

### A file that declares and does

<!-- include: code/39-code-style/side-effects.bad.php -->

✗ The `echo` runs whenever the class is loaded: when a test loads it, when another script only wants `Greeting`, even when a tool such as PHPStan reads the project. Code that loads a class doesn't expect it to print, read a file or change a setting.

**Fix:** a file in `src/` only declares. Doing belongs in the script that starts the program, as `shelf.php` does.

### Style changes and real changes in one commit

You fix a bug in one line, and the editor reformats the whole file. The change now shows 80 changed lines, and the reviewer can't find the one that matters.

**Fix:** fix the style first, in a commit of its own (chapter 43), then make the real change. With the fixer run on every file from the start, as here, this rarely happens.

### An editor that disagrees with the fixer

The editor indents with tabs or adds spaces at the end of lines, and the fixer changes them back on every run.

**Fix:** set the editor to four spaces and to remove spaces at the end of lines, or let it run PHP-CS-Fixer when you save. Most editors for PHP can do both.

### Arguing about style

Every rule you add or switch off is a rule each new person has to learn, and a discussion in every review.

**Fix:** use the standard as it is. Its value is that everyone uses the same rules, not that every rule is the best one.

## Principle check

**KISS:** one shared standard and one tool that applies it means nobody thinks about spaces again. **DRY:** the rules live once, in the configuration file, not in every reviewer's head.

## Security

Nothing new here, with one habit worth having: a reformat that touches many lines can hide a small, real change in the middle. Keep style commits apart from other changes, so every changed line in a real change can be read.

## Shelf

*Shelf* stays as it is in this chapter. The course's configuration has checked the folder `shelf/` since *Shelf* began in chapter 6, so every line you copied into it was already in PER Coding Style. Run the check on it yourself:

```
vendor/bin/php-cs-fixer check shelf
```

The answer ends with `Found 0 of ... files that can be fixed`. From now on, run it before you finish a change, together with the checks of the next chapters.

## Exercises

The exercises are in `exercises/39-code-style/`. Check your work with:

```
composer exercise 39 beginner
```

1. **Beginner, `fines.php`:** bring a messy file into PER Coding Style, with the fixer or by hand, without changing what it prints.
2. **Intermediate, `names.php`:** rename a class, its constant and its methods to the names PSR-1 asks for.
3. **Advanced, `lint.php`:** write a small style checker that finds tabs, spaces at the end of lines and lines that are too long.

## Summary

- A shared code style makes code faster to read and changes easier to review.
- PSR-1 sets the names and file rules; PER Coding Style, the successor of PSR-12, sets the layout and grows with PHP.
- `vendor/bin/php-cs-fixer check --diff` shows what would change; `fix` changes it, never what the code does.
- The rules live in `.php-cs-fixer.dist.php`, with the rule set `@PER-CS`.
- A file either declares or does; keep style commits apart from real changes.

## Further reading

- PHP-FIG, PER Coding Style: https://www.php-fig.org/per/coding-style/
- PHP-FIG, PSR-1 Basic Coding Standard: https://www.php-fig.org/psr/psr-1/
- PHP-FIG, PSR-12 Extended Coding Style: https://www.php-fig.org/psr/psr-12/
- PHP-CS-Fixer documentation: https://cs.symfony.com/
