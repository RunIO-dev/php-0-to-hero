<?php

declare(strict_types=1);

namespace Shelf\Tests\Database;

use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use Shelf\Book;
use Shelf\Doctrine\DoctrineBookRepository;
use Shelf\Doctrine\DoctrineLoanRepository;
use Shelf\Doctrine\DoctrineMemberRepository;
use Shelf\Loan;
use Shelf\Member;
use Shelf\Role;
use Shelf\Tests\LoanRepositoryContract;

/** The loans in a real SQLite database, through Doctrine (chapter 74). */
final class DoctrineLoanRepositoryTest extends LoanRepositoryContract
{
    private EntityManager $entities;

    protected function repositories(Book ...$books): array
    {
        $this->entities = Entities::manager();
        $bookRepository = new DoctrineBookRepository($this->entities);
        foreach ($books as $book) {
            $bookRepository->add($book);
        }
        $members = new DoctrineMemberRepository($this->entities);
        $members->add($this->amira);
        $members->add($this->jonas);

        return [$bookRepository, new DoctrineLoanRepository($this->entities)];
    }

    public function testALoanThatFailsTakesNoCopy(): void
    {
        $sula = new Book('Sula', 'Toni Morrison', 1973, 2);
        [, $loans] = $this->repositories($sula);

        try {
            $loans->add(new Loan($sula, new Member('Nobody', Role::Reader), new DateTimeImmutable('2026-03-16')));
            self::fail('A loan for someone who is not a member must fail');
        } catch (ORMInvalidArgumentException $e) {
            self::assertStringContainsString('A new entity was found', $e->getMessage());
        }
        $copies = $this->entities->getConnection()->fetchOne("SELECT copies FROM books WHERE title = 'Sula'");
        self::assertSame(2, $copies, 'The transaction must give the copy back');
        self::assertFalse($this->entities->getConnection()->isTransactionActive());
    }

    public function testALoanComesBackWithItsBookAndMember(): void
    {
        $sula = new Book('Sula', 'Toni Morrison', 1973, 2);
        [, $loans] = $this->repositories($sula);
        $loans->add(new Loan($sula, $this->amira, new DateTimeImmutable('2026-03-16')));
        $this->entities->clear();

        $loan = $loans->forMember('amira')[0] ?? null;

        self::assertNotNull($loan);
        self::assertSame(['Sula', 'Amira', '2026-03-16'], [$loan->book->title, $loan->member->name, $loan->due->format('Y-m-d')]);
    }
}
