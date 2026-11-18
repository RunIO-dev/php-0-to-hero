<?php

declare(strict_types=1);

// A health check (chapter 78): an address that a monitor, or a load balancer, asks every minute
// whether Shelf can answer. Health::__invoke() asks the database "SELECT 1":
// - it answers: 200, JSON {"status": "ok"};
// - it throws a PDOException: 503, application/problem+json with
//   {"type": "about:blank", "title": "Service Unavailable", "status": 503, "detail": "The database does not answer."}
//   and never the exception's message: it may name the server, the user or the password.
// Both with "Cache-Control: no-store": a cache must never answer for Shelf.

use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class Health
{
    public function __construct(private PDO $db) {}

    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']); // TODO
    }
}
