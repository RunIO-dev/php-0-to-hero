<?php

declare(strict_types=1);

// Exercise 57, advanced: lending, with the library's two rules, safe when requests come at once.
//
// lend($db, $title, $member, $due) stores a loan and takes one copy of the book, in one
// transaction, and returns the new loan's id. It refuses with a DomainException, and changes
// nothing, when:
//   - no copy of the book is left ("All copies of "Kindred" are on loan");
//   - the member has 3 loans already ("Amira has 3 books already").
// An unknown title or member: OutOfBoundsException, also without changes.
//
// Take the copy with one UPDATE that checks and takes (copies > 0), and look at rowCount():
// reading copies first and writing later lets two requests take the last copy.

function lend(PDO $db, string $title, string $member, string $due): int
{
    $db->prepare('UPDATE books SET copies = copies - 1 WHERE title = :title')->execute(['title' => $title]);
    $db->prepare(
        'INSERT INTO loans (book_id, member_id, due)
         VALUES ((SELECT id FROM books WHERE title = :title), (SELECT id FROM members WHERE name = :member), :due)',
    )->execute(['title' => $title, 'member' => $member, 'due' => $due]);

    return (int) $db->lastInsertId();
}
// Check your work: composer exercise 57 advanced
