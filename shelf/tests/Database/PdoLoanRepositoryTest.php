<?php

declare(strict_types=1);

namespace Shelf\Tests\Database;

use DateTimeImmutable;
use PDO;
use PDOException;
use Shelf\Book;
use Shelf\Loan;
use Shelf\Member;
use Shelf\PdoBookRepository;
use Shelf\PdoLoanRepository;
use Shelf\PdoMemberRepository;
use Shelf\Role;
use Shelf\Tests\LoanRepositoryContract;

use function Shelf\openDatabase;

final class PdoLoanRepositoryTest extends LoanRepositoryContract
{
    private PDO $db;

    protected function repositories(Book ...$books): array
    {
        $this->db = openDatabase(':memory:');
        $bookRepository = new PdoBookRepository($this->db);
        foreach ($books as $book) {
            $bookRepository->add($book);
        }
        $members = new PdoMemberRepository($this->db);
        $members->add($this->amira);
        $members->add($this->jonas);

        return [$bookRepository, new PdoLoanRepository($this->db)];
    }

    public function testALoanThatFailsTakesNoCopy(): void
    {
        $sula = new Book('Sula', 'Toni Morrison', 1973, 2);
        [$books, $loans] = $this->repositories($sula);

        try {
            $loans->add(new Loan($sula, new Member('Nobody', Role::Reader), new DateTimeImmutable('2026-03-16')));
            self::fail('A loan for someone who is not a member must fail');
        } catch (PDOException $e) {
            self::assertStringContainsString('NOT NULL constraint failed: loans.member_id', $e->getMessage());
        }
        self::assertSame(2, $books->find('Sula')?->copies, 'The transaction must give the copy back');
        self::assertFalse($this->db->inTransaction());
    }
}
