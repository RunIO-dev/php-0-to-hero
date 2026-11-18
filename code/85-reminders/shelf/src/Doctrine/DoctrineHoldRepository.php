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
use Shelf\Member;

/** The reservations, through Doctrine (chapter 84). */
final readonly class DoctrineHoldRepository implements HoldRepository
{
    private const string WAITING = HoldStatus::Waiting->value;
    private const string READY = HoldStatus::Ready->value;

    public function __construct(private EntityManagerInterface $entities) {}

    public function add(Hold $hold): void
    {
        $this->entities->persist($hold);
        $this->entities->flush();
    }

    public function find(int $id): ?Hold
    {
        return $this->entities->find(Hold::class, $id);
    }

    public function openFor(Book $book, Member $member): ?Hold
    {
        /** @var ?Hold */
        return $this->entities->createQuery('SELECT h FROM ' . Hold::class . ' h WHERE h.book = :book AND h.member = :member AND h.status IN (:open)')
            ->setParameters(['book' => $book, 'member' => $member, 'open' => [self::WAITING, self::READY]])
            ->getOneOrNullResult();
    }

    public function ofMember(Member $member): array
    {
        /** @var list<Hold> */
        return $this->entities->createQuery('SELECT h, b FROM ' . Hold::class . ' h JOIN h.book b WHERE h.member = :member AND h.status IN (:open) ORDER BY h.id')
            ->setParameters(['member' => $member, 'open' => [self::WAITING, self::READY]])
            ->getResult();
    }

    public function ready(): array
    {
        /** @var list<Hold> */
        return $this->entities->createQuery('SELECT h, b, m FROM ' . Hold::class . ' h JOIN h.book b JOIN h.member m WHERE h.status = :ready ORDER BY h.readyUntil, b.title')
            ->setParameter('ready', self::READY)
            ->getResult();
    }

    public function expired(DateTimeImmutable $today): array
    {
        /** @var list<Hold> */
        return $this->entities->createQuery('SELECT h, b, m FROM ' . Hold::class . ' h JOIN h.book b JOIN h.member m WHERE h.status = :ready AND h.readyUntil < :today ORDER BY h.id')
            ->setParameter('ready', self::READY)
            ->setParameter('today', $today, Types::DATE_IMMUTABLE)
            ->getResult();
    }

    public function position(Hold $hold): int
    {
        return (int) $this->entities->createQuery('SELECT COUNT(h.id) FROM ' . Hold::class . ' h WHERE h.book = :book AND h.status = :waiting AND h.id <= :id')
            ->setParameters(['book' => $hold->book, 'waiting' => self::WAITING, 'id' => $hold->id()])
            ->getSingleScalarResult();
    }

    public function isWaitedFor(Book $book): bool
    {
        return $this->firstInQueue($book) !== null;
    }

    public function passOn(Book $book, DateTimeImmutable $today): ?Hold
    {
        /** @var ?Hold $hold */
        $hold = $this->entities->wrapInTransaction(function () use ($book, $today): ?Hold {
            while (($first = $this->firstInQueue($book)) !== null) {
                // Ready only if it is still waiting: if another request claimed it a moment ago, 0 rows change.
                $claimed = $this->entities->createQuery('UPDATE ' . Hold::class . ' h SET h.status = :ready, h.readyUntil = :until WHERE h.id = :id AND h.status = :waiting')
                    ->setParameter('ready', self::READY)
                    ->setParameter('until', Hold::pickupBy($today), Types::DATE_IMMUTABLE)
                    ->setParameter('id', $first)
                    ->setParameter('waiting', self::WAITING)
                    ->execute();
                if ($claimed === 1) {
                    return $this->entities->find(Hold::class, $first);
                }
            }
            $this->entities->createQuery('UPDATE ' . Book::class . ' b SET b.copies = b.copies + 1 WHERE b.id = :book')
                ->setParameter('book', $book)
                ->execute();

            return null;
        });
        // The UPDATEs went past the objects: read them again.
        if ($hold !== null) {
            $this->entities->refresh($hold);
        }
        $this->entities->refresh($book);

        return $hold;
    }

    public function release(Hold $hold, DateTimeImmutable $today): ?Hold
    {
        /** @var ?Hold */
        return $this->entities->wrapInTransaction(function () use ($hold, $today): ?Hold {
            $this->entities->flush();

            return $hold->hadACopy() ? $this->passOn($hold->book, $today) : null;
        });
    }

    public function markNotified(Hold $hold, DateTimeImmutable $today): bool
    {
        $claimed = $this->entities->createQuery('UPDATE ' . Hold::class . ' h SET h.notifiedOn = :today WHERE h.id = :id AND h.notifiedOn IS NULL')
            ->setParameter('today', $today, Types::DATE_IMMUTABLE)
            ->setParameter('id', $hold->id())
            ->execute() === 1;
        $this->entities->refresh($hold);

        return $claimed;
    }

    public function update(Hold $hold): void
    {
        $this->entities->flush();
    }

    public function forget(Member $member): void
    {
        $this->removeAll('member', $member);
    }

    public function forgetBook(Book $book): void
    {
        $this->removeAll('book', $book);
    }

    /** The number of the first reservation in the book's queue, or null if nobody is waiting. */
    private function firstInQueue(Book $book): ?int
    {
        $first = $this->entities->createQuery('SELECT MIN(h.id) FROM ' . Hold::class . ' h WHERE h.book = :book AND h.status = :waiting')
            ->setParameters(['book' => $book, 'waiting' => self::WAITING])
            ->getSingleScalarResult();

        return $first === null ? null : (int) $first;
    }

    /** $field is 'member' or 'book'. remove(), not a DELETE query, as for the loans (chapter 83). */
    private function removeAll(string $field, Member|Book $of): void
    {
        /** @var list<Hold> $holds */
        $holds = $this->entities->createQuery('SELECT h FROM ' . Hold::class . " h WHERE h.$field = :of")
            ->setParameter('of', $of)
            ->getResult();
        foreach ($holds as $hold) {
            $this->entities->remove($hold);
        }
        $this->entities->flush();
    }
}
