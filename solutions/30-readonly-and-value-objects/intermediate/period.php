<?php

declare(strict_types=1);

final readonly class LoanPeriod
{
    public function __construct(public DateTimeImmutable $from, public DateTimeImmutable $until)
    {
        if ($until <= $from) {
            throw new InvalidArgumentException('A loan period must end after it starts');
        }
    }

    public function days(): int
    {
        return (int) $this->from->diff($this->until)->days;
    }

    public function extendedBy(int $days): LoanPeriod
    {
        return new LoanPeriod($this->from, $this->until->modify("+$days days"));
    }

    public function contains(DateTimeImmutable $day): bool
    {
        return $day >= $this->from && $day <= $this->until;
    }
}
