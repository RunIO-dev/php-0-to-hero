<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'health.php');

final class HealthTest extends TestCase
{
    public function testEveryCheckPassed(): void
    {
        [$status, $body] = health(['database' => fn(): bool => true, 'worker' => fn(): bool => true]);

        self::assertSame(200, $status);
        self::assertSame(['status' => 'ok', 'checks' => ['database' => 'ok', 'worker' => 'ok']], json_decode($body, true));
    }

    public function testOneFailedCheckFailsTheWhole(): void
    {
        [$status, $body] = health(['database' => fn(): bool => true, 'worker' => fn(): bool => false]);

        self::assertSame(503, $status);
        self::assertSame(['status' => 'failing', 'checks' => ['database' => 'ok', 'worker' => 'failing']], json_decode($body, true));
    }

    public function testACheckThatThrowsFailsAndSaysNothingMore(): void
    {
        [$status, $body] = health(['backup' => fn(): bool => throw new RuntimeException('/home/shelf/backups: Permission denied')]);

        self::assertSame(503, $status);
        self::assertSame(['status' => 'failing', 'checks' => ['backup' => 'failing']], json_decode($body, true));
        self::assertStringNotContainsString('Permission', $body);
    }
}
