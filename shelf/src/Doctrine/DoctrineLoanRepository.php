<?php

declare(strict_types=1);

namespace Shelf\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Shelf\Book;
use Shelf\Loan;
use Shelf\LoanRepository;
use Shelf\NoCopyLeft;

/**
 * The loans, through Doctrine (chapter 74). A loan's book and member come back as objects with it:
 * the JOINs that PdoLoanRepository wrote by hand, Doctrine writes from the mapping.
 */
final readonly class DoctrineLoanRepository implements LoanRepository
{
    public function __construct(private EntityManagerInterface $entities) {}

    public function add(Loan $loan): void
    {
        $this->entities->wrapInTransaction(function () use ($loan): void {
            // Only if a copy is left, checked and taken in one statement: another request can't come in between (chapter 57).
            $taken = $this->entities->createQuery('UPDATE ' . Book::class . ' b SET b.copies = b.copies - 1 WHERE b.id = :book AND b.copies > 0')
                ->setParameter('book', $loan->book)
                ->execute();
            if ($taken === 0) {
                throw new NoCopyLeft("All copies of \"{$loan->book->title}\" are on loan");
            }
            $this->entities->persist($loan);
        });
        $this->entities->refresh($loan->book); // the UPDATE went past the object: read its copies again
    }

    public function forMember(string $name): array
    {
        /** @var list<Loan> */
        return $this->entities->createQuery(
            'SELECT l, b, m FROM ' . Loan::class . ' l JOIN l.book b JOIN l.member m WHERE m.name = :name ORDER BY l.due, b.title',
        )->setParameter('name', trim($name))->getResult();
    }

    public function isOnLoan(string $title): bool
    {
        return $this->entities->createQuery('SELECT COUNT(l.id) FROM ' . Loan::class . ' l JOIN l.book b WHERE b.title = :title')
            ->setParameter('title', trim($title))
            ->getSingleScalarResult() > 0;
    }
}
