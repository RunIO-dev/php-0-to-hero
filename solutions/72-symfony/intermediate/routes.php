<?php

declare(strict_types=1);

// The library's addresses, as Symfony routes:
// - books        GET    /books
// - book         GET    /books/{id}            id: digits only
// - lend         POST   /books/{id}/loans      id: digits only
// - member_loans GET    /members/{name}/loans  name: lower-case letters and hyphens, such as amira or ana-lucia

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

function libraryRoutes(): RouteCollection
{
    $routes = new RouteCollection();
    $routes->add('books', new Route('/books', methods: ['GET']));
    $routes->add('book', new Route('/books/{id}', requirements: ['id' => '\d+'], methods: ['GET']));
    $routes->add('lend', new Route('/books/{id}/loans', requirements: ['id' => '\d+'], methods: ['POST']));
    $routes->add('member_loans', new Route('/members/{name}/loans', requirements: ['name' => '[a-z]+(-[a-z]+)*'], methods: ['GET']));

    return $routes;
}
