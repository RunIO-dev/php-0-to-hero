<?php

declare(strict_types=1);

// The reminder job runs twice (chapter 85): a worker retries it after a crash, or two workers run at
// once. Both find Amira's loan due soon. Sending first sends twice. Claiming first, with one UPDATE that
// checks nobody has reminded her yet, sends once: the second run changes no row, and sends nothing.

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$reset = fn() => $db->exec("DROP TABLE IF EXISTS loans; DROP TABLE IF EXISTS outbox;
    CREATE TABLE loans (id INTEGER PRIMARY KEY, member TEXT, title TEXT, reminded_on TEXT);
    CREATE TABLE outbox (address TEXT, subject TEXT);
    INSERT INTO loans VALUES (1, 'amira@example.org', 'Kindred', NULL)");
$send = fn(array $loan) => $db->prepare('INSERT INTO outbox VALUES (?, ?)')->execute([$loan['member'], "\"{$loan['title']}\" is due back soon"]);
$due = fn(): array => $db->query('SELECT * FROM loans WHERE reminded_on IS NULL')->fetchAll(PDO::FETCH_ASSOC);
$sent = fn(): int => (int) $db->query('SELECT COUNT(*) FROM outbox')->fetchColumn();

// ✗ Send, then note it: both runs read "not reminded" before either notes it.
$reset();
[$first, $second] = [$due(), $due()];
foreach ([...$first, ...$second] as $loan) {
    $send($loan);
    $db->prepare("UPDATE loans SET reminded_on = '2026-03-14' WHERE id = ?")->execute([$loan['id']]);
}
echo 'send, then note:  ', $sent(), " emails\n";

// ✓ Claim, then send: only the run whose UPDATE changed the row sends.
$reset();
[$first, $second] = [$due(), $due()];
$claim = $db->prepare("UPDATE loans SET reminded_on = '2026-03-14' WHERE id = ? AND reminded_on IS NULL");
foreach ([...$first, ...$second] as $loan) {
    $claim->execute([$loan['id']]);
    if ($claim->rowCount() === 1) {
        $send($loan);
    }
}
echo 'claim, then send: ', $sent(), " email\n";
