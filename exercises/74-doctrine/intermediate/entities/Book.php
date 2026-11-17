<?php

declare(strict_types=1);

namespace Desk;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'books')]
final class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    public function __construct(
        #[ORM\Column]
        public readonly string $title,
    ) {}
}
