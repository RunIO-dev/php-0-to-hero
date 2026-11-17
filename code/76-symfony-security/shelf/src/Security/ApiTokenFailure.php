<?php

declare(strict_types=1);

namespace Shelf\Security;

use Shelf\Web\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/** A token that was sent, but isn't valid: 401 as problem details (chapter 68), as before. */
final class ApiTokenFailure implements AuthenticationFailureHandlerInterface
{
    public const string DETAIL = 'Send a valid token: Authorization: Bearer <token>.';

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return Json::problem(401, 'Unauthorized', self::DETAIL, headers: ['WWW-Authenticate' => 'Bearer']);
    }
}
