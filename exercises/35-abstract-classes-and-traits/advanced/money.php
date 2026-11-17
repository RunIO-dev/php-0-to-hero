<?php

declare(strict_types=1);

// Exercise 35, advanced: from a trait to a value object.
//
// Receipt and FeeNotice share a trait for formatting money. Replace the trait
// with a readonly value object Money:
// - its constructor takes cents (an int, 0 or more, or an
//   InvalidArgumentException);
// - format(): string returns "1.50 EUR".
// Receipt and FeeNotice then take a Money in their constructors instead of an
// int, and their line() methods print the same text as now. Remove the trait.

trait FormatsMoney
{
    private function money(int $cents): string
    {
        return number_format($cents / 100, 2) . ' EUR';
    }
}

final readonly class Receipt
{
    use FormatsMoney;

    public function __construct(private int $paid) {}

    public function line(): string
    {
        return 'Paid: ' . $this->money($this->paid);
    }
}

final readonly class FeeNotice
{
    use FormatsMoney;

    public function __construct(private int $owed) {}

    public function line(): string
    {
        return 'Please pay ' . $this->money($this->owed);
    }
}

// Check your work: composer exercise 35 advanced
