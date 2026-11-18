<?php

declare(strict_types=1);

namespace Shelf\Tests\Database;

use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Shelf\Doctrine\DoctrineBookRepository;
use Shelf\Doctrine\DoctrineHoldRepository;
use Shelf\Doctrine\DoctrineMemberRepository;
use Shelf\Hold;
use Shelf\Tests\HoldRepositoryContract;

/** The reservations in a real SQLite database, through Doctrine (chapter 84). */
final class DoctrineHoldRepositoryTest extends HoldRepositoryContract
{
    private EntityManager $entities;

    protected function repositories(): array
    {
        $this->entities = Entities::manager();
        $books = new DoctrineBookRepository($this->entities);
        $books->add($this->sula);
        $members = new DoctrineMemberRepository($this->entities);
        $members->add($this->amira);
        $members->add($this->jonas);

        return [$books, new DoctrineHoldRepository($this->entities)];
    }

    /**
     * Two copies come back at once, at two desks. The other desk was quicker and gave Amira hers; this
     * desk's object for her reservation still says "waiting". The queue is read from the database,
     * so this copy goes to the next in line.
     */
    public function testAReservationGetsOneCopy(): void
    {
        [, $holds] = $this->repositories();
        $holds->add($amiras = new Hold($this->sula, $this->amira, new DateTimeImmutable('2026-03-02')));
        $holds->add($jonass = new Hold($this->sula, $this->jonas, new DateTimeImmutable('2026-03-02')));
        $this->entities->getConnection()->executeStatement("UPDATE holds SET status = 'ready', ready_until = '2026-03-09' WHERE id = ?", [$amiras->id()]);

        $next = $holds->passOn($this->sula, new DateTimeImmutable('2026-03-02'));

        self::assertSame($jonass->id(), $next?->id(), 'Not Amira twice');
    }
}
