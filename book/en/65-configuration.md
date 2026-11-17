# 65. Configuration and secrets

## Goals

- You will be able to read settings from environment variables with `getenv()`.
- You will be able to keep settings in a `.env` file, and know which file goes into Git and which never does.
- You will be able to check every setting when the program starts, and stop with a clear message.
- You will be able to move *Shelf*'s paths into its settings.

## Before you start

- Chapter 45: Docker and `compose.yaml`.
- Chapter 43: Git and `.gitignore`.
- Chapter 59: the database passwords in the environment.

## The idea

The same code runs in several places: on your computer, in CI, on a server. Some things differ between them: where the database is, whether errors are shown, the password for the mail server. Those are **settings**, or **configuration**. They don't belong in the code, for two reasons:

- **They differ.** Code that says `var/shelf.sqlite` runs only where that path is right. Changing a file in Git for each server means every server runs different code.
- **Some are secrets.** A password in a PHP file is in Git: in every copy of the repository, in its history even after you delete the line, and on every computer that ever cloned it. A leaked repository then leaks the database too.

The usual answer, used by most frameworks and hosting services, is **environment variables**: named values that the operating system gives a program when it starts. Docker sets them from `compose.yaml` (chapter 59), a server from its own configuration. PHP reads them with `getenv()`.

Typing a dozen variables before every start is tiresome on a developer's computer, so projects keep them in a file, **`.env`**, one `NAME=value` per line, which the program reads when it starts. Three layers, each overriding the one before:

1. **`.env`**, in Git: what a fresh copy needs to run on a developer's computer. Never a secret.
2. **`.env.local`**, listed in `.gitignore`: changes for this one computer, and its secrets.
3. **Real environment variables**: what the server or Docker sets. They win over both files, so a server never needs to edit a file.

Symfony uses exactly these names (Part 11). Whatever the source, read the settings once, at the start, and check them there: a missing setting should stop the program with its name, not cause a strange error an hour later.

## In code

### Environment variables

<!-- include: code/65-configuration/env.php -->

- `getenv('NAME')` returns the value as a string, or `false` when nobody set it.
- `compose.yaml` passes `SHELF_APP_PASSWORD` to the course's containers. The example says only *whether* it is set: printing a secret, even to the screen, is how secrets end up in logs and screenshots.

### A .env file

The settings, `code/65-configuration/library.env`:

<!-- include: code/65-configuration/library.env -->

A function that reads such a file, `code/65-configuration/envfile.php`:

<!-- include: code/65-configuration/envfile.php -->

<!-- include: code/65-configuration/dotenv.php -->

- Every line is `NAME=value`. Names are capitals, by custom, so they stand out from the code's own names.
- A line that doesn't fit stops the program with the file and the line number. Guessing what a broken line meant would hide the mistake.
- The values are strings. Turning `14` into a number, or `yes` into `true`, is the job of the code that checks the settings.

### Three layers

`code/65-configuration/library.local.env` stands for the file of one computer:

<!-- include: code/65-configuration/library.local.env -->

<!-- include: code/65-configuration/layers.php -->

The spread operator, `...`, puts the three arrays together; for a name that is in more than one, the later array wins. The server's database path replaces the one in the file, and the local file switches debugging off and adds the password.

## Common mistakes

### A secret in the code

<!-- include: code/65-configuration/secret.bad.php -->

✗ The password is in Git. Deleting the line in the next commit doesn't help: the history keeps it, and so does every clone.

**Fix:** read it from the environment, and keep it in `.env.local` on your computer. If a secret ever reached Git, or a public place, treat it as known to everyone: change the password first, then clean up.

### A setting nobody set

<!-- include: code/65-configuration/missing.error.php -->

⚠ `getenv()` returned `false`, and the error appears where the value is used: deep in the code, maybe hours after the start, with a message that names `$dsn`, not the setting.

**Fix:** read and check every setting at the start, in one place, and stop with the setting's name: "The setting SHELF_DATABASE is missing". *Shelf*'s `Config` does it below.

