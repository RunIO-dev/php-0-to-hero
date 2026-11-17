<?php

declare(strict_types=1);

// Lending in a database, in one transaction: what your tests in LendTest.php test.

/** The tables lend() needs. A test runs them in a new database, then adds books and members. */
const LIBRARY_TABLES = '
    CREATE TABLE books (id INTEGER PRIMARY KEY, title TEXT NOT NULL UNIQUE, copies INTEGER NOT NULL);
    CREATE TABLE members (id INTEGER PRIMARY KEY, name TEXT NOT NULL UNIQUE);
    CREATE TABLE loans (id INTEGER PRIMARY KEY, book_id INTEGER NOT NULL, member_id INTEGER NOT NULL, due TEXT NOT NULL);
';

const MOST_LOANS = 3;

/**
 * Stores a loan and takes one copy of the book, together, and returns the loan's id.
 * DomainException, with nothing changed: no copy left, or the member has MOST_LOANS loans.
 * OutOfBoundsException, with nothing changed: no such book or member.
 */
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
