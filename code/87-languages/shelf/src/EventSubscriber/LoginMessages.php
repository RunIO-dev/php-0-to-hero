<?php

declare(strict_types=1);

namespace Shelf\EventSubscriber;

use Psr\Log\LoggerInterface;
use Shelf\Member;
use Shelf\MemberRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Translation\TranslatableMessage;

use function Symfony\Component\Translation\t;

/**
 * What chapter 62's login said and logged, now that Symfony's security logs members in and out:
 * a welcome, a goodbye, and a line in the log for every login, right or wrong (chapter 66).
 * Chapter 87: in the page's language; and a login remembers it, for the member's emails.
 */
final readonly class LoginMessages implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger, private MemberRepository $members) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'welcome',
            LoginFailureEvent::class => 'refused',
            LogoutEvent::class => ['goodbye', -100], // after Symfony has ended the session
        ];
    }

    public function welcome(LoginSuccessEvent $event): void
    {
        $member = $event->getUser();
        if ($event->getFirewallName() !== 'main' || !$member instanceof Member) {
            return; // the API's tokens log in on every request; the API logs its own logins
        }
        $this->logger->info('{name} logged in', ['name' => $member->name]);
        // Chapter 87: Shelf writes to the member in the language they logged in with.
        $language = $event->getRequest()->getLocale();
        if ($member->language !== $language) {
            $member->speaks($language);
            $this->members->update($member);
        }
        self::flash($event->getRequest(), t('Welcome, {name}.', ['name' => $member->name]));
    }

    public function refused(LoginFailureEvent $event): void
    {
        if ($event->getFirewallName() === 'main') {
            $this->logger->warning('Failed login for {name}', ['name' => trim($event->getRequest()->request->getString('name'))]); // never the password
        }
    }

    public function goodbye(LogoutEvent $event): void
    {
        self::flash($event->getRequest(), t('You are logged out.'));
    }

    private static function flash(Request $request, TranslatableMessage $message): void
    {
        $session = $request->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('notice', $message);
        }
    }
}
