# 43. Git basics for one developer

## Goals

- You will be able to put a project under version control with Git, and record changes as commits.
- You will be able to see what changed with `git status`, `git diff` and `git log`, and undo a change that isn't committed yet.
- You will be able to keep generated files out of the repository with `.gitignore`.
- You will be able to keep secrets out of your code and your repository, and know what to do when one gets in.

## Before you start

- Chapter 38: `vendor/` and `composer.lock`, and which of them belongs in a project.
- Chapter 41: *Shelf*'s tests, which you run before each commit.

## The idea

You change a file, and the program stops working. What did it look like this morning? Copies named `shelf-old.php` and `shelf-final-2.php` work for a day, and then nobody knows which is which.

**Git** is a **version control system**: it records the state of a project's files, step by step, with a note for each step, so that you can see every change, when you made it and why, and go back. It works on your computer, without a server or an account. Almost every software project uses it, and every PHP package you install with Composer comes from a Git repository.

Five words cover the basics:

- A **repository** is a project folder whose history Git keeps, in a hidden folder `.git/` inside it.
- A **commit** is one recorded state of the files, with a message, the author and the date. The history is the list of commits.
- The **working tree** is the files as they are now, with your changes.
- The **staging area** is what will go into the next commit. You choose with `git add`, so that one commit holds one change, even if you edited three things today.
- A **branch** is a line of commits, by default called `main`. One developer can do everything in this chapter on `main`.

The rule that matters most: **commit small and often.** A commit that does one thing, with a message that says why, is easy to read, to check and to undo.

## In code

This chapter's code is mostly commands. Install Git from git-scm.com, or with your system's package manager, then tell it who you are, once:

```
git config --global user.name "Amira Haddad"
git config --global user.email "amira@example.com"
```

Every commit records this name and email address. If your repository will be public later, use an address you don't mind being public; code hosting services can give you a private one.

### A repository for *Shelf*

You downloaded the course as a ZIP file in chapter 2, so the course folder is not a repository. Make *Shelf* one, in its folder:

```
cd shelf
git init -b main
git status
```

`-b main` names the first branch `main`. `git status` lists every file as **untracked**: Git sees them, but doesn't record them yet. The list includes `vendor/` and `.phpunit.cache/`, which don't belong in the history (chapter 38): Composer and PHPUnit make them again whenever needed. Tell Git to leave them out, with a file `.gitignore` next to `composer.json`:

<!-- include: code/43-git-basics/shelf/.gitignore -->

Each line is a pattern. `/vendor/` means the folder `vendor` here, at the top of the repository; without the first `/`, it would match a `vendor` folder at any depth. A line starting with `#` is a comment. Run `git status` again: `vendor/` and `.phpunit.cache/` are gone from the list.

### The first commit

```
git add .
git status
git commit -m "Start Shelf's repository"
```

`git add .` stages everything in this folder that isn't ignored. Before you commit, read the list `git status` shows: this is the moment to notice a file that shouldn't be there. `git commit -m "..."` records the staged files, with the message.

### A change, step by step

Lend a book with `php shelf.php lend "Things Fall Apart" Amira` (or change any file), then:

```
git status
git diff
```

`git diff` shows each changed line: `-` for the old version, `+` for the new one, like PHP-CS-Fixer in chapter 39. Then, the usual cycle:

```
git add books.json
git commit -m "Lend Things Fall Apart to Amira"
git log --oneline
```

Before a commit that changes code, run the tests, in a second terminal in the course folder: `vendor/bin/phpunit -c shelf`.

`git log --oneline` lists the commits, newest first, each with a short **hash**, a code such as `cc23893` that names it. `git show cc23893` shows what that commit changed.

| You want to | Command |
|---|---|
| see what changed, and what is staged | `git status` |
| see the changed lines (not staged yet) | `git diff` |
| see the staged lines, just before you commit | `git diff --staged` |
| stage one file, or everything | `git add src/Loan.php`, `git add .` |
| unstage a file, keeping your change | `git restore --staged src/Loan.php` |
| throw away a change that isn't committed | `git restore src/Loan.php` |
| record the staged changes | `git commit -m "Move the due date into Loan"` |
| read the history | `git log --oneline`, `git show <hash>` |

`git restore` without `--staged` can't be undone: the change was never committed, so Git has no copy of it. Read `git diff` first.

### Good commit messages

The message is for the person who reads the history in a year, often you. The first line is a summary of at most about 50 characters, written as an instruction: "Move the due date into Loan", not "moved stuff". If the why isn't obvious, leave a blank line and explain it below. Write it with `git commit` without `-m`, which opens your editor.

If you cloned the course with `git clone` instead of downloading it, the course folder is already a repository. Then don't run `git init` in `shelf/`, which would put a repository inside another one. Create a branch of your own with `git switch -c my-work` and commit there; `main` stays as the course published it.

