<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'tokens.php');

final class TokensTest extends TestCase
{
    private ArraySession $session;
    private Tokens $tokens;

    protected function setUp(): void
    {
        $this->session = new ArraySession();
        $this->tokens = new Tokens($this->session);
    }

    public function testEachFormHasItsOwnToken(): void
    {
        $add = $this->tokens->token('add');

        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $add);
        self::assertSame($add, $this->tokens->token('add'));
        self::assertNotSame($add, $this->tokens->token('delete'));
        self::assertSame($add, $this->session->values['csrf.add'] ?? null);
    }

    public function testATokenWorksOnlyForItsForm(): void
    {
        $add = $this->tokens->token('add');
        $this->tokens->token('delete');

        self::assertFalse($this->tokens->use('delete', $add));
        self::assertTrue($this->tokens->use('add', $add));
    }

    public function testATokenWorksOnce(): void
    {
        $add = $this->tokens->token('add');

        self::assertTrue($this->tokens->use('add', $add));
        self::assertFalse($this->tokens->use('add', $add));
        self::assertNotSame($add, $this->tokens->token('add'));
    }

    public function testAWrongTokenKeepsTheRightOne(): void
    {
        $add = $this->tokens->token('add');

        self::assertFalse($this->tokens->use('add', 'guessed'));
        self::assertFalse($this->tokens->use('unknown', ''));
        self::assertTrue($this->tokens->use('add', $add));
    }
}
