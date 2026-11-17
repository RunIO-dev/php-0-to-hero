<?php

declare(strict_types=1);

final class Branch
{
    // static: one list for the class, shared by every branch.
    /** @var list<string> */
    private static array $titles = [];

    public function __construct(public readonly string $name) {}

    public function add(string $title): void
    {
        self::$titles[] = $title;
    }

    public function count(): int
    {
        return count(self::$titles);
    }
}

$north = new Branch('North');
$south = new Branch('South');
$north->add('Kindred');
$north->add('Kitchen');

echo "$south->name has ", $south->count(), " books\n";
