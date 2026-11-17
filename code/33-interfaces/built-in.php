<?php

declare(strict_types=1);

// PHP's own interfaces: count() works with Countable, json_encode with JsonSerializable.
final class Basket implements Countable, JsonSerializable
{
    /** @var list<string> */
    private array $titles = [];

    public function add(string $title): void
    {
        $this->titles[] = $title;
    }

    public function count(): int
    {
        return count($this->titles);
    }

    /** @return array{titles: list<string>} */
    public function jsonSerialize(): array
    {
        return ['titles' => $this->titles];
    }
}

$basket = new Basket();
$basket->add('Kindred');
$basket->add('Sula');

echo count($basket), " books\n";
echo json_encode($basket), "\n";
