<?php

declare(strict_types=1);

// Counting in the database (chapter 86). GROUP BY makes one group of rows per book, and COUNT counts
// each group: only the answers come back, not every loan. HAVING keeps the groups whose count fits,
// as WHERE keeps rows. LEFT JOIN keeps the books without loans too, which COUNT(l.id) counts as 0.

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec("CREATE TABLE books (id INTEGER PRIMARY KEY, title TEXT);
    CREATE TABLE loans (id INTEGER PRIMARY KEY, book_id INTEGER, member TEXT, lent_on TEXT);
    INSERT INTO books VALUES (1, 'Kindred'), (2, 'Sula'), (3, 'Beloved');
    INSERT INTO loans (book_id, member, lent_on) VALUES
        (1, 'Amira', '2026-01-05'), (1, 'Ben', '2026-02-02'), (2, 'Ben', '2026-02-20'), (1, 'Amira', '2026-03-02')");

echo "Borrowed most:\n";
$most = $db->query('SELECT b.title, COUNT(*) AS loans
    FROM loans l JOIN books b ON b.id = l.book_id
    GROUP BY b.id, b.title
    ORDER BY loans DESC, b.title');
foreach ($most as $row) {
    echo "  {$row['title']}: {$row['loans']}\n";
}

echo "Borrowed more than once:\n";
$often = $db->query('SELECT b.title, COUNT(*) AS loans
    FROM loans l JOIN books b ON b.id = l.book_id
    GROUP BY b.id, b.title
    HAVING COUNT(*) > 1');
foreach ($often as $row) {
    echo "  {$row['title']}: {$row['loans']}\n";
}

echo "Every book, COUNT(*) and COUNT(l.id):\n";
$every = $db->query('SELECT b.title, COUNT(*) AS rows_, COUNT(l.id) AS loans
    FROM books b LEFT JOIN loans l ON l.book_id = b.id
    GROUP BY b.id, b.title
    ORDER BY b.title');
foreach ($every as $row) {
    echo "  {$row['title']}: COUNT(*) {$row['rows_']}, COUNT(l.id) {$row['loans']}\n";
}
