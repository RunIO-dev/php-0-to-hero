# 88. Launch

## Goals

- You will be able to make a backup of a database that is in use, check it, keep the newest few, and restore one.
- You will be able to give an app a health check that a monitor can ask, and that tells nobody more than "ok" or "failing".
- You will be able to show a page of your own for every error: one the app catches, one it doesn't, and one where PHP itself doesn't answer.
- You will be able to run an app as a public demo that resets every night, and close what a demo must not do.
- You will be able to go through a launch checklist, with the part a program can check written as a command.

## Before you start

- Chapter 57: transactions; chapter 66: logging.
- Chapter 78: the production setup, with Caddy; chapter 79: deployment.
- Chapter 85: the worker, Messenger and Scheduler.

## The idea

*Shelf* runs on a server since chapter 79. Running and being ready for people are not the same. Before you tell anyone the address, ask what happens on the bad day: the disk breaks, the worker stops at night, a deployment goes wrong, a visitor finds a bug. Each needs an answer before it happens.

**A backup is a copy you can restore.** The data is the one part of *Shelf* that no repository holds: the books, the members, the loans. A copy made with `copy()` while the app writes can be half of one write and half of the next, or, in SQLite's WAL mode, miss everything written lately. SQLite's **backup API** copies the database as one committed state, while others go on writing. A backup is only finished when you have checked it (`PRAGMA integrity_check`), and it is only trusted once you have restored one. Keep a few, the newest, and copy them to another computer: a backup on the same disk helps after a mistake, not after the disk is gone.

**A health check answers a monitor.** When the worker stops, no reminder goes out, and no page shows an error. Someone has to ask, every few minutes: a **monitor**, a program on another computer that requests an address and warns you when the answer isn't 200. `/api/health` asks *Shelf* three questions: does the database answer, did the worker run lately, is there a recent backup. The answer is `ok` or `failing` for each, and nothing else: no versions, no paths, no error messages. Anyone can ask, an attacker too.

**The worker signs its own life.** The worker has no address to ask. So it leaves a sign: every five minutes, the schedule sends it a `Heartbeat` message, and it writes the time into a file. The health check reads the file. More than fifteen minutes old, and the worker counts as stopped.

**Every error gets a page.** *Shelf* has its pages for 404, 403, 400 and 429. An exception that nothing caught (a bug) gets a page with a reference, which finds the details in the log. And when PHP itself doesn't answer, during a deployment or after a crash, the web server answers alone: it needs a page that needs nothing, a plain HTML file.

**A demo resets.** A library app is hard to show without logging in, and nobody signs up to try. So *Shelf* gets a demo mode: the login page names two shared accounts, a librarian and a reader, with their password, and every night the library goes back to the same books, members, loans and reservations. A demo invites strangers, so it closes what strangers could misuse: nothing that sends emails (sign-up, a new password), nothing that changes the shared accounts, and no uploaded pictures.

**A checklist, partly a program.** Pilots use a checklist for what they know by heart, because on the bad day memory fails. A launch checklist is the same, and what a program can check, a command should: `shelf:launch` checks the settings on the server, and fails while any answer is no.

## In code

### A backup while the app writes

<!-- include: code/88-launch/backup.php -->

<!-- include: code/88-launch/backup.out -->

- `BEGIN` starts a change that isn't committed: a third book, Sula. The backup copies the two committed books: a state that really existed, never half of a change.
- `backup()` copies page by page, from one connection to another. The source is opened read-only: a backup must not change what it copies.
- `PRAGMA integrity_check` reads the whole copy and answers `ok`, or names what is broken.

### A health check

<!-- include: code/88-launch/health.php -->

<!-- include: code/88-launch/health.out -->

- Each check is a closure: `$checks` says what is asked, the loop asks it. A new check is a new line.
- A check that throws is a no. Its message goes to the log, where the person who fixes it reads it; the answer says only `failing`.
- 503, *Service Unavailable*, tells a monitor that something is wrong without reading the body.

## Common mistakes

### A backup made with copy()

<!-- include: code/88-launch/copy.bad.php -->

```
Books in the copy:
Warning: SQLite3::querySingle(): Unable to prepare statement: no such table: books
```

✗ In WAL mode, the table and both books are still in `shelf.sqlite-wal`. The copy of `shelf.sqlite` alone has none of them. Without WAL, the copy can catch a write halfway, and is broken in a way you find only when you need it.

**Fix:** SQLite's backup API (`SQLite3::backup()`), or `sqlite3 shelf.sqlite ".backup copy.sqlite"` on the command line; then `PRAGMA integrity_check`.

### A health check that tells too much

```json
{"status": "failing", "error": "SQLSTATE[HY000] [14] unable to open database file /srv/shelf/data/shelf.sqlite"}
```

✗ The address is public. This answer names the database, its path, and that it is SQLite: a map for an attacker, and no help for the monitor, which only needs to know that something failed.

