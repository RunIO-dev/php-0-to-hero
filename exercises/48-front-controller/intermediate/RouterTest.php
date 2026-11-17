<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'router.php');

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        $this->router->add('GET', '/books', fn(): string => 'the list');
        $this->router->add('GET', '/add', fn(): string => 'the form');
        $this->router->add('POST', '/add', fn(): string => 'added');
    }

    public function testARouteRunsItsHandler(): void
    {
        self::assertSame(['status' => 200, 'body' => 'the list', 'allow' => []], $this->router->dispatch('GET', '/books'));
        self::assertSame('added', $this->router->dispatch('POST', '/add')['body']);
    }

    public function testTheQueryStringIsNotPartOfThePath(): void
    {
        self::assertSame('the list', $this->router->dispatch('GET', '/books?sort=year')['body']);
    }

    public function testAnUnknownPathIs404(): void
    {
        self::assertSame(['status' => 404, 'body' => 'Not found', 'allow' => []], $this->router->dispatch('GET', '/members'));
    }

    public function testAWrongMethodIs405WithTheAllowedMethods(): void
    {
        self::assertSame(['status' => 405, 'body' => 'Method not allowed', 'allow' => ['GET', 'POST']], $this->router->dispatch('DELETE', '/add'));
        self::assertSame(['GET'], $this->router->dispatch('POST', '/books')['allow']);
    }
}
