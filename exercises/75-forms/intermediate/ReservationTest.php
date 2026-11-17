<?php

declare(strict_types=1);

use Desk\Reservation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

require_once subject(__DIR__, 'Reservation.php');

final class ReservationTest extends TestCase
{
    public function testAWeekIsFine(): void
    {
        self::assertSame([], self::mistakes(self::reservation('Reading room', '2026-12-01', '2026-12-08')));
        self::assertSame([], self::mistakes(self::reservation('Reading room', '2026-12-01', '2026-12-15')), 'Exactly 14 days');
    }

    public function testMissingFields(): void
    {
        self::assertSame(['room' => ['Which room?'], 'from' => ['From when?'], 'until' => ['Until when?']], self::mistakes(new Reservation()));
    }

    public function testTheEndComesAfterTheStart(): void
    {
        self::assertSame(['until' => ['The reservation ends before it starts.']], self::mistakes(self::reservation('Study 2', '2026-12-08', '2026-12-01')));
        self::assertSame(['until' => ['The reservation ends before it starts.']], self::mistakes(self::reservation('Study 2', '2026-12-08', '2026-12-08')));
    }

    public function testAtMostTwoWeeks(): void
    {
        self::assertSame(['until' => ['A room is reserved for at most 14 days.']], self::mistakes(self::reservation('Study 2', '2026-12-01', '2026-12-16')));
    }

    private static function reservation(string $room, string $from, string $until): Reservation
    {
        $reservation = new Reservation();
        $reservation->room = $room;
        $reservation->from = new DateTimeImmutable($from);
        $reservation->until = new DateTimeImmutable($until);

        return $reservation;
    }

    /** @return array<string, list<string>> */
    private static function mistakes(Reservation $reservation): array
    {
        $mistakes = [];
        foreach (Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator()->validate($reservation) as $violation) {
            $mistakes[$violation->getPropertyPath()][] = (string) $violation->getMessage();
        }

        return $mistakes;
    }
}
