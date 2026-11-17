<?php

declare(strict_types=1);

namespace Shelf\Web;

use Shelf\Library;
use Shelf\Member;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The member who is logged in (chapter 62), from the session's name, read again on every request:
 * a member who was removed, or lost a role, loses it at once. The templates know it as "current".
 * Chapter 76 hands this to Symfony's security.
 */
final readonly class CurrentMember
{
    public function __construct(private RequestStack $requests, private Library $library) {}

    public function member(): ?Member
    {
        $request = $this->requests->getMainRequest();
        // A visitor without a session cookie has no session yet; asking for one would start it.
        if ($request === null || !$request->hasPreviousSession()) {
            return null;
        }
        $name = $request->getSession()->get('member');

        return is_string($name) ? $this->library->findMember($name) : null;
    }
}