**Fix:** `ok` or `failing` per check, and the reason in the log.

### An error page that needs what failed

The 500 page extends `base.html.twig`, which asks for the logged-in member, the session and the translator. If the error is in one of them, rendering the error page throws again, and the visitor gets PHP's empty page.

**Fix:** try the template, and fall back to plain HTML without templates, `ErrorPages::failed()` below.

## Principle check

**KISS:** the heartbeat is a file with a time in it, a backup is a folder with a database and the covers in it, and the health check asks three questions. **YAGNI:** no monitoring server of our own; any monitor that can request an address will do. **Secure by default:** the demo closes everything that would let a stranger send mail or lock other visitors out.

## Security

- **The demo is public on purpose.** Its password is on the login page and protects nothing but the demo. A demo must never hold real people's data, and `shelf:demo` refuses to run unless `SHELF_DEMO=1`: on a real library, it would delete everything.
- **What strangers write, everyone sees.** A visitor of the demo can add a book with any title until the next reset. The demo takes no covers, because a picture is worse than a title, and it sends no emails, so it can't be used to send mail to strangers.
- **Backups are the whole database.** They hold every member's name, email and password hash. The folder is for Shelf's user only (mode 700), and a copy on another computer needs the same care, best encrypted.
- **The health check says ok or failing.** Never an exception, a version or a path. It is cheap to answer: a count of books and two file times.
- **The down page needs nothing.** It is a static file: no PHP, no database, no stylesheet, so no failure can break it.

## Shelf

### Backups

`shelf/src/Backups.php`:

<!-- include: code/88-launch/shelf/src/Backups.php lines 23-88 -->

Each backup is a folder in `SHELF_DATA/backups/`, named by its time, with `shelf.sqlite` and a copy of the covers. `SHELF_DATA` is a new setting, `var` on your computer and `data` on a server (`shelf/compose.prod.yaml`), the folder that also holds the database. Try it:

```
php bin/console shelf:backup
```

**Restoring** is the step to practise before you need it. Stop what writes, put the copy back, start again. In Docker, with the backup's folder name:

```
docker compose -f compose.prod.yaml stop app worker
docker compose -f compose.prod.yaml run --rm --no-deps --entrypoint sh app -c \
  'b=data/backups/2026-11-18T023000; rm -f data/shelf.sqlite-wal data/shelf.sqlite-shm && cp $b/shelf.sqlite data/ && rm -rf data/covers && cp -r $b/covers data/'
docker compose -f compose.prod.yaml start app worker
```

The `-wal` and `-shm` files belong to the old database: kept, SQLite would read them into the restored one.

### The schedule

The worker makes a backup every night, says it is alive every five minutes, and, in a demo, resets it, `shelf/src/Schedule.php`:

<!-- include: code/88-launch/shelf/src/Schedule.php -->

Both times are UTC, given with their zone: `new DateTimeImmutable('03:00')` would be 03:00 in the server's time zone, and a server in Berlin would reset the demo an hour before the note on every page says. The backup runs at 02:30, before the demo's reset at 03:00: if a visitor finds something worth keeping during the day, it is in that night's backup. Each message has a small handler in `src/MessageHandler/` that calls `Backups`, `Heartbeat` or `Demo`.

### The health check

`shelf/src/Health.php`:

<!-- include: code/88-launch/shelf/src/Health.php -->

`/api/health` is part of the API, in English, at one address, and never cached (`Cache-Control: no-store`): a monitor must get today's answer. Without a worker, as in `web.out`:

```
GET /api/health

HTTP/1.1 503 Service Unavailable
{"status": "failing", "checks": {"database": "ok", "worker": "failing", "backup": "ok"}}
```

Point any monitor at `https://your-shelf/api/health`: an uptime service, or `curl -fsS` in a cron job on another computer, which mails you its output when it fails. The monitor must run elsewhere: a server that is down can't report that it is down.

### Error pages

An error that nothing caught now gets `shelf/templates/error.html.twig`, in the layout and the page's language, with its reference, and plain HTML if even that fails, in `shelf/src/EventSubscriber/ErrorPages.php` (in the snapshot):

```php
private function failed(string $reference): string
{
    try {
        return $this->twig->render('error.html.twig', ['reference' => $reference]);
    } catch (Throwable) {
        return "<!doctype html>\n<title>Error · Shelf</title>\n<h1>Something went wrong</h1>\n" ...
    }
}
```

When PHP doesn't answer, Caddy shows `shelf/public/down.html`, a page in all three languages that needs nothing else, `shelf/docker/Caddyfile`:

<!-- include: code/88-launch/shelf/docker/Caddyfile -->

`handle_errors 502 503 504` catches the answers Caddy makes itself when PHP-FPM is gone; the status stays, so a monitor still sees the problem.

### The demo

`shelf/src/Demo.php`:

