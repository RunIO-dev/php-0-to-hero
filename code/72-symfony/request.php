<?php

declare(strict_types=1);

// Symfony's Request and Response (symfony/http-foundation): chapter 48's, as a package.

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require __DIR__ . '/vendor/autoload.php';

// A page reads the real request with Request::createFromGlobals(); an example or a test makes one.
$request = Request::create('/book?title=Kindred', 'POST', ['note' => 'Signed copy'], server: [
    'HTTP_AUTHORIZATION' => 'Bearer 8a5770ac',
]);

echo $request->getMethod(), ' ', $request->getPathInfo(), "\n";
echo 'title: ', $request->query->getString('title'), "\n";        // $_GET
echo 'note: ', $request->request->getString('note'), "\n";        // $_POST
echo 'page: ', $request->query->getInt('page', 1), "\n";          // with a default, as a number
echo 'token: ', $request->headers->get('Authorization'), "\n";

$response = new Response('<h1>Kindred</h1>', Response::HTTP_CREATED, ['Location' => '/book?title=Kindred']);
$response->headers->set('X-Content-Type-Options', 'nosniff');

echo $response->getStatusCode(), ' ', Response::$statusTexts[$response->getStatusCode()], "\n";
echo 'Location: ', $response->headers->get('Location'), "\n";
echo $response->getContent(), "\n";
