<?php

declare(strict_types=1);

namespace Library\Catalog;

use DateTimeImmutable;

final readonly class Book
{
    public function __construct(public string $title) {}
}

function dueDate(DateTimeImmutable $lentOn): DateTimeImmutable
{
    return $lentOn->modify('+14 days');
}
