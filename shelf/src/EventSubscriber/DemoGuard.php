<?php

declare(strict_types=1);

namespace Shelf\EventSubscriber;

use Shelf\Demo;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * What the demo doesn't do (chapter 88). Signing up and new passwords send emails, to any address a
 * visitor types: the demo would send mail for strangers. A visitor who changed the password of a
 * shared account, deleted it, or blocked it would lock every later visitor out. All of these get a
 * page that says so, with status 403. After CheckCsrf (priority 4): a forged form is refused first.
 */
final readonly class DemoGuard implements EventSubscriberInterface
{
    /** Routes that send emails, or change the account that is logged in. */
    private const array CLOSED = ['signup', 'confirm', 'forgot', 'reset', 'account_password', 'account_email', 'account_delete'];

    /** Routes of the desk that change another member, named by the form's "member". */
    private const array MEMBER = ['members_block', 'members_role'];

    public function __construct(private Demo $demo, private Environment $twig) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['check', 2]];
    }

    public function check(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->demo->on || !$event->isMainRequest()) {
            return;
        }
        // A route in three languages (chapter 87) is signup.en, signup.de and signup.ar: one canonical name.
        $route = $request->attributes->getString('_canonical_route') ?: $request->attributes->getString('_route');
        if (in_array($route, self::CLOSED, true)
            || (in_array($route, self::MEMBER, true) && $this->demo->isShared($request->request->getString('member')))) {
            $event->setResponse(new Response($this->twig->render('demo.html.twig'), Response::HTTP_FORBIDDEN));
        }
    }
}
