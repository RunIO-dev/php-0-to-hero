<?php

declare(strict_types=1);

namespace Shelf\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Chapter 64's headers, now on every answer: nosniff, so a browser takes each one as the type it says;
 * and for pages, the content security policy. Shelf has no JavaScript, so no script may run at all;
 * styles, images and forms only from Shelf itself; no other site may show it in a frame.
 */
final class SecurityHeaders implements EventSubscriberInterface
{
    public const array FOR_PAGES = [
        'Content-Security-Policy' => "default-src 'self'; script-src 'none'; frame-ancestors 'none'; form-action 'self'; base-uri 'none'",
        'Referrer-Policy' => 'same-origin',
    ];

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'add'];
    }

    public function add(ResponseEvent $event): void
    {
        $headers = $event->getResponse()->headers;
        $headers->set('X-Content-Type-Options', 'nosniff');
        if (str_starts_with((string) $headers->get('Content-Type'), 'text/html')) {
            $headers->add(self::FOR_PAGES);
        }
    }
}
