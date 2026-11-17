<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SiteTest extends TestCase
{
    public function testEveryPathGetsItsPageOr404(): void
    {
        [$home, $about, $opening, $missing] = responses_of(
            dirname(subject(__DIR__, 'site.php')),
            "# router site.php\nGET /\n\n###\nGET /about?x=1\n\n###\nGET /opening\n\n###\nGET /admin\n",
        );

        self::assertSame([200, "Home\n"], [$home['status'], $home['body']]);
        self::assertSame([200, "About us\n"], [$about['status'], $about['body']]);
        self::assertSame([200, "Monday to Friday, 9 to 18\n"], [$opening['status'], $opening['body']]);
        self::assertSame([404, "Not found\n"], [$missing['status'], $missing['body']]);
    }
}