## Common mistakes

### A secret in the code

<!-- include: code/43-git-basics/mail-key.bad.php -->

✗ A key in the code is in every copy of the code: in the repository, in its history, in every clone, and on every server that receives it. Deleting the line in the next commit doesn't help, because the old commit still contains it, and `git log -p` shows it to anyone with a copy.

**Fix:** the key comes from outside the code, from an **environment variable** that the computer running the program sets:

<!-- include: code/43-git-basics/mail-key.php -->

`getenv('SHELF_MAIL_KEY')` reads the variable, or returns `false` when it isn't set. The program says what is missing and stops, instead of running without a key. You start it with the key in front of the command: `SHELF_MAIL_KEY=... php mail-key.php`. Part 9 keeps such settings in a `.env` file, which `.gitignore` leaves out.

### A secret that was committed anyway

It happens. The order of what to do matters:

1. **Make the secret useless first.** Create a new key and switch the old one off, at the service that issued it. From the moment a secret was committed, treat it as known to others: you can't know who has a copy.
2. Then remove it from the code, as above, and commit.
3. Removing it from the history needs tools that rewrite all commits. It is worth doing, but it is never a replacement for step 1.

### Committing generated files

`vendor/` in the repository makes it a hundred times bigger, and every `composer update` shows thousands of changed lines that nobody reads.

**Fix:** `.gitignore` for everything a tool makes: `vendor/`, caches, the built book. If a file is already committed, `git rm --cached <file>` takes it out of the repository and keeps it on your disk.

### One commit for everything

"Changes" with forty files: a bug fix, a new feature and a style cleanup. If one part is wrong, you can't undo it alone.

**Fix:** stage and commit each change separately, with `git add <file>`; `git diff --staged` shows what you are about to commit.

## Principle check

**Secure by default:** secrets never enter the code, so they can't enter the history. **KISS:** small commits with clear messages, on one branch, are all one developer needs. **DRY:** the repository is the one copy of the truth; no `-old` and `-final` files.

## Security

A repository is shared more widely and kept longer than you expect: a public code host, a backup, a colleague's laptop, a hosting service that builds from it. Three habits:

- Never put passwords, keys or tokens in code or in committed files. Environment variables for now; Part 9 adds a `.env` file that stays out of git.
- Read `git status` and `git diff --staged` before each commit, as the last check that no secret and no generated file slips in.
- When a secret was committed, replace it first, and clean up the history second.

## Shelf

*Shelf* becomes a repository of its own, with a `.gitignore` for the files that tools make. Create `shelf/.gitignore`:

<!-- include: code/43-git-basics/shelf/.gitignore -->

Then, in the folder `shelf/`, as above:

```
git init -b main
git add .
git status
git commit -m "Start Shelf's repository"
```

From now on, each change to *Shelf* ends with the same steps:

1. `vendor/bin/php-cs-fixer check shelf`, `vendor/bin/phpstan analyse -c shelf/phpstan.neon.dist` and `vendor/bin/phpunit -c shelf` in the course folder: style, types and rules;
2. `git diff`, to read your change once more;
3. `git add`, `git diff --staged` and `git commit`, one change per commit.

`books.json` changes whenever you lend or add a book. For now, it is *Shelf*'s sample data, and committing it is fine. In a real library, data doesn't belong in the code's history; Part 8 moves it into a database.

Part 6 is complete. *Shelf* is a tested project with a strict style and type check, and a history. Part 7 takes it to the web.

## Exercises

The exercises are in `exercises/43-git-basics/`. Check your work with:

```
composer exercise 43 beginner
```

1. **Beginner, `.gitignore`:** write the ignore rules for a small web project, so that Git leaves out exactly the right files.
2. **Intermediate, `secrets.php`:** write a check that finds secrets in files before they are committed.
3. **Advanced, `messages.php`:** write a check for commit messages: a short summary, as an instruction, and a blank line before the body.

## Summary

- A repository keeps the history of a project as commits; `git add` chooses what goes into the next one.
- `git status`, `git diff` and `git log` show where you are; `git restore` throws away an uncommitted change.
- `.gitignore` keeps generated files, such as `vendor/` and caches, out of the repository.
- Commit small and often, with a summary that says what the commit does.
- Secrets come from the environment, never from the code; a committed secret is replaced first, then removed.

## Further reading

- Git documentation, Git Basics (Pro Git, chapter 2): https://git-scm.com/book/en/v2/Git-Basics-Getting-a-Git-Repository
- Git documentation, `gitignore`: https://git-scm.com/docs/gitignore
- PHP manual, `getenv`: https://www.php.net/manual/en/function.getenv.php
