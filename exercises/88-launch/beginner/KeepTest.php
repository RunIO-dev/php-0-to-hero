<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'keep.php');

final class KeepTest extends TestCase
{
    public function testTheNewestStay(): void
    {
        $names = ['2026-03-04T023000', '2026-03-01T023000', '2026-03-03T023000', '2026-03-02T023000'];

        self::assertSame(['2026-03-01T023000', '2026-03-02T023000'], toDelete($names, 2));
    }

    public function testFewerThanKeepDeleteNothing(): void
    {
        self::assertSame([], toDelete(['2026-03-01T023000'], 7));
    }

    public function testOtherNamesAreNeverDeleted(): void
    {
        $names = ['notes', '2026-03-01T023000', '2026-13-01T023000', '2026-03-02T023000', '.', '..'];

        self::assertSame(['2026-03-01T023000'], toDelete($names, 1));
    }
}
