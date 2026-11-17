<?php

declare(strict_types=1);

namespace Shelf\EventSubscriber;

use Psr\Log\LoggerInterface;
use Shelf\Web\Json;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;
use Twig\Environment;

/**
 * What a visitor or a program gets when a request ends in an exception, as before chapter 73:
 * - under /api/, always problem details (chapter 68), with a reference for an error nothing caught;
 * - for pages, Shelf's own "Not found" for a 404, and the status alone for another HTTP error, such
 *   as 405 with its Allow header;
 * - for an error nothing caught: on a developer's computer (debug), Symfony's page with every detail;
 *   on a server, a short page with a reference that finds the details in the log (chapter 66).
 * Before Symfony's own listeners (priority 10): an answer set here is the one sent.
 */
final readonly class ErrorPages implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
        private LoggerInterface $logger,
        #[Autowire('%kernel.debug%')]
        private bool $debug,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['answer', 10]];
    }

    public function answer(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        if (str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            $event->setResponse($this->problem($e, $event->getRequest()->getPathInfo()));
        } elseif ($e instanceof HttpExceptionInterface) {
            $page = $e->getStatusCode() === Response::HTTP_NOT_FOUND ? $this->twig->render('missing.html.twig') : '';
            $event->setResponse(new Response($page, $e->getStatusCode(), $e->getHeaders()));
        } elseif (!$this->debug) {
            // Plain HTML, without templates: they may be what failed.
            $reference = $this->log($e);
            $event->setResponse(new Response("<!doctype html>\n<title>Error · Shelf</title>\n<h1>Something went wrong</h1>\n"
                . "<p>It has been noted, with the reference $reference. Please try again later.</p>\n", Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    private function problem(Throwable $e, string $path): Response
    {
        if (!$e instanceof HttpExceptionInterface) {
            $reference = $this->log($e);
            $detail = "Something went wrong. It has been noted, with the reference $reference.";

            return Json::problem(500, 'Internal Server Error', $detail, more: ['reference' => $reference, ...$this->debug ? ['exception' => (string) $e] : []]);
        }
        /** @var array<string, string> $headers */
        $headers = $e->getHeaders();
        $detail = match ($e->getStatusCode()) {
            404 => "The API has no address $path.",
            405 => 'Use ' . ($headers['Allow'] ?? '') . '.',
            default => $e->getMessage(),
        };

        return Json::problem($e->getStatusCode(), Response::$statusTexts[$e->getStatusCode()] ?? 'Error', $detail, headers: $headers);
    }

    private function log(Throwable $e): string
    {
        $reference = bin2hex(random_bytes(4));
        $this->logger->error('Uncaught {class}, reference {reference}', ['class' => $e::class, 'reference' => $reference, 'exception' => $e]);

        return $reference;
    }
}
