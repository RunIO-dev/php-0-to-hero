<?php

declare(strict_types=1);

namespace Shelf\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

/**
 * Chapter 51's rule: every POST of a page must carry the session's CSRF token, which the templates
 * put into each form with csrf_token('shelf'). The API takes no cookies, only tokens (chapter 69),
 * so it needs none. Chapter 76: after the firewall (priority 8), which checks the token of the login
 * and the logout itself; the forms of chapter 75 check theirs too.
 */
final readonly class CheckCsrf implements EventSubscriberInterface
{
    public function __construct(private CsrfTokenManagerInterface $tokens, private Environment $twig) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['check', 4]];
    }

    public function check(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !$request->isMethod('POST') || str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }
        if (!$this->tokens->isTokenValid(new CsrfToken('shelf', $request->request->getString('_token')))) {
            $event->setResponse(new Response($this->twig->render('expired.html.twig'), Response::HTTP_FORBIDDEN));
        }
    }
}
