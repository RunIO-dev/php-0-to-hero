<?php

declare(strict_types=1);

// The same job without a trait: a small value object that both classes use.
final readonly class Money
{
    public function __construct(public int $cents) {}

    public function format(): string
    {
        return number_format($this->cents / 100, 2) . ' EUR';
    }
}

final readonly class Receipt
{
    public function __construct(private Money $paid) {}

    public function line(): string
    {
        return 'Paid: ' . $this->paid->format();
    }
}

echo (new Receipt(new Money(150)))->line(), "\n";
