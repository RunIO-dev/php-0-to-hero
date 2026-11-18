<?php

declare(strict_types=1);

// Two copies of Kindred come back at the same moment, at two desks (chapter 84); Amira and Ben wait,
// in that order. Each desk finds the first in the queue, then sets the copy aside for them.
// Read, then write: both desks find Amira, and Ben gets nothing. The fix: change the row only if it
// is still waiting, and see whether a row changed.

$file = tempnam(sys_get_temp_dir(), 'shelf');
$connect = fn(): PDO => new PDO("sqlite:$file", options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$desk1 = $connect();
$desk2 = $connect();
$reset = function () use ($desk1): void {
    $desk1->exec("DROP TABLE IF EXISTS holds; CREATE TABLE holds (id INTEGER PRIMARY KEY, member TEXT, status TEXT);
        INSERT INTO holds VALUES (1, 'Amira', 'waiting'), (2, 'Ben', 'waiting')");
};
$first = fn(PDO $db): int => (int) $db->query("SELECT MIN(id) FROM holds WHERE status = 'waiting'")->fetchColumn();
$ready = fn(PDO $db): string => implode(', ', $db->query("SELECT member FROM holds WHERE status = 'ready' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN));

// ✗ Both desks read the queue before either writes.
$reset();
$found1 = $first($desk1);
$found2 = $first($desk2);
$desk1->prepare("UPDATE holds SET status = 'ready' WHERE id = ?")->execute([$found1]);
$desk2->prepare("UPDATE holds SET status = 'ready' WHERE id = ?")->execute([$found2]);
echo 'read, then write: two copies, ready for ', $ready($desk1), "\n";

// ✓ The same, but each UPDATE checks that the row is still waiting. 0 rows: someone was quicker, try the next.
$reset();
$claim = function (PDO $db, int $id): bool {
    $update = $db->prepare("UPDATE holds SET status = 'ready' WHERE id = ? AND status = 'waiting'");
    $update->execute([$id]);

    return $update->rowCount() === 1;
};
$found1 = $first($desk1);
$found2 = $first($desk2);
$claim($desk1, $found1);
if (!$claim($desk2, $found2)) {
    $claim($desk2, $first($desk2));
}
echo 'AND status = waiting: two copies, ready for ', $ready($desk1), "\n";

unset($desk1, $desk2);
unlink($file);
