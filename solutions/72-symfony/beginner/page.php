<?php

declare(strict_types=1);

// A book's page, with Symfony's Request and Response instead of $_GET and echo.
// - GET /book?title=Kindred: 200, "<h1>Kindred</h1>", the title escaped for HTML.
// - No title, or only spaces: 400, "<p>Which book?</p>".
// - Every answer has the header X-Content-Type-Options: nosniff.

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

function bookPage(Request $request): Response
{
    $title = trim($request->query->getString('title'));
    $response = $title === ''
        ? new Response('<p>Which book?</p>', Response::HTTP_BAD_REQUEST)
        : new Response('<h1>' . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h1>');
    $response->headers->set('X-Content-Type-Options', 'nosniff');

    return $response;
}
