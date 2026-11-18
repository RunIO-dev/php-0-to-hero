<?php

declare(strict_types=1);

// Events (chapter 80): the code that lends a book says only "a loan was made". Who reacts to it, and in
// which order, is decided elsewhere: a new reaction needs no change to the code that lends.

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

require __DIR__ . '/vendor/autoload.php';

final class LoanMade extends Event
{
    public function __construct(
        public readonly string $member,
        public readonly string $title,
    ) {}
}

$dispatcher = new EventDispatcher();
$dispatcher->addListener(LoanMade::class, static function (LoanMade $loan): void {
    echo "log: {$loan->member} borrowed {$loan->title}\n";
});
// A higher priority runs first; the default is 0.
$dispatcher->addListener(LoanMade::class, static function (LoanMade $loan): void {
    echo "stock: one copy of {$loan->title} fewer\n";
}, priority: 10);

// The code that lends the book:
echo "lend: Kindred to Amira\n";
$dispatcher->dispatch(new LoanMade('Amira', 'Kindred'));
