# 85. Reminders

## Goals

- You will be able to do slow work outside the request: a page puts it in a queue, and a worker does it.
- You will be able to send a message with Symfony Messenger, and handle it in a worker, with retries and a place for what keeps failing.
- You will be able to run work on a timetable with Symfony Scheduler: every hour, every night.
- You will be able to make a job safe to run twice, by claiming before acting.
- You will be able to keep a worker running on a server, and restart it after each deployment.

## Before you start

- Chapter 82: emails with Symfony Mailer, and Mailpit.
- Chapter 83: `copies = copies + 1`; chapter 84: claiming a row with an `UPDATE` that checks its state.
- Chapter 78: Shelf in production, in containers.

## The idea

*Shelf* lends books for fourteen days, and nobody reminds anyone. This chapter sends three emails: a loan is **due soon**, a loan is **overdue** (and again every week), and a reserved copy is **ready to pick up**. Nobody clicks anything to send them: they go out by themselves, at the right time.

**A queue and a worker.** Until now, everything *Shelf* did happened inside a request: someone clicks, PHP runs, the page answers. An email sent that way makes the page wait for the mail server, and fails with it. So the page only puts a **message** into a **queue**, a table in the database, and answers at once. A **worker**, a PHP process that runs all the time, takes the messages one by one and does each. If one fails, the worker tries it again later; after three tries, it goes to a separate place, `failed`, for a person to look at. Symfony **Messenger** is that queue and that worker. Every email of *Shelf* now goes through it, chapter 82's too.

**A timetable.** The reminders aren't caused by a click. They are due because time has passed. Symfony **Scheduler** gives the worker a second source of messages, a timetable: "send the reminders" every hour, "pass on the copies nobody collected" (chapter 84) every night. The worker handles them like any other message.

**Safe to run twice.** A worker can crash after sending an email and before noting it. Then Messenger runs the message again. Two workers can also run the same hourly job at once. Both find "Amira's loan is due soon", and Amira gets two emails. The fix is the one from chapters 83 and 84: **claim first, then act**. One `UPDATE ... WHERE reminded_on IS NULL` notes the reminder; only the run whose `UPDATE` changed a row sends the email. This makes the job **idempotent**: running it again changes nothing. If the email fails after the claim, it is queued anyway, and the queue retries the sending.

**The rule in the model.** When a loan needs which reminder is `Loan::reminderDue()`. It looks only at dates: due soon from two days before, overdue from the day after, then every seven days. A loan that was extended gets a new due date, and so a new "due soon".

## In code

### A queue and its worker, by hand

<!-- include: code/85-reminders/queue.php -->

<!-- include: code/85-reminders/queue.out -->

- A message is a row. The worker takes the oldest one that is available, does it, and deletes it.
- Ben's email fails: the row stays, with one more try, and is available again a minute later. Each try waits twice as long.
- Messenger's table, `messenger_messages`, has the same columns: the message, its queue, when it is available, and when a worker took it.

### Twice

<!-- include: code/85-reminders/twice.php -->

<!-- include: code/85-reminders/twice.out -->

- Both runs read "not reminded" before either notes it, as two workers can.
- `AND reminded_on IS NULL` lets one `UPDATE` change the row; `rowCount()` tells the other run to send nothing.

## Common mistakes

### The page sends the email

<!-- include: code/85-reminders/wait.bad.php -->

✗ The member waits for the mail server, and a failure loses the email.

**Fix:** route the message to a queue (`Symfony\Component\Mailer\Messenger\SendEmailMessage: async`), and let a worker send it.

### Send, then note it

A job that sends first and notes it afterwards sends twice whenever it runs twice.

**Fix:** claim first, with an `UPDATE` that checks the state, and send only if the claim changed a row.

### A worker nobody watches

A worker started by hand stops at the next reboot, or when it runs out of memory, and the queue fills up in silence.

