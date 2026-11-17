<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shelf\Tests\ArrayLogger;
use Shelf\Web\Response;

final class ResponseTest extends TestCase
{
    public function testAnErrorShowsLittleAndLogsMuch(): void
    {
        $logger = new ArrayLogger();

        $response = Response::error(new RuntimeException('no such table: books'), $logger, showDetails: false);

        self::assertSame(500, $response->status);
        self::assertStringNotContainsString('no such table', $response->body);
        self::assertMatchesRegularExpression('/reference ([0-9a-f]{8})\./', $response->body);
        preg_match('/reference ([0-9a-f]{8})\./', $response->body, $m);
        self::assertSame(['error Uncaught RuntimeException, reference ' . ($m[1] ?? '')], $logger->lines);
    }

    public function testADevelopersComputerShowsTheDetailsEscaped(): void
    {
        $response = Response::error(new RuntimeException('<b>no such table</b>'), new ArrayLogger(), showDetails: true);

        self::assertStringContainsString('RuntimeException: &lt;b&gt;no such table&lt;/b&gt;', $response->body);
    }

    public function testJsonIsReadableWithSlashesAndLettersAsTheyAre(): void
    {
        $response = Response::json(['title' => 'Cien años de soledad', 'cover' => '/cover?title=Kitchen'], 201);

        self::assertSame(201, $response->status);
        self::assertSame(['Content-Type' => 'application/json', 'X-Content-Type-Options' => 'nosniff'], $response->headers);
        self::assertSame("{\n    \"title\": \"Cien años de soledad\",\n    \"cover\": \"/cover?title=Kitchen\"\n}\n", $response->body);
    }
}
