<?php

declare(strict_types=1);

// A queue in a table (chapter 84): first come, first served is the order of the row numbers. A
// reservation's place is how many waiting ones come before it, itself included; nothing to renumber
// when someone leaves the queue.

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec("CREATE TABLE holds (id INTEGER PRIMARY KEY AUTOINCREMENT, member TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'waiting')");
foreach (['Amira', 'Ben', 'Chen'] as $member) {
    $db->prepare('INSERT INTO holds (member) VALUES (?)')->execute([$member]);
}

$place = function (string $member) use ($db): int {
    $find = $db->prepare("SELECT COUNT(*) FROM holds WHERE status = 'waiting' AND id <= (SELECT id FROM holds WHERE member = ?)");
    $find->execute([$member]);

    return (int) $find->fetchColumn();
};
echo "Chen is number {$place('Chen')}\n";

$db->exec("UPDATE holds SET status = 'cancelled' WHERE member = 'Ben'"); // Ben doesn't want it any more
echo "Ben cancels: Chen is number {$place('Chen')}\n";

$first = $db->query("SELECT member FROM holds WHERE status = 'waiting' ORDER BY id LIMIT 1")->fetchColumn();
echo "A copy comes back: it goes to $first\n";
