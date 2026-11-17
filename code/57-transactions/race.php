<?php

declare(strict_types=1);

// Two requests lend the last copy of Kindred at the same moment. Each has its own connection,
// as two visitors' requests would. The steps run in turns here, so you can follow them.

require_once __DIR__ . '/state.php';

$file = sys_get_temp_dir() . '/race-' . bin2hex(random_bytes(4)) . '.sqlite';
$setup = library("sqlite:$file");
$setup->exec((string) file_get_contents(__DIR__ . '/library.sql'));
[$first, $second] = [library("sqlite:$file"), library("sqlite:$file")];

echo "Read, then write:\n";
$copies = function (PDO $db): int {
    $result = $db->query("SELECT copies FROM books WHERE title = 'Kindred'");

    return $result === false ? 0 : (int) $result->fetchColumn();
};
$seenByFirst = $copies($first);
$seenBySecond = $copies($second); // both requests read 1, before either writes
foreach ([[$first, $seenByFirst, 'Amira'], [$second, $seenBySecond, 'Jonas']] as [$db, $seen, $member]) {
    if ($seen > 0) {
        $write = $db->prepare("UPDATE books SET copies = :copies WHERE title = 'Kindred'");
        $write->execute(['copies' => $seen - 1]);
        $lend = $db->prepare("INSERT INTO loans (book_id, member, due) VALUES (1, :member, '2026-03-16')");
        $lend->execute(['member' => $member]);
        echo "  lent to $member\n";
    }
}
echo '  ', state($setup);

echo "Check and take in one statement:\n";
$setup->exec("DELETE FROM loans; UPDATE books SET copies = 1 WHERE title = 'Kindred'");
foreach ([[$first, 'Amira'], [$second, 'Jonas']] as [$db, $member]) {
    $db->beginTransaction();
    $take = $db->prepare("UPDATE books SET copies = copies - 1 WHERE title = 'Kindred' AND copies > 0");
    $take->execute();
    if ($take->rowCount() === 0) {
        $db->rollBack();
        echo "  no copy left for $member\n";
        continue;
    }
    $lend = $db->prepare("INSERT INTO loans (book_id, member, due) VALUES (1, :member, '2026-03-16')");
    $lend->execute(['member' => $member]);
    $db->commit();
    echo "  lent to $member\n";
}
echo '  ', state($setup);
unlink($file);
