# 83. The lending desk

## Goals

- You will be able to build pages that do a library's daily work: lend, take back and extend, in the browser.
- You will be able to keep what happened as history, instead of deleting it.
- You will be able to change a count in the database without losing an update when two requests come at once.
- You will be able to let people act only on their own things, and see a 404 for anyone else's.
- You will be able to let staff manage members, without locking themselves out.

## Before you start

- Chapter 57: transactions.
- Chapter 63: roles; chapter 76: Symfony's security and `#[IsGranted]`.
- Chapter 80: components and Twig filters.
- Chapter 82: members with accounts.

## The idea

Until now, a book was lent with `bin/console shelf:lend`, and never came back: nothing in *Shelf* could return it. A library's staff spend the day at a desk, not in a terminal. This chapter gives them one: a page to lend a book to a member, the list of every book that is out, the soonest due first, and a button for each loan to take it back or extend it. A second page lists the members, where the staff can block someone or make them staff. Readers get "Extend" on their own loans.

**Rules live in the model.** A loan may be extended twice, each time by its member's loan period, and never once it is overdue. A reader may have five books at once, staff ten. A blocked member can't borrow. All of this is in `Loan`, `Role` and `Library`, and the pages only call them. The same rules then hold for the desk, for "My loans", for the console and for the API.

**History, not deletion.** A returned book could simply delete its loan. But then nobody could say later which books are popular, who brings books back late, or what happened to a copy. So a loan that comes back gets the day in `returned_on`, and stays. "Out" now means "without `returned_on`". Chapter 86's dashboard reads that history. History is deleted only when what it is about goes: a member who deletes their account (chapter 82) takes their past loans with them, and a book removed for good takes its own.

**Counting in the database.** Two members bring back Kindred at the same moment, at two desks. Each request reads "0 copies", adds one, and writes "1". The second write undoes the first, and a copy is lost from the count: a **lost update**. `UPDATE books SET copies = copies + 1` has the database do the counting, one statement after the other, as lending has done since chapter 57 with `copies - 1 ... AND copies > 0`.

**Only your own.** "Extend" sends a loan's number in a hidden field. A hidden field is input like any other: a reader can change it, and so ask to extend someone else's loan. This mistake is called an **insecure direct object reference**. The server checks that the loan is the reader's own, and answers 404 when it isn't, so the number doesn't even confirm that such a loan exists. Staff may extend any loan, but only from the desk.

**Not yourself.** Staff can make another member staff, make them a reader again, or block them. Nobody can do that to themselves. Otherwise the last member of staff could make themselves a reader by accident, and nobody could run the desk any more.

## In code

### A lost update

<!-- include: code/83-the-lending-desk/lost.php -->

<!-- include: code/83-the-lending-desk/lost.out -->

- Each connection is like one request. Both read 0 before either writes, so two returns make 1.
- `copies = copies + 1` reads and writes in one statement, which the database runs one after the other.

### History

<!-- include: code/83-the-lending-desk/history.php -->

<!-- include: code/83-the-lending-desk/history.out -->

- `AND returned_on IS NULL` in the `UPDATE` makes a second return change nothing: `rowCount()` says 0.
- The history answers questions nobody had to plan for: which book is borrowed most, how many came back late.

## Common mistakes

### Someone else's loan

<!-- include: code/83-the-lending-desk/others.bad.php -->

✗ Any member extends any loan by sending its number.

**Fix:** find the loan, check that it belongs to the member who asks, and answer 404 if it doesn't, as `DeskController::renew()` does.

### Deleting what is over

`DELETE FROM loans WHERE id = ?` when a book comes back: the desk works, and every question about the past has no answer.

**Fix:** mark it: `returned_on`, and `WHERE returned_on IS NULL` wherever "out" is meant.

### Read, then write

`$copies = $book->copies; $book->copies = $copies + 1;` in two requests at once loses one.

**Fix:** one `UPDATE ... SET copies = copies + 1`. Or, where the new value needs PHP, a lock (Further reading).

## Principle check

**DRY:** the rules are in `Loan` and `Library` once. The desk, "My loans", the console and the API use them. **KISS:** two pages, plain forms, and `<datalist>` for suggestions while typing, without JavaScript. **Secure by default:** every page of the desk asks for `ROLE_STAFF`; a reader's "Extend" checks whose loan it is; nobody changes their own role.

## Security

