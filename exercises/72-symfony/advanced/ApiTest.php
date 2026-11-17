<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once subject(__DIR__, 'api.php');

final class ApiTest extends TestCase
{
    public function testTheListIsJson(): void
    {
        $response = $this->send('GET', '/books');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('Content-Type'));
        self::assertSame(['Kindred', 'Things Fall Apart'], array_column($this->json($response), 'title'));
    }

    public function testOneBook(): void
    {
        $response = $this->send('GET', '/books/2');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['id' => 2, 'title' => 'Things Fall Apart', 'year' => 1958], $this->json($response));
    }

    public function testAnUnknownBookIsAProblem(): void
    {
        $response = $this->send('GET', '/books/9');

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('application/problem+json', $response->headers->get('Content-Type'));
        self::assertSame(['type' => 'about:blank', 'title' => 'Not Found', 'status' => 404, 'detail' => 'There is no book 9.'], $this->json($response));
    }

    public function testAnUnknownAddressIsAProblem(): void
    {
        $response = $this->send('GET', '/members');

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('application/problem+json', $response->headers->get('Content-Type'));
        self::assertSame(['type' => 'about:blank', 'title' => 'Not Found', 'status' => 404], array_slice($this->json($response), 0, 3));
    }

    public function testAWrongMethodNamesTheRightOnes(): void
    {
        $response = $this->send('DELETE', '/books/1');

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('GET', $response->headers->get('Allow'));
        self::assertSame('application/problem+json', $response->headers->get('Content-Type'));
        self::assertSame('Method Not Allowed', $this->json($response)['title'] ?? null);
    }

    private function send(string $method, string $path): Response
    {
        return libraryApi()->handle(Request::create($path, $method));
    }

    /** @return array<mixed> */
    private function json(Response $response): array
    {
        $data = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }
}
