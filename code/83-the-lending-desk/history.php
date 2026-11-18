<?php

declare(strict_types=1);

// A loan that comes back is marked, not deleted (chapter 83): returned_on holds the day. The loans
// that are out are the ones without it; the others are the library's history, which can answer
// questions later, such as which books are borrowed most.

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$db->exec('CREATE TABLE loans (id INTEGER PRIMARY KEY, title TEXT NOT NULL, member TEXT NOT NULL,
    due DATE NOT NULL, returned_on DATE DEFAULT NULL)');
$lend = $db->prepare('INSERT INTO loans (title, member, due) VALUES (?, ?, ?)');
foreach ([['Kindred', 'Amira', '2026-03-16'], ['Sula', 'Kenji', '2026-03-10'], ['Kindred', 'Kenji', '2026-04-01'],
    ['Beloved', 'Ana', '2026-03-20'], ['Kindred', 'Ana', '2026-04-20']] as $loan) {
    $lend->execute($loan);
}
$back = $db->prepare('UPDATE loans SET returned_on = :day WHERE id = :id AND returned_on IS NULL');
$back->execute(['day' => '2026-03-14', 'id' => 1]);
$back->execute(['day' => '2026-03-12', 'id' => 2]);
$back->execute(['day' => '2026-03-30', 'id' => 3]);
$back->execute(['day' => '2026-04-01', 'id' => 3]);
echo "A second return of loan 3 changed {$back->rowCount()} rows\n\n";

echo "Out now:\n";
foreach ($db->query('SELECT title, member, due FROM loans WHERE returned_on IS NULL ORDER BY due') as $loan) {
    echo "  {$loan['title']}, {$loan['member']}, due {$loan['due']}\n";
}
echo "\nBorrowed most:\n";
foreach ($db->query('SELECT title, COUNT(*) AS times FROM loans GROUP BY title ORDER BY times DESC, title') as $row) {
    echo "  {$row['title']}: {$row['times']}\n";
}
echo "\nBack late: ";
echo $db->query('SELECT COUNT(*) FROM loans WHERE returned_on > due')->fetchColumn(), "\n";
