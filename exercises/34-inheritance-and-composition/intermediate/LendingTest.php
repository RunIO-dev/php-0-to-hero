<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LendingTest extends TestCase
{
    public function testItWritesToTheLogItIsGiven(): void
    {
        require_once subject(__DIR__, 'lending.php');

        $log = new MemoryLog();
        $lending = new Lending($log);

        self::assertSame('Kindred lent to Amira', $lending->lend('Kindred', 'Amira'));
        $lending->lend('Sula', 'Kenji');
        self::assertSame(['Kindred lent to Amira', 'Sula lent to Kenji'], $log->lines);
    }

    public function testAnyLog(): void
    {
        require_once subject(__DIR__, 'lending.php');

        $log = new class implements Log {
            public int $count = 0;

            public function write(string $line): void
            {
                $this->count++;
            }
        };
        (new Lending($log))->lend('Kitchen', 'Jonas');

        self::assertSame(1, $log->count);
    }

    public function testLendingIsNoLog(): void
    {
        require_once subject(__DIR__, 'lending.php');

        self::assertFalse((new ReflectionClass(Lending::class))->getParentClass(), 'Lending should not extend a class.');
        self::assertFalse(is_subclass_of(Lending::class, Log::class), 'Lending should have a log, not be one.');
    }
}
