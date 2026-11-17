<?php

declare(strict_types=1);

final class Book
{
    public string $title;
    public int $copies;

    // A method is a function that belongs to the object. $this is that object.
    public function isAvailable(): bool
    {
        return $this->copies > 0;
    }

    public function status(): string
    {
        return $this->title . ': ' . ($this->isAvailable() ? 'available' : 'all copies on loan');
    }
}

$kindred = new Book();
$kindred->title = 'Kindred';
$kindred->copies = 0;

$kitchen = new Book();
$kitchen->title = 'Kitchen';
$kitchen->copies = 1;

echo $kindred->status(), "\n";
echo $kitchen->status(), "\n";
