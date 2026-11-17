<?php

declare(strict_types=1);

// Exercise 57, advanced: lending, with the library's two rules, safe when requests come at once.

const MOST_LOANS = 3;

function lend(PDO $db, string $title, string $member, string $due): int
{
    $db->beginTransaction();
    try {
        $memberId = idOf($db, 'SELECT id FROM members WHERE name = :value', $member);
        $bookId = idOf($db, 'SELECT id FROM books WHERE title = :value', $title);
        $count = $db->prepare('SELECT COUNT(*) FROM loans WHERE member_id = :member');
        $count->execute(['member' => $memberId]);
        if ($count->fetchColumn() >= MOST_LOANS) {
            throw new DomainException("$member has " . MOST_LOANS . ' books already');
        }
        $take = $db->prepare('UPDATE books SET copies = copies - 1 WHERE id = :book AND copies > 0');
        $take->execute(['book' => $bookId]);
        if ($take->rowCount() === 0) {
            throw new DomainException("All copies of \"$title\" are on loan");
        }
        $lend = $db->prepare('INSERT INTO loans (book_id, member_id, due) VALUES (:book, :member, :due)');
        $lend->execute(['book' => $bookId, 'member' => $memberId, 'due' => $due]);
        $id = (int) $db->lastInsertId();
        $db->commit();

        return $id;
    } catch (Throwable $e) {
        $db->rollBack();

        throw $e;
    }
}

/** The id that a query with one :value finds, or an OutOfBoundsException. */
function idOf(PDO $db, string $sql, string $value): int
{
    $find = $db->prepare($sql);
    $find->execute(['value' => $value]);
    $id = $find->fetchColumn();
    if (!is_int($id)) {
        throw new OutOfBoundsException("Not found: $value");
    }

    return $id;
}
