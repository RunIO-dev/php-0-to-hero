<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FilterTest extends TestCase
{
    private function get(string $query): array
    {
        [$response] = responses_of(dirname(subject(__DIR__, 'filter.php')), "GET /filter.php?$query\n");

        return $response;
    }

    public function testBothFieldsFilter(): void
    {
        $body = $this->get('q=ki&before=1985')['body'];

        self::assertStringContainsString('<li>Kindred (1979)</li>', $body);
        self::assertStringNotContainsString('Kitchen', $body);
        self::assertStringNotContainsString('Sower', $body);
    }

    public function testEmptyFieldsShowEverything(): void
    {
        self::assertSame(4, substr_count($this->get('q=&before=')['body'], '<li>'));
    }

    public function testTheFieldsKeepWhatWasTypedEscaped(): void
    {
        $body = $this->get('q=%22%3Ex&before=1990')['body'];

        self::assertStringContainsString('value="&quot;&gt;x"', $body);
        self::assertStringContainsString('value="1990"', $body);
    }

    public function testAWrongYearIs400WithoutBooks(): void
    {
        $response = $this->get('q=&before=soon');

        self::assertSame(400, $response['status']);
        self::assertStringContainsString('<p class="error">The year must be a number from 1 to 2100.</p>', $response['body']);
        self::assertStringNotContainsString('<li>', $response['body']);
        self::assertStringContainsString('value="soon"', $response['body']);
    }
}
