<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'mail.php');

final class MailTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('MAIL_PASSWORD'); // unset
    }

    public function testThePasswordComesFromTheEnvironment(): void
    {
        putenv('MAIL_PASSWORD=river-stones-2026');

        self::assertSame('river-stones-2026', mailPassword());
        self::assertStringNotContainsString('sunflower-2026', (string) file_get_contents(subject(__DIR__, 'mail.php')), 'Remove the password from the code');
    }

    public function testAMissingPasswordStopsWithItsName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MAIL_PASSWORD is not set');

        mailPassword();
    }
}
