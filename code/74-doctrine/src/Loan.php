<?php

declare(strict_types=1);

namespace Library;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/** A loan points to its book: in the table, the book's ID; in PHP, the Book. */
#[ORM\Entity]
#[ORM\Table(name: 'loans')]
final class Loan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        public readonly Book $book,
        #[ORM\Column]
        public readonly string $member,
        #[ORM\Column(type: 'date_immutable')]
        public readonly DateTimeImmutable $due,
    ) {}
}
