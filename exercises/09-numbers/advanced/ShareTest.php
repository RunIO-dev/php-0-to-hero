<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ShareTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = subject(__DIR__, 'share.php');
    }

    public function testTheSharesAddUpToTheCost(): void
    {
        self::assertSame("Amira: 334\nJonas: 333\nKenji: 333\n", output_of($this->file));
    }

    public function testCalculatesTheShares(): void
    {
        $typed = array_filter(
            PhpToken::tokenize((string) file_get_contents($this->file)),
            static fn(PhpToken $token): bool => $token->is(T_LNUMBER) && in_array($token->text, ['333', '334'], true),
        );
        self::assertSame([], array_values($typed), 'Calculate the shares from $costCents and $people.');
    }
}
