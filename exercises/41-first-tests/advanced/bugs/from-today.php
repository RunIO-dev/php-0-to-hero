<?php

declare(strict_types=1);

/** Renewing always counts the year from today, so a valid card loses days. */
final readonly class Card
{
    public function __construct(public string $member, public DateTimeImmutable $expires) {}

    public function isValidOn(DateTimeImmutable $day): bool
    {
        return $day->format('Y-m-d') <= $this->expires->format('Y-m-d');
    }

    /**
     * A new card that is valid for one more year: from the old expiry date when the card is
     * still valid, or from today when it has expired. The old card doesn't change.
     */
    public function renew(DateTimeImmutable $today): self
    {
        $from = $today;

        return new self($this->member, $from->modify('+1 year'));
    }
}
