<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AuditTest extends TestCase
{
    private const array ADVISORIES = [
        ['package' => 'example/test-tools', 'fixed' => '1.9.1', 'title' => 'Test reports readable by anyone'],
        ['package' => 'example/http-client', 'fixed' => '7.4.5', 'title' => 'Cookies sent to the wrong host'],
        ['package' => 'example/log', 'fixed' => '3.0.1', 'title' => 'Log lines can be forged'],
        ['package' => 'example/templates', 'fixed' => '2.1.0', 'title' => 'Output not escaped'],
        ['package' => 'example/mailer', 'fixed' => '5.0.0', 'title' => 'Header injection'],
    ];

    public function testListsOnlyTheAffectedPackages(): void
    {
        require_once subject(__DIR__, 'audit.php');

        self::assertSame([
            'example/http-client 7.4.4: Cookies sent to the wrong host (fixed in 7.4.5)',
            'example/test-tools 1.9.0: Test reports readable by anyone (fixed in 1.9.1)',
        ], affected(__DIR__ . '/composer.lock', self::ADVISORIES));
    }

    public function testNoAdvisoriesNoLines(): void
    {
        require_once subject(__DIR__, 'audit.php');

        self::assertSame([], affected(__DIR__ . '/composer.lock', []));
    }

    public function testComparesVersionsNotText(): void
    {
        require_once subject(__DIR__, 'audit.php');

        $lock = sys_get_temp_dir() . '/audit-' . bin2hex(random_bytes(4)) . '.lock';
        file_put_contents($lock, '{"packages": [{"name": "example/log", "version": "3.10.0"}]}');
        try {
            self::assertSame([], affected($lock, [['package' => 'example/log', 'fixed' => '3.9.0', 'title' => 'Old']]));
        } finally {
            unlink($lock);
        }
    }
}
