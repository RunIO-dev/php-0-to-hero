<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'reservations.php');

final class ReservationsTest extends TestCase
{
    public function testEnglish(): void
    {
        self::assertSame(['no reservations', '1 reservation', '2 reservations'], [reservations('en', 0), reservations('en', 1), reservations('en', 2)]);
    }

    public function testGerman(): void
    {
        self::assertSame(['keine Reservierungen', '1 Reservierung', '7 Reservierungen'], [reservations('de', 0), reservations('de', 1), reservations('de', 7)]);
    }

    public function testTheNumberIsWrittenAsTheLanguageWritesIt(): void
    {
        self::assertSame('1,234 reservations', reservations('en', 1234));
        self::assertSame('1.234 Reservierungen', reservations('de', 1234));
    }
}
