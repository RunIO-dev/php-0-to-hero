<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'guestbook.php');

final class GuestbookTest extends TestCase
{
    public function testAnEntryIsEscaped(): void
    {
        self::assertSame('<li><b>Amira</b>: Loved &quot;Kindred&quot;</li>', entryHtml('Amira', 'Loved "Kindred"'));
        self::assertSame('<li><b>&lt;script&gt;x()&lt;/script&gt;</b>: &lt;img src=x onerror=y()&gt;</li>', entryHtml('<script>x()</script>', '<img src=x onerror=y()>'));
    }

    public function testAfterLoginStaysOnTheSite(): void
    {
        self::assertSame('/loans', afterLogin('/loans'));
        self::assertSame('/book?title=Sula', afterLogin('/book?title=Sula'));
        foreach (['https://stranger.example', '//stranger.example', '/\\stranger.example', 'loans', ''] as $next) {
            self::assertSame('/', afterLogin($next), $next);
        }
    }
}
