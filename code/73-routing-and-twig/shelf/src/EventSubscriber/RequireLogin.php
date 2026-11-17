<?php

declare(strict_types=1);

namespace Shelf\EventSubscriber;

use Psr\Log\LoggerInterface;
use Shelf\Web\CurrentMember;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * Chapter 63's check, before every request's controller runs: a visitor who isn't logged in is sent
 * to the login, a member without the role gets 403. After the router (priority 32), so it knows the
 * route. Chapter 76 hands this to Symfony's security.
 */
final readonly class RequireLogin implements EventSubscriberInterface
{
    /** Routes for members only; the ones in STAFF_ONLY need a role that may change books. */
    private const array MEMBERS_ONLY = ['loans', 'add', 'copies', 'remove'];

    private const array STAFF_ONLY = ['add', 'copies', 'remove'];

    public function __construct(private CurrentMember $current, private Environment $twig, private LoggerInterface $logger) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['check', 8]];
    }

    public function check(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        if (!$event->isMainRequest() || !in_array($route, self::MEMBERS_ONLY, true)) {
            return;
        }
        $member = $this->current->member();
        if ($member === null) {
            $session = $request->getSession();
            if ($session instanceof FlashBagAwareSessionInterface) {
                $session->getFlashBag()->add('notice', 'Log in first.');
            }
            $event->setResponse(new RedirectResponse('/login', Response::HTTP_SEE_OTHER));
        } elseif (in_array($route, self::STAFF_ONLY, true) && !$member->role->mayChangeBooks()) {
            $this->logger->warning('{name} was refused {path}', ['name' => $member->name, 'path' => $request->getPathInfo()]);
            $event->setResponse(new Response($this->twig->render('forbidden.html.twig'), Response::HTTP_FORBIDDEN));
        }
    }
}
