<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TableTest extends TestCase
{
    public function testEveryBookIsARowWithEscapedValues(): void
    {
        [$page] = responses_of(dirname(subject(__DIR__, 'table.php')), "GET /table.php\n");
        $html = $page['body'];

        self::assertSame(3, substr_count($html, '<tr'));
        self::assertStringContainsString('<td>Tom &amp; Jerry</td><td>1940</td>', $html);
        self::assertStringContainsString('title="The &quot;Quiet&quot; American"', $html);
        self::assertStringContainsString('<td>Butler&#039;s &lt;i&gt;Kindred&lt;/i&gt;</td>', $html);
        self::assertStringNotContainsString('<i>', $html);
    }

    public function testTheRowsComeFromTheLoop(): void
    {
        $code = (string) file_get_contents(subject(__DIR__, 'table.php'));

        self::assertStringContainsString('endforeach', $code, 'Use foreach (...): with endforeach;');
    }
}