**Fix:** let the system keep it running (Docker's `restart`, or a service manager such as supervisord), stop it after an hour (`--time-limit`) so it starts fresh, and stop it after each deployment (`messenger:stop-workers`), so the new code runs.

## Principle check

**DRY:** when a reminder is due is one method, `Loan::reminderDue()`; the hourly job and the tests both use it. **KISS:** the queue is a table in the database *Shelf* already has; no message broker to run. **Secure by default:** reminders go only to confirmed addresses (`Member::canBeMailed()`), and a message that keeps failing stops after three tries instead of retrying for ever.

## Security

- **Email only to confirmed addresses.** An address that was never confirmed may belong to someone else (chapter 82).
- **A queued message is data in the database.** `SendEmailMessage` holds the whole email, links included, until it is sent. It lives in the same database as everything else, with the same protection.
- **The worker runs with the site's settings and secrets.** It reads the same `.env.local`; on a server, its log goes where the site's does.

## Shelf

### Which reminder, when

The kinds of reminder, `shelf/src/Reminder.php`:

<!-- include: code/85-reminders/shelf/src/Reminder.php -->

`reminderDue()` and the day of the last reminder, `shelf/src/Loan.php`:

<!-- include: code/85-reminders/shelf/src/Loan.php -->

A reservation remembers when its member was told, `shelf/src/Hold.php` (in the snapshot): `notifiedOn`, `notify()` and `needsPickupMail()`. A member can be mailed once their address is confirmed, `Member::canBeMailed()`.

### Claiming a reminder

`markReminded()` sets the day only if it is still what the caller read, `shelf/src/Doctrine/DoctrineLoanRepository.php`:

<!-- include: code/85-reminders/shelf/src/Doctrine/DoctrineLoanRepository.php -->

`DoctrineHoldRepository::markNotified()` does the same for "ready to pick up". `Library` finds what is due and claims it, `shelf/src/Library.php`:

<!-- include: code/85-reminders/shelf/src/Library.php -->

### The messages and their handlers

A message says what to do, `shelf/src/Message/SendReminders.php`:

<!-- include: code/85-reminders/shelf/src/Message/SendReminders.php -->

Its handler, `shelf/src/MessageHandler/SendRemindersHandler.php`:

<!-- include: code/85-reminders/shelf/src/MessageHandler/SendRemindersHandler.php -->

`ExpireHoldsHandler` does chapter 84's daily job. The emails, `shelf/src/Web/ReminderMail.php`:

<!-- include: code/85-reminders/shelf/src/Web/ReminderMail.php -->

The worker has no request, so the links in its emails take the site's address from `SHELF_URL`, through `framework.router.default_uri` in `shelf/config/packages/framework.yaml`.

### The queue and the timetable

`shelf/config/packages/messenger.yaml`:

<!-- include: code/85-reminders/shelf/config/packages/messenger.yaml -->

`shelf/src/Schedule.php`:

<!-- include: code/85-reminders/shelf/src/Schedule.php -->

The migration creates the queue's table and the two columns, `shelf/migrations/Version20261120090000.php`:

<!-- include: code/85-reminders/shelf/migrations/Version20261120090000.php -->

### Running the worker

While you work, `compose.yaml` has a `worker` service: `docker compose up web worker`. Its emails arrive in Mailpit, at http://localhost:8025. To send the reminders that are due without waiting for the next hour:

```
docker compose run --rm php php shelf/bin/console shelf:reminders
```

In production, the worker is a third container from the same image, `shelf/compose.prod.yaml`:

<!-- include: code/85-reminders/shelf/compose.prod.yaml -->

### Tests

In the tests, the queue is handled at once (`sync://`), so each email is there when `dispatch()` returns. The reminders as the worker handles them, `shelf/tests/Messenger/RemindersTest.php`:

<!-- include: code/85-reminders/shelf/tests/Messenger/RemindersTest.php lines 25-96 -->

`LoanTest` tests the rule day by day, and both repository contracts test that a claim works once.

## Exercises

The exercises are in `exercises/85-reminders/`. Check your work with:

```
composer exercise 85 beginner
```

1. **Beginner, `reminder.php`:** which reminder a loan needs today.
2. **Intermediate, `Backoff.php`:** how long to wait after each failed try, and when to give up.
3. **Advanced, `Queue.php`:** a queue on a database for several workers, with claims, retries and failures.

## Summary

- A page queues slow work; a worker does it, retries what fails, and keeps what fails for good.
- Symfony Messenger is the queue and the worker; Symfony Scheduler adds a timetable.
- A job that may run twice claims first, with an `UPDATE` that checks the state, and acts only if the claim changed a row.
- A worker runs under something that restarts it, stops after an hour, and stops after each deployment.

## Further reading

- Symfony documentation, Messenger: https://symfony.com/doc/current/messenger.html
- Symfony documentation, Scheduler: https://symfony.com/doc/current/scheduler.html
- Symfony documentation, sending emails with Messenger: https://symfony.com/doc/current/mailer.html#sending-messages-async
- Supervisor documentation, configuration: https://supervisord.org/configuration.html
