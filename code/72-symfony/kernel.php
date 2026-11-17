<?php

declare(strict_types=1);

// The loop every Symfony app runs (symfony/http-kernel): a request goes in, events let listeners
// act, the router finds the controller, and a response comes out.

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

require __DIR__ . '/vendor/autoload.php';

$routes = new RouteCollection();
$routes->add('book', new Route('/books/{id}', [
    '_controller' => fn(int $id): Response => new Response("Book $id"), // {id} becomes $id
], ['id' => '\d+']));

$requests = new RequestStack();
$events = new EventDispatcher();
$events->addSubscriber(new RouterListener(new UrlMatcher($routes, new RequestContext()), $requests));
$events->addListener(KernelEvents::EXCEPTION, function (ExceptionEvent $event): void {
    $e = $event->getThrowable();
    $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
    $event->setResponse(new Response(Response::$statusTexts[$status], $status));
});
$kernel = new HttpKernel($events, new ControllerResolver(), $requests, new ArgumentResolver());

foreach (['/books/12', '/books/kindred'] as $path) {
    $response = $kernel->handle(Request::create($path));
    echo "GET $path: ", $response->getStatusCode(), ' ', $response->getContent(), "\n";
}