<!-- include: code/88-launch/shelf/src/Demo.php lines 20-92 -->

The reset deletes the rows, children first because of the foreign keys, and loads `books.json`, `catalogue.json` and `members.json` again, in one transaction. Its loans are dated from the reset's day, so the desk always shows one overdue book, one due soon, and a reservation. `shelf/src/EventSubscriber/DemoGuard.php` (in the snapshot) answers the closed routes with `shelf/templates/demo.html.twig` and 403: sign-up, the confirmation and password links, and the account's password, email and deletion. On the members page, Jonas and Amira can't be blocked or given another role. The login page shows the accounts, and every page a note:

```twig
{% if demo %}
    <p class="notice">{{ 'A demo: try anything. Every night at 03:00 UTC, it all goes back as it was.'|trans }}</p>
{% endif %}
```

`demo` is a Twig global, `SHELF_DEMO` as a boolean (`shelf/config/packages/twig.yaml`). `shelf/demo.http` asks the demo's pages, as `web.http` asks the others, and its answers are in `demo.out`: the login page with the accounts, 403 for sign-up and for blocking Amira, a desk with one book overdue.

To make a server a demo, set `SHELF_DEMO=1` in its `.env`, deploy, and reset it once, so the shared accounts get their password:

```
docker compose -f compose.prod.yaml exec app php bin/console shelf:demo
```

### The launch checklist

Before you give anyone the address:

1. **`shelf:launch` passes on the server** (below).
2. **HTTPS works**, and `http://` leads to `https://` (Caddy, chapter 78).
3. **A monitor asks `/api/health`** from another computer, and you got its warning once: stop the worker for twenty minutes and see.
4. **Backups leave the server**: a copy of `data/backups/` on another computer every night, and **one restore tried**.
5. **Every error has its page:** try an address that doesn't exist, and stop `app` to see `down.html`.
6. **You know where the logs are:** `docker compose -f compose.prod.yaml logs app worker`.
7. **The staff have their own strong passwords**, set on the server; never the sample passwords of this repository.
8. **Updates have a day:** `composer audit` and new images (CI builds them), at least every month.

The first one is a command, `shelf/src/Command/LaunchCommand.php` (in the snapshot). On a server that isn't ready yet:

```
$ php bin/console shelf:launch
 ✓ APP_ENV is prod
 ✓ Debugging is off
 ✓ The secret is long and random
 ✓ The address is https://
 ✓ Email is sent
 ✓ The database has every migration
 ✓ A member of staff can log in
 ✓ The database answers
 ✗ The worker ran in the last 15 minutes. Start it, and keep it running: php bin/console messenger:consume async scheduler_shelf
 ✗ A backup is less than 26 hours old. Run php bin/console shelf:backup once; then the worker makes one every night.

Not ready: 2 of 10 checks failed.
```

It fails with status 1, so a deployment script can stop on it, as on a failing test.

### Tests

`BackupsTest` makes backups and checks that the newest seven stay, and that a folder that isn't a backup does too. `HealthTest` asks `/api/health` with a heartbeat that is fresh and one that is late. `DemoTest` sets `SHELF_DEMO=1` before the kernel starts, and checks the login page, the closed routes, the shared accounts and the reset, `shelf/tests/Web/DemoTest.php`:

<!-- include: code/88-launch/shelf/tests/Web/DemoTest.php lines 24-79, 97-111 -->

`ErrorPagesTest` answers an exception as a server does, with debug off: the German page with its reference, and plain HTML when the templates fail. `CommandsTest` runs `shelf:launch` with the tests' settings, where most answers are no.

## Exercises

The exercises are in `exercises/88-launch/`. Check your work with:

```
composer exercise 88 beginner
```

1. **Beginner, `keep.php`:** which backups to delete, by their names, keeping the newest.
2. **Intermediate, `health.php`:** the status and the JSON of a health check, from its checks.
3. **Advanced, `NightlyReset.php`:** when the demo resets next, in words, in any time zone, on the night the clocks change too.

## Summary

- Back up with SQLite's backup API, check the copy, keep the newest few, copy them away, and try a restore.
- A health check answers a monitor on another computer, with `ok` or `failing` and nothing more.
- The worker proves it runs with a heartbeat; a late one is a failing check.
- Every error has a page, down to a static one for when PHP doesn't answer.
- A demo resets every night and closes what strangers could misuse; a checklist, partly a command, comes before the launch.

## Further reading

- SQLite, the online backup API: https://www.sqlite.org/backup.html
- SQLite, write-ahead logging: https://www.sqlite.org/wal.html
- PHP manual, SQLite3::backup: https://www.php.net/manual/en/sqlite3.backup.php
- Symfony documentation, Scheduler: https://symfony.com/doc/current/scheduler.html
- Caddy documentation, handle_errors: https://caddyserver.com/docs/caddyfile/directives/handle_errors
