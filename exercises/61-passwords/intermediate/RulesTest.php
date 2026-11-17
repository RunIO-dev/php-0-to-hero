<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'rules.php');

final class RulesTest extends TestCase
{
    public function testAGoodPasswordHasNoProblem(): void
    {
        self::assertNull(passwordProblem('tea kettle under the lemon tree', 'Amira'));
        self::assertNull(passwordProblem('شاي تحت شجرة الليمون', 'Amira'), '20 Arabic letters are 20 characters');
    }

    public function testLength(): void
    {
        self::assertSame('Use at least 15 characters', passwordProblem('sunflower', 'Amira'));
        self::assertSame('Use at least 15 characters', passwordProblem('ééééééééééééé', 'Amira'), '13 letters, 26 bytes: count letters');
        self::assertSame('Use at most 72 bytes', passwordProblem(str_repeat('شاي ', 20), 'Amira'));
    }

    public function testTheMembersName(): void
    {
        self::assertSame("Don't use your name", passwordProblem('my name is AMIRA and I read', 'Amira'));
    }

    public function testCommonPasswords(): void
    {
        self::assertSame('This password is too common', passwordProblem('QwertyuiopASDFG', 'Amira'));
    }
}
