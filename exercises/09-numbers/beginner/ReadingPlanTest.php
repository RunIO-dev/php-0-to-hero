<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReadingPlanTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = subject(__DIR__, 'reading-plan.php');
    }

    public function testPrintsThePlan(): void
    {
        self::assertSame("Days: 14\nLast day: 3 pages\n", output_of($this->file));
    }

    public function testCalculatesTheNumbers(): void
    {
        $typed = array_filter(
            PhpToken::tokenize((string) file_get_contents($this->file)),
            static fn(PhpToken $token): bool => $token->is(T_LNUMBER) && in_array($token->text, ['14', '3'], true),
        );
        self::assertSame([], array_values(array_map(static fn(PhpToken $t): string => $t->text, $typed)), 'Calculate 14 and 3 from $pages and $pagesPerDay.');
    }
}
