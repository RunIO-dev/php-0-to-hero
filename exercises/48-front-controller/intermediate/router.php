<?php

declare(strict_types=1);

// Exercise 48, intermediate: a Router class.
//
// add($method, $path, $handler) registers a route; $handler returns the body of the answer.
// dispatch($method, $uri) finds the route and returns status, body and allow:
// - a route for the path and the method: 200, what the handler returns, allow [];
// - no route for the path: 404, 'Not found', allow [];
// - the path exists, but not for this method: 405, 'Method not allowed', and allow lists the
//   methods the path takes, in the order they were added.
// The query string of $uri is not part of the path.

final class Router
{
    /** @param Closure(): string $handler */
    public function add(string $method, string $path, Closure $handler): void {}

    /** @return array{status: int, body: string, allow: list<string>} */
    public function dispatch(string $method, string $uri): array
    {
        return ['status' => 200, 'body' => '', 'allow' => []];
    }
}
// Check your work: composer exercise 48 intermediate
