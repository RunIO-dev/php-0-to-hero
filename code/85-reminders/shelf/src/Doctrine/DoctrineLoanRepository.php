<?php

declare(strict_types=1);

namespace Shelf\Doctrine;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Shelf\Book;
use Shelf\Hold;
use Shelf\HoldRepository;
use Shelf\HoldStatus;
use Shelf\Loan;
use Shelf\Member;
use Shelf\LoanRepository;
use Shelf\NoCopyLeft;

/**
 * The loans, through Doctrine (chapter 74). A loan's book and member come back as objects with it:
 * the JOINs that PdoLoanRepository wrote by hand, Doctrine writes from the mapping. Chapter 84: a copy
 * that comes back goes to the reservations first.
 */
final readonly class DoctrineLoanRepository implements LoanRepository
{
    public function __construct(private EntityManagerInterface $entities, private HoldRepository $holds) {}

    public function add(Loan $loan, ?Hold $hold = null): void
    {
        $this->entities->wrapInTransaction(function () use ($loan, $hold): void {
            if ($hold?->status !== HoldStatus::Ready) { // a copy set aside (chapter 84) is off the shelf already
                // Only if a copy is left, checked and taken in one statement: another request can't come in between (chapter 57).
                $taken = $this->entities->createQuery('UPDATE ' . Book::class . ' b SET b.copies = b.copies - 1 WHERE b.id = :book AND b.copies > 0')
                    ->setParameter('book', $loan->book)
                    ->execute();
                if ($taken === 0) {
                    throw new NoCopyLeft("All copies of \"{$loan->book->title}\" are on loan");
                }
            }
            $hold?->collect();
            $this->entities->persist($loan);
        });
        $this->entities->refresh($loan->book); // the UPDATE went past the object: read its copies again
    }

    public function forMember(string $name): array
    {
        /** @var list<Loan> */
        return $this->entities->createQuery(
            'SELECT l, b, m FROM ' . Loan::class . ' l JOIN l.book b JOIN l.member m WHERE m.name = :name AND l.returnedOn IS NULL ORDER BY l.due, b.title',
        )->setParameter('name', trim($name))->getResult();
    }

    public function isOnLoan(string $title): bool
    {
        return $this->entities->createQuery('SELECT COUNT(l.id) FROM ' . Loan::class . ' l JOIN l.book b WHERE b.title = :title AND l.returnedOn IS NULL')
            ->setParameter('title', trim($title))
            ->getSingleScalarResult() > 0;
    }

    public function find(int $id): ?Loan
    {
        return $this->entities->find(Loan::class, $id);
    }

    public function current(): array
    {
        /** @var list<Loan> */
        return $this->entities->createQuery(
            'SELECT l, b, m FROM ' . Loan::class . ' l JOIN l.book b JOIN l.member m WHERE l.returnedOn IS NULL ORDER BY l.due, b.title, m.name',
        )->getResult();
    }

    public function giveBack(Loan $loan, DateTimeImmutable $today): ?Hold
    {
        /** @var ?Hold */
        return $this->entities->wrapInTransaction(function () use ($loan, $today): ?Hold {
            $loan->giveBack($today);
            $this->entities->flush();

            // Chapter 84: to the first in the queue, or on the shelf; in SQL, so two returns at once can't lose one.
            return $this->holds->passOn($loan->book, $today);
        });
    }

    public function update(Loan $loan): void
    {
        $this->entities->flush();
    }

    public function markReminded(Loan $loan, ?DateTimeImmutable $seen, DateTimeImmutable $today): bool
    {
        $query = $this->entities->createQuery('UPDATE ' . Loan::class . ' l SET l.remindedOn = :today WHERE l.id = :id AND '
            . ($seen === null ? 'l.remindedOn IS NULL' : 'l.remindedOn = :seen'))
            ->setParameter('today', $today, Types::DATE_IMMUTABLE)
            ->setParameter('id', $loan->id());
        if ($seen !== null) {
            $query->setParameter('seen', $seen, Types::DATE_IMMUTABLE);
        }
        $claimed = $query->execute() === 1;
        $this->entities->refresh($loan); // the UPDATE went past the object

        return $claimed;
    }

    public function forget(Member $member): void
    {
        $this->removeReturned('member', $member);
    }

    public function forgetBook(Book $book): void
    {
        $this->removeReturned('book', $book);
    }

    /** $field is 'member' or 'book': the returned loans of that member or book go. */
    private function removeReturned(string $field, Member|Book $of): void
    {
        // remove(), not a DELETE query: loans already loaded would stay in Doctrine's memory otherwise.
        /** @var list<Loan> $returned */
        $returned = $this->entities->createQuery('SELECT l FROM ' . Loan::class . " l WHERE l.$field = :of AND l.returnedOn IS NOT NULL")
            ->setParameter('of', $of)
            ->getResult();
        foreach ($returned as $loan) {
            $this->entities->remove($loan);
        }
        $this->entities->flush();
    }
}
