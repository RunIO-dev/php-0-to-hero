<?php

declare(strict_types=1);

// Exercise 48, intermediate: a Router class.

final class Router
{
    /** @var array<string, array<string, Closure(): string>> path => method => handler */
    private array $routes = [];

    /** @param Closure(): string $handler */
    public function add(string $method, string $path, Closure $handler): void
    {
        $this->routes[$path][$method] = $handler;
    }

    /** @return array{status: int, body: string, allow: list<string>} */
    public function dispatch(string $method, string $uri): array
    {
        $route = $this->routes[(string) parse_url($uri, PHP_URL_PATH)] ?? null;
        if ($route === null) {
            return ['status' => 404, 'body' => 'Not found', 'allow' => []];
        }
        if (!isset($route[$method])) {
            return ['status' => 405, 'body' => 'Method not allowed', 'allow' => array_keys($route)];
        }

        return ['status' => 200, 'body' => $route[$method](), 'allow' => []];
    }
}
