<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'queue.php');

final class QueueTest extends TestCase
{
    private const array HOLDS = [
        ['member' => 'Amira', 'status' => 'collected'],
        ['member' => 'Ben', 'status' => 'waiting'],
        ['member' => 'Chen', 'status' => 'cancelled'],
        ['member' => 'Dana', 'status' => 'waiting'],
        ['member' => 'Emil', 'status' => 'ready'],
        ['member' => 'Chen', 'status' => 'waiting'],
    ];

    /** @return iterable<string, array{string, ?int}> */
    public static function members(): iterable
    {
        yield 'the first who waits' => ['Ben', 1];
        yield 'after one who waits' => ['Dana', 2];
        yield 'cancelled, then reserved again' => ['Chen', 3];
        yield 'their copy is ready' => ['Emil', null];
        yield 'borrowed it already' => ['Amira', null];
        yield 'never reserved' => ['Farah', null];
        yield 'as typed' => ['ben', null];
    }

    #[DataProvider('members')]
    public function testAPlaceCountsOnlyWhoWaits(string $member, ?int $place): void
    {
        self::assertSame($place, placeInQueue(self::HOLDS, $member));
    }

    public function testAnEmptyQueue(): void
    {
        self::assertNull(placeInQueue([], 'Ben'));
    }
}
