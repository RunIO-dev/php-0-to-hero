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
    $routes->add('books', new Route('/books'));
    $routes->add('book', new Route('/books/{id}'));
    // TODO: the methods and requirements above, and the routes lend and member_loans.

    return $routes;
}
