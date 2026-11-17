<?php

declare(strict_types=1);

namespace Desk;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

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

    // TODO: a Callback for the two rules about from and until together
}
