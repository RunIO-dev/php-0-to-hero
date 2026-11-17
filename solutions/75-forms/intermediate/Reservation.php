<?php

declare(strict_types=1);

namespace Desk;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A reader reserves a room of the library, from one day to another. Rules that look at one field
 * are attributes; a rule about two fields together is a Callback, which reports on the field it names:
 * - room: "Which room?" when blank;
 * - from, until: "From when?" and "Until when?" when missing;
 * - until must be after from: on until, "The reservation ends before it starts.";
 * - at most 14 days: on until, "A room is reserved for at most 14 days.".
 */
final class Reservation
{
    public const int MOST_DAYS = 14;

    #[Assert\NotBlank(message: 'Which room?')]
    public ?string $room = null;

    #[Assert\NotNull(message: 'From when?')]
    public ?DateTimeImmutable $from = null;

    #[Assert\NotNull(message: 'Until when?')]
    public ?DateTimeImmutable $until = null;

    #[Assert\Callback]
    public function checkDays(ExecutionContextInterface $context): void
    {
        if ($this->from === null || $this->until === null) {
            return; // NotNull has said it already
        }
        if ($this->until <= $this->from) {
            $context->buildViolation('The reservation ends before it starts.')->atPath('until')->addViolation();
        } elseif ($this->from->diff($this->until)->days > self::MOST_DAYS) {
            $context->buildViolation('A room is reserved for at most ' . self::MOST_DAYS . ' days.')->atPath('until')->addViolation();
        }
    }
}
