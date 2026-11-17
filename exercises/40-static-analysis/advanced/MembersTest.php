<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MembersTest extends TestCase
{
    public function testPhpstanAtLevel10FindsNothing(): void
    {
        $analysis = analysis_of(subject(__DIR__, 'members.php'), 10);

        self::assertTrue($analysis['passed'], "PHPStan says:\n" . $analysis['out']);
    }

    public function testGoodData(): void
    {
        require_once subject(__DIR__, 'members.php');

        $members = membersFrom('[{"name": "Amira", "card": 1042}, {"name": "Kenji", "card": 7}]');
        self::assertCount(2, $members);
        self::assertSame('Kenji', $members[1]->name);
        self::assertSame(1042, $members[0]->card);
        self::assertSame([], membersFrom('[]'));
    }

    public function testBadDataIsRejected(): void
    {
        require_once subject(__DIR__, 'members.php');

        foreach (['{"name": "Amira"}', '42', '[{"name": "Amira"}]', '[{"name": "Amira", "card": "1042"}]', '["Amira"]'] as $json) {
            try {
                membersFrom($json);
                self::fail("$json was accepted");
            } catch (UnexpectedValueException) {
                self::addToAssertionCount(1);
            }
        }
    }
}