- **404, not 403, for other people's things.** A 403 says "this exists, but not for you". For a number someone made up, a 404 says nothing.
- **Blocking keeps the account.** A blocked member can still log in, see their loans, and bring books back. They only can't borrow. Deleting an account is the member's decision (chapter 82), not the desk's.
- **History is personal data.** What someone borrowed says a lot about them. *Shelf* shows it only to staff, and deletes it with the account. A real library would also delete history after some years (Further reading).

## Shelf

### Loans that come back

`Loan` gets `renewals` and `returnedOn`, and the rules to extend and return, `shelf/src/Loan.php`:

<!-- include: code/83-the-lending-desk/shelf/src/Loan.php -->

`Role` says how many books each role may have, `shelf/src/Role.php`:

<!-- include: code/83-the-lending-desk/shelf/src/Role.php -->

A member can be blocked, and change role, `shelf/src/Member.php`:

<!-- include: code/83-the-lending-desk/shelf/src/Member.php -->

The repository finds a loan, lists the ones that are out, takes one back, and forgets the history of a member or a book, `shelf/src/LoanRepository.php`:

<!-- include: code/83-the-lending-desk/shelf/src/LoanRepository.php -->

`shelf/src/Doctrine/DoctrineLoanRepository.php`:

<!-- include: code/83-the-lending-desk/shelf/src/Doctrine/DoctrineLoanRepository.php lines 53-106 -->

- `giveBack()` adds the copy in SQL, inside the transaction, like `add()` takes it.
- `forget()` and `forgetBook()` use `remove()` for each loan. A `DELETE` query would leave the loans Doctrine has already loaded in its memory.

The migration adds the columns in place, as in chapter 82, `shelf/migrations/Version20261118120000.php`:

<!-- include: code/83-the-lending-desk/shelf/migrations/Version20261118120000.php -->

### The library's rules

`shelf/src/Library.php`:

<!-- include: code/83-the-lending-desk/shelf/src/Library.php lines 90-156 -->

### The desk

`shelf/src/Controller/DeskController.php`:

<!-- include: code/83-the-lending-desk/shelf/src/Controller/DeskController.php lines 31-99 -->

`shelf/templates/desk.html.twig`:

<!-- include: code/83-the-lending-desk/shelf/templates/desk.html.twig -->

- `<datalist>` suggests the titles on the shelf and the members' names as the staff type, in every browser, without a script. It only suggests: whatever is typed still goes through `Library`'s checks.
- The buttons have an `aria-label` that names the book and the member, so a screen reader doesn't read "Back, Back, Back".

The members, `shelf/templates/members.html.twig`, and "Extend" on `loans.html.twig` are in the chapter's snapshot. The menu gets "Desk" and "Members" for the staff, and a book's page links to the desk with its title filled in.

### Tests

The repository contract, for both repositories, `shelf/tests/LoanRepositoryContract.php`:

<!-- include: code/83-the-lending-desk/shelf/tests/LoanRepositoryContract.php -->

The desk in the browser, `shelf/tests/Web/DeskTest.php`:

<!-- include: code/83-the-lending-desk/shelf/tests/Web/DeskTest.php lines 17-66, 80-99 -->

`LoanTest` and `LibraryTest` test the rules without a browser, and `AccessibilityTest` checks both new pages.

## Exercises

The exercises are in `exercises/83-the-lending-desk/`. Check your work with:

```
composer exercise 83 beginner
```

1. **Beginner, `renewal.php`:** whether a loan may be extended, and if not, why.
2. **Intermediate, `Fine.php`:** the fine for a late book, counted in days and cents, and shown in euros.
3. **Advanced, `Desk.php`:** a lending desk on a database, with transactions, a limit per member, and no lost update.

## Summary

- The staff lend, take back and extend at a desk in the browser; readers extend their own loans.
- A returned loan keeps its day in `returned_on`: history instead of deletion.
- Counts change in one `UPDATE` (`copies = copies + 1`), so two requests at once can't lose one.
- A reader acts only on their own loans; anyone else's is a 404.
- Staff block members and change roles, but never their own.

## Further reading

- OWASP, insecure direct object references: https://cheatsheetseries.owasp.org/cheatsheets/Insecure_Direct_Object_Reference_Prevention_Cheat_Sheet.html
- MDN Web Docs, `<datalist>`: https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/datalist
- Doctrine ORM documentation, transactions and concurrency (locking): https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/transactions-and-concurrency.html
- European Data Protection Board, the principle of storage limitation: https://www.edpb.europa.eu/sme-data-protection-guide/data-protection-basics_en
