<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

require_once subject(__DIR__, 'lend.php');

final class LendTest extends TestCase
{
    public function testEachLoanIsLogged(): void
    {
        $log = new class extends AbstractLogger {
            /** @var list<array{mixed, string, array<mixed>}> */
            public array $records = [];

            public function log($level, string|Stringable $message, array $context = []): void
            {
                $this->records[] = [$level, (string) $message, $context];
            }
        };

        self::assertTrue(lend($log, 'Kindred', 'Amira', 1));
        self::assertFalse(lend($log, 'Sula', 'Jonas', 0));

        self::assertSame([
            ['info', '{member} borrowed {title}', ['member' => 'Amira', 'title' => 'Kindred']],
            ['warning', '{member} could not borrow {title}: no copy left', ['member' => 'Jonas', 'title' => 'Sula']],
        ], $log->records);
    }
}
