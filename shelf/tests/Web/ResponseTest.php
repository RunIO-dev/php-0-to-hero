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

    public function testAProblemHasTheShapeOfProblemDetails(): void
    {
        $response = Response::problem(422, 'The book is not valid', 'One field is wrong.', '/problems/invalid-book', ['errors' => [['field' => 'year']]]);

        self::assertSame(422, $response->status);
        self::assertSame('application/problem+json', $response->headers['Content-Type'] ?? null);
        self::assertSame(
            ['type' => '/problems/invalid-book', 'title' => 'The book is not valid', 'status' => 422, 'detail' => 'One field is wrong.', 'errors' => [['field' => 'year']]],
            json_decode($response->body, true, flags: JSON_THROW_ON_ERROR),
        );
    }

    public function testAnErrorInTheApiIsAProblemWithTheReference(): void
    {
        $logger = new ArrayLogger();

        $response = Response::error(new RuntimeException('no such table: books'), $logger, showDetails: false, api: true);
        $problem = json_decode($response->body, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame([500, 'application/problem+json'], [$response->status, $response->headers['Content-Type'] ?? null]);
        self::assertIsArray($problem);
        self::assertSame(['about:blank', 'Internal Server Error', 500], [$problem['type'] ?? null, $problem['title'] ?? null, $problem['status'] ?? null]);
        self::assertIsString($problem['reference'] ?? null);
        self::assertSame(['error Uncaught RuntimeException, reference ' . $problem['reference']], $logger->lines);
        self::assertStringNotContainsString('no such table', $response->body);
    }

    public function testWithHeaderAddsOneHeader(): void
    {
        $response = Response::json([])->withHeader('Allow', 'GET');

        self::assertSame(['Content-Type' => 'application/json', 'X-Content-Type-Options' => 'nosniff', 'Allow' => 'GET'], $response->headers);
    }
}
