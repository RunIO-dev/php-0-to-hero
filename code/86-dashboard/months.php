<?php

declare(strict_types=1);

// The loans of each month (chapter 86). GROUP BY the month, "2026-02", the first seven characters of
// the date. A month without loans has no rows, so it has no group, and the database can't count it:
// PHP walks the months, and puts 0 where the answer has none.

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec("CREATE TABLE loans (id INTEGER PRIMARY KEY, title TEXT, lent_on TEXT);
    INSERT INTO loans (title, lent_on) VALUES
        ('Kindred', '2026-01-05'), ('Sula', '2026-01-19'), ('Kindred', '2026-03-02'), ('Beloved', '2026-03-10')");

$counts = $db->query('SELECT SUBSTR(lent_on, 1, 7) AS month, COUNT(*) FROM loans GROUP BY month')
    ->fetchAll(PDO::FETCH_KEY_PAIR);
echo 'From the database: ', json_encode($counts), "\n";

for ($month = new DateTimeImmutable('2026-01-01'); $month <= new DateTimeImmutable('2026-04-01'); $month = $month->modify('+1 month')) {
    $loans = $counts[$month->format('Y-m')] ?? 0;
    printf("%s %-4s %d\n", $month->format('M Y'), str_repeat('#', $loans), $loans);
}
