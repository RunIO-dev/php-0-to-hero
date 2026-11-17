<?php

declare(strict_types=1);

// L: a class that implements an interface must keep all its promises.
interface Rack
{
    /** Puts a book on the rack. */
    public function add(string $title): void;

    public function count(): int;
}

final class OpenRack implements Rack
{
    private int $count = 0;

    public function add(string $title): void
    {
        $this->count++;
    }

    public function count(): int
    {
        return $this->count;
    }
}

// ✗ It says it is a Rack, but refuses half of what a Rack promises.
final class DisplayCase implements Rack
{
    public function add(string $title): void
    {
        throw new LogicException('A display case is locked');
    }

    public function count(): int
    {
        return 3;
    }
}

function stock(Rack $rack): int
{
    $rack->add('Kindred');

    return $rack->count();
}

foreach ([new OpenRack(), new DisplayCase()] as $rack) {
    try {
        $count = stock($rack);
        echo $rack::class, ": $count\n";
    } catch (LogicException $e) {
        echo $rack::class, ': ', $e->getMessage(), "\n";
    }
}
