<?php

declare(strict_types=1);

namespace Library;

use Doctrine\ORM\Mapping as ORM;

/** A book, as an entity: the attributes say where Doctrine keeps it. */
#[ORM\Entity]
#[ORM\Table(name: 'books')]
final class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    public function __construct(
        #[ORM\Column(unique: true)]
        public readonly string $title,
        #[ORM\Column]
        public readonly string $author,
        #[ORM\Column]
        public private(set) int $copies = 1,
    ) {}

    public function lend(): void
    {
        $this->copies--;
    }
}
