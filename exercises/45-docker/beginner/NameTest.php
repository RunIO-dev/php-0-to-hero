<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class NameTest extends TestCase
{
    public function testTheNameComesFromTheEnvironment(): void
    {
        $dir = dirname(subject(__DIR__, 'name.php'));

        self::assertSame("Welcome to North Library\n", responses_of($dir, "# env LIBRARY_NAME=North Library\nGET /name.php\n")[0]['body']);
        self::assertSame("Welcome to Shelf\n", responses_of($dir, "GET /name.php\n")[0]['body']);
        self::assertSame("Welcome to Shelf\n", responses_of($dir, "# env LIBRARY_NAME=\nGET /name.php\n")[0]['body']);
    }
}
