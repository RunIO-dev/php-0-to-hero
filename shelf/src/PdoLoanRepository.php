<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use PDO;
use UnexpectedValueException;

/**
 * The loans in the database's loans table (chapter 56). A loan row holds the ids of its book and
 * its member; a JOIN brings their rows back with it, so one query makes whole Loan objects.
 * Chapter 57: lending changes two tables, in one transaction.
 */
final readonly class PdoLoanRepository implements LoanRepository
{
    public function __construct(private PDO $db) {}

    public function add(Loan $loan): void
    {
        inTransaction($this->db, function () use ($loan): void {
            // Only if a copy is left, checked and taken in one statement: another request can't come in between.
            $take = $this->db->prepare('UPDATE books SET copies = copies - 1 WHERE title = :title AND copies > 0');
            $take->execute(['title' => $loan->book->title]);
            if ($take->rowCount() === 0) {
                throw new NoCopyLeft("All copies of \"{$loan->book->title}\" are on loan");
            }
            $add = $this->db->prepare(
                'INSERT INTO loans (book_id, member_id, due) VALUES (
                    (SELECT id FROM books WHERE title = :title),
                    (SELECT id FROM members WHERE name = :name),
                    :due)',
            );
            $add->execute(['title' => $loan->book->title, 'name' => $loan->member->name, 'due' => $loan->due->format('Y-m-d')]);
        });
    }

    public function forMember(string $name): array
    {
        $find = $this->db->prepare(
            'SELECT books.title, books.author, books.year, books.copies, books.isbn,
                    members.name, members.role, loans.due
             FROM loans
             JOIN books ON books.id = loans.book_id
             JOIN members ON members.id = loans.member_id
             WHERE members.name = :name
             ORDER BY loans.due, books.title',
        );
        $find->execute(['name' => trim($name)]);

        return array_values(array_map(self::loan(...), $find->fetchAll()));
    }

    public function isOnLoan(string $title): bool
    {
        $count = $this->db->prepare('SELECT COUNT(*) FROM loans JOIN books ON books.id = loans.book_id WHERE books.title = :title');
        $count->execute(['title' => trim($title)]);

        return $count->fetchColumn() > 0;
    }

    private static function loan(mixed $row): Loan
    {
        if (!is_array($row) || !is_string($row['due'] ?? null)) {
            throw new UnexpectedValueException('A loan needs a due date: ' . json_encode($row));
        }
        $due = DateTimeImmutable::createFromFormat('!Y-m-d', $row['due']);
        if ($due === false) {
            throw new UnexpectedValueException("A loan's due date must look like 2026-03-16: {$row['due']}");
        }

        return new Loan(Book::fromArray($row), Member::fromArray($row), $due);
    }
}