### A .env file that is served

A `.env` file inside the folder the web server serves, such as `public/`, can be downloaded by anyone who guesses its name: `https://shelf.example/.env`. Attackers try that address on every site.

**Fix:** keep settings files outside the served folder. *Shelf* serves only `public/`; its `.env` is one folder above.

## Principle check

**Secure by default:** secrets never reach Git, only `.env.local` and the environment hold them, and the settings are checked before anything runs. **KISS:** a file of `NAME=value` lines and a 20-line reader. **DRY:** every setting is read in one class; the rest of the code gets ready values from it.

## Security

- **`.gitignore` before the first secret.** Add `.env.local` to `.gitignore` before you create it. `git status` shows you what Git would add.
- **Different secrets per place.** Your computer, CI and the server each get their own passwords. A leak in one place then opens nothing else.
- **Secrets stay out of output.** Don't print them, don't log them (chapter 66), and keep `phpinfo()` off servers: it prints every environment variable.
- **On a server**, the environment is set by the hosting service, by Docker, or by the service manager. Part 12 sets up *Shelf*'s.

## Shelf

*Shelf*'s paths become settings, read from `.env` and the environment, and checked when it starts. It has no secrets yet; the next one, a database password, will go into `.env.local` or the server's environment, with no code change.

**The settings in Git,** `shelf/.env`:

<!-- include: code/65-configuration/shelf/.env -->

`SHELF_ENV` is `development` or `production`. Chapter 66 uses it to decide how much an error page shows.

`shelf/.gitignore` lists `.env.local`:

<!-- include: code/65-configuration/shelf/.gitignore -->

**The reader,** `readEnvFile()`, at the end of `shelf/src/functions.php`:

<!-- include: code/65-configuration/shelf/src/functions.php -->

**The settings,** `shelf/src/Config.php`:

<!-- include: code/65-configuration/shelf/src/Config.php -->

- `load()` puts the three layers together. Of the real environment variables, it takes only those whose names start with `SHELF_`.
- `fromSettings()` checks each one: a missing one, or a `SHELF_ENV` that isn't one of the two, stops *Shelf* with the setting's name.
- A relative path is taken from *Shelf*'s folder, so `var/shelf.sqlite` means the same wherever you start *Shelf* from.

**The tests** write `.env` files into a temporary folder, and set an environment variable with `putenv()`, which they remove again afterwards. `shelf/tests/ConfigTest.php`:

<!-- include: code/65-configuration/shelf/tests/ConfigTest.php -->

**Using them.** The front controller, `shelf/public/index.php`:

<!-- include: code/65-configuration/shelf/public/index.php -->

The command line, `shelf/shelf.php`:

<!-- include: code/65-configuration/shelf/shelf.php -->

To keep a second database on your computer, for trying things out, create `shelf/.env.local` with one line, `SHELF_DATABASE=var/try.sqlite`. Delete the file to go back.

## Exercises

The exercises are in `exercises/65-configuration/`. Check your work with:

```
composer exercise 65 beginner
```

1. **Beginner, `mail.php`:** take a password out of the code, and read it from the environment.
2. **Intermediate, `envfile.php`:** read a `.env` file, with comments, quotes, and the line number of a mistake.
3. **Advanced, `config.php`:** check every setting at the start, and name every problem in one message.

## Summary

- Settings that differ between computers, and every secret, stay out of the code.
- Read them from environment variables with `getenv()`; a `.env` file sets them on a developer's computer.
- `.env` is in Git and holds no secrets; `.env.local` is not in Git; the real environment wins over both.
- Check every setting when the program starts, and stop with its name.
- A secret that reached Git is known: change it.

## Further reading

- PHP manual, `getenv`: https://www.php.net/manual/en/function.getenv.php
- The Twelve-Factor App, config: https://12factor.net/config
- Symfony documentation, configuring with environment variables: https://symfony.com/doc/current/configuration.html#configuration-based-on-environment-variables
- OWASP, Secrets Management Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Secrets_Management_Cheat_Sheet.html
