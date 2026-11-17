<?php

declare(strict_types=1);

final readonly class CardNumber
{
    private function __construct(public string $number) {}

    public static function fromString(string $input): self
    {
        $number = trim($input);
        if (preg_match('/^MC-\d{6}$/D', $number) !== 1) {
            throw new InvalidArgumentException("\"$input\" is not a card number");
        }

        return new self($number);
    }

    public static function after(CardNumber $last): self
    {
        return new self(sprintf('MC-%06d', (int) substr($last->number, 3) + 1));
    }
}
