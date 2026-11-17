<?php

declare(strict_types=1);

// A small books API on Symfony's HttpKernel, without the framework around it:
// - GET /books: 200, the list as JSON; GET /books/{id}: 200, one book as JSON.
// - A book that doesn't exist: 404, detail "There is no book 9."
// - Every error, also an unknown address (404) or a wrong method (405, with its Allow header), is
//   answered as problem details (chapter 68): Content-Type application/problem+json, and
//   {"type": "about:blank", "title": <the status text>, "status": <the code>, "detail": <the message>}.

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

const BOOKS = [
    1 => ['id' => 1, 'title' => 'Kindred', 'year' => 1979],
    2 => ['id' => 2, 'title' => 'Things Fall Apart', 'year' => 1958],
];

function libraryApi(): HttpKernel
{
    $routes = new RouteCollection();
    $routes->add('books', new Route('/books', ['_controller' => fn(): Response => new JsonResponse(array_values(BOOKS))], methods: ['GET']));
    $routes->add('book', new Route('/books/{id}', ['_controller' => fn(int $id): Response => new JsonResponse(BOOKS[$id] ?? throw new NotFoundHttpException("There is no book $id."))], ['id' => '\d+'], methods: ['GET']));

    $requests = new RequestStack();
    $events = new EventDispatcher();
    $events->addSubscriber(new RouterListener(new UrlMatcher($routes, new RequestContext()), $requests));
    $events->addListener(KernelEvents::EXCEPTION, function (ExceptionEvent $event): void {
        $e = $event->getThrowable();
        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
        $headers = ['Content-Type' => 'application/problem+json', ...($e instanceof HttpExceptionInterface ? $e->getHeaders() : [])];
        $problem = ['type' => 'about:blank', 'title' => Response::$statusTexts[$status], 'status' => $status, 'detail' => $e->getMessage()];
        $event->setResponse(new JsonResponse($problem, $status, $headers));
    });

    return new HttpKernel($events, new ControllerResolver(), $requests, new ArgumentResolver());
}
