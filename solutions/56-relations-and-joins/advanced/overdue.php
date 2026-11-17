<?php

declare(strict_types=1);

// Exercise 56, advanced: the reminder list. Which loans are overdue on a given day?

/** @return list<string> */
function overdue(PDO $db, string $today): array
{
    $day = DateTimeImmutable::createFromFormat('!Y-m-d', $today);
    if ($day === false || $day->format('Y-m-d') !== $today) {
        throw new InvalidArgumentException("Not a date like 2026-03-17: $today");
    }
    $late = $db->prepare(
        'SELECT books.title, members.name, loans.due
         FROM loans
         JOIN books ON books.id = loans.book_id
         JOIN members ON members.id = loans.member_id
         WHERE loans.due < :today
         ORDER BY loans.due',
    );
    $late->execute(['today' => $today]);
    $lines = [];
    foreach ($late->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $days = new DateTimeImmutable($row['due'])->diff($day)->days;
        $lines[] = "{$row['title']}: {$row['name']}, $days " . ($days === 1 ? 'day' : 'days') . ' late';
    }

    return $lines;
}
