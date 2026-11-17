<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ConstraintsTest extends TestCase
{
    /** @param array<string, bool> $versions */
    private static function check(string $constraint, array $versions): void
    {
        require_once subject(__DIR__, 'constraints.php');
        foreach ($versions as $version => $allowed) {
            self::assertSame($allowed, allows($constraint, (string) $version), "$constraint and $version");
        }
    }

    public function testAnExactVersion(): void
    {
        self::check('3.0.2', ['3.0.2' => true, '3.0.1' => false, '3.0.3' => false]);
    }

    public function testCaret(): void
    {
        self::check('^1.2', ['1.1.9' => false, '1.2.0' => true, '1.9.9' => true, '2.0.0' => false]);
        self::check('^1.2.3', ['1.2.2' => false, '1.2.3' => true, '1.10.0' => true, '2.0.0' => false]);
    }

    public function testCaretBeforeOnePointZero(): void
    {
        self::check('^0.3', ['0.2.9' => false, '0.3.0' => true, '0.3.7' => true, '0.4.0' => false]);
        self::check('^0.0.3', ['0.0.3' => true, '0.0.4' => false]);
    }

    public function testTilde(): void
    {
        self::check('~1.2.3', ['1.2.2' => false, '1.2.3' => true, '1.2.9' => true, '1.3.0' => false]);
        self::check('~1.2', ['1.2.0' => true, '1.9.0' => true, '2.0.0' => false]);
    }
}
