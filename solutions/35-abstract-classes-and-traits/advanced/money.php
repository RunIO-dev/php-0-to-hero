<?php

declare(strict_types=1);

final readonly class Money
{
    public function __construct(public int $cents)
    {
        if ($cents < 0) {
            throw new InvalidArgumentException("An amount can't be $cents cents");
        }
    }

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

final readonly class FeeNotice
{
    public function __construct(private Money $owed) {}

    public function line(): string
    {
        return 'Please pay ' . $this->owed->format();
    }
}
