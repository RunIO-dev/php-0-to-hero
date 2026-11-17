<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use PHPUnit\Framework\TestCase;
use Shelf\Web\Csrf;

final class CsrfTest extends TestCase
{
    public function testATokenIsRandomAndStaysTheSameInOneSession(): void
    {
        $csrf = new Csrf(new ArraySession());
        $other = new Csrf(new ArraySession());

        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $csrf->token());
        self::assertSame($csrf->token(), $csrf->token());
        self::assertNotSame($csrf->token(), $other->token());
    }

    public function testOnlyTheSessionsTokenIsValid(): void
    {
        $csrf = new Csrf(new ArraySession());
        $token = $csrf->token();

        self::assertTrue($csrf->isValid($token));
        self::assertFalse($csrf->isValid(''));
        self::assertFalse($csrf->isValid(strtoupper($token)));
    }

    public function testNothingIsValidBeforeATokenWasMade(): void
    {
        self::assertFalse((new Csrf(new ArraySession()))->isValid(''));
    }
}
