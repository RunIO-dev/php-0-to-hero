<?php

declare(strict_types=1);

// Routing (symfony/routing): from a method and a path to a route, and back from a route to a path.

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

require __DIR__ . '/vendor/autoload.php';

$routes = new RouteCollection();
$routes->add('books', new Route('/books', methods: ['GET']));
$routes->add('book', new Route('/books/{id}', requirements: ['id' => '\d+'], methods: ['GET', 'DELETE']));

$context = new RequestContext(); // the method, host and scheme of the request; GET by default
$matcher = new UrlMatcher($routes, $context);

print_r($matcher->match('/books/12'));

try {
    $matcher->match('/books/kindred');
} catch (ResourceNotFoundException) {
    echo "/books/kindred: no route, 404\n";
}

$context->setMethod('POST');
try {
    $matcher->match('/books/12');
} catch (MethodNotAllowedException $e) {
    echo 'POST /books/12: 405, Allow: ', implode(', ', $e->getAllowedMethods()), "\n";
}

echo (new UrlGenerator($routes, $context))->generate('book', ['id' => 7]), "\n";
