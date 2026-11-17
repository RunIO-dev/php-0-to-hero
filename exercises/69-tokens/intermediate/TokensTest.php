<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'tokens.php');

final class TokensTest extends TestCase
{
    public function testATokenIs32RandomBytes(): void
    {
        $tokens = array_column([newToken(), newToken(), newToken()], 'token');

        foreach ($tokens as $token) {
            self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token, 'bin2hex(random_bytes(32))');
        }
        self::assertCount(3, array_unique($tokens), 'Every token is new');
    }

    public function testOnlyItsHashIsKept(): void
    {
        $new = newToken();

        self::assertSame(hash('sha256', $new['token']), $new['hash']);
        self::assertStringNotContainsString($new['token'], $new['hash']);
    }

    public function testATokenFindsItsMemberByItsHash(): void
    {
        $amira = newToken();
        $jonas = newToken();
        $hashes = [$amira['hash'] => 'Amira', $jonas['hash'] => 'Jonas'];

        self::assertSame('Amira', memberFor($amira['token'], $hashes));
        self::assertSame('Jonas', memberFor($jonas['token'], $hashes));
        self::assertNull(memberFor(newToken()['token'], $hashes));
    }

    public function testTheHashItselfIsNotAToken(): void
    {
        $amira = newToken();

        self::assertNull(memberFor($amira['hash'], [$amira['hash'] => 'Amira']), 'Whoever reads the database must not be able to use what it holds');
    }
}
