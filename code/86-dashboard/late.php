<?php

declare(strict_types=1);

// How many came back late (chapter 86): one query counts all and some. CASE turns each row into 1 or
// 0, and SUM adds them up: a count with a condition, next to the plain COUNT.

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec("CREATE TABLE loans (id INTEGER PRIMARY KEY, title TEXT, due TEXT, returned_on TEXT);
    INSERT INTO loans (title, due, returned_on) VALUES
        ('Kindred', '2026-01-19', '2026-01-15'), ('Kindred', '2026-02-16', '2026-02-18'),
        ('Sula', '2026-03-06', '2026-03-06'), ('Beloved', '2026-03-24', NULL)");

$row = $db->query('SELECT COUNT(*) AS returned,
        SUM(CASE WHEN returned_on > due THEN 1 ELSE 0 END) AS late
    FROM loans WHERE returned_on IS NOT NULL')->fetch(PDO::FETCH_ASSOC);

printf("%d came back, %d late: %d%% on time\n", $row['returned'], $row['late'], round(100 * ($row['returned'] - $row['late']) / $row['returned']));
