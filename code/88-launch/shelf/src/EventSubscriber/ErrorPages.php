<?php

declare(strict_types=1);

namespace Shelf\EventSubscriber;

use Psr\Log\LoggerInterface;
use Shelf\Web\Json;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\LogoutException;
use Symfony\Component\Translation\LocaleSwitcher;
use Throwable;
use Twig\Environment;

/**
 * What a visitor or a program gets when a request ends in an exception, as before chapter 73:
 * - under /api/, always problem details (chapter 68), with a reference for an error nothing caught;
 * - for pages, Shelf's own "Not found" for a 404, "Not understood" for a 400 (chapter 81), "Slow
 *   down" for a 429 (chapter 82), "Not
 *   allowed" for a 403 (chapter 76: a voter said no, which goes into the log), and the status alone for another HTTP error, such as 405 with its
 *   Allow header;
 * - for an error nothing caught: on a developer's computer (debug), Symfony's page with every detail;
 *   on a server, a short page with a reference that finds the details in the log (chapter 66), from
 *   chapter 88 in Shelf's layout and the page's language.
 * Chapter 87: in the language of the address, also when no route matched it.
 * After the security's listener (priority 1), which sends a visitor who isn't logged in to the login,
 * and before Symfony's error page (priority -128): an answer set here is the one sent.
 */
final readonly class ErrorPages implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
        private LoggerInterface $logger,
        private Security $security,
        private LocaleSwitcher $locales,
        #[Autowire('%kernel.debug%')]
        private bool $debug,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['answer', -1]];
    }

    public function answer(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        // Chapter 87: an address that matched no route has no language yet; /de/nothing is German.
        if (preg_match('~^/(de|ar)(/|$)~', $event->getRequest()->getPathInfo(), $m) === 1) {
            $event->getRequest()->setLocale($m[1]);
            $this->locales->setLocale($m[1]);
        }
        if (str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            $event->setResponse($this->problem($e, $event->getRequest()->getPathInfo()));
        } elseif ($e->getPrevious() instanceof LogoutException) {
            // A logout without the right token (chapter 76): a link or another site's form, never the button.
            $event->setResponse(new Response($this->twig->render('expired.html.twig'), Response::HTTP_FORBIDDEN));
        } elseif ($e instanceof HttpExceptionInterface) {
            $event->setResponse(new Response($this->page($e->getStatusCode(), $event->getRequest()), $e->getStatusCode(), $e->getHeaders()));
        } elseif (!$this->debug) {
            $event->setResponse(new Response($this->failed($this->log($e)), Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    private function page(int $status, Request $request): string
    {
        if ($status === Response::HTTP_FORBIDDEN) {
            $this->logger->warning('{name} was refused {path}', ['name' => $this->security->getUser()?->getUserIdentifier(), 'path' => $request->getPathInfo()]);

            return $this->twig->render('forbidden.html.twig');
        }

        return match ($status) {
            Response::HTTP_NOT_FOUND => $this->twig->render('missing.html.twig'),
            Response::HTTP_BAD_REQUEST => $this->twig->render('unclear.html.twig'), // chapter 81
            Response::HTTP_TOO_MANY_REQUESTS => $this->twig->render('slow-down.html.twig'), // chapter 82
            default => '',
        };
    }

    /**
     * Chapter 88: Shelf's own page, in the page's language, with the layout. If that fails too (the
     * error may be in the templates, or the database the layout asks), plain HTML, without templates.
     */
    private function failed(string $reference): string
    {
        try {
            return $this->twig->render('error.html.twig', ['reference' => $reference]);
        } catch (Throwable) {
            return "<!doctype html>\n<title>Error · Shelf</title>\n<h1>Something went wrong</h1>\n"
                . "<p>It has been noted, with the reference $reference. Please try again later.</p>\n";
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
