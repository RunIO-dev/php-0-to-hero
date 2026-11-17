<?php

declare(strict_types=1);

// Bug: a title of exactly 100 letters is refused.

// A page where readers suggest books for the library to buy: a small application that turns a Request
// into a Response, as Symfony's Kernel does, so a browser for tests can use it (new HttpKernelBrowser($app)).
//
// - GET /: 200, the page "Suggestions": every suggestion in an <li>, and a form with a field "title", a
//   hidden "_token" and the button "Suggest". The token is also in the cookie "csrf", set on the first visit.
// - POST /: the form. A missing or wrong token (not the cookie's): 403, nothing added. An empty title:
//   422 and "Name a book." in a <p class="error">; more than 100 letters: 422 and "At most 100 letters.";
//   both keep what was typed in the field. Otherwise the suggestion is added, and 303 to /.
// - Any other method on /: 405 with "Allow: GET, POST". Any other path: 404.
// Every suggestion is text, never HTML: it is escaped on the page.

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class Suggestions implements HttpKernelInterface
{
    /** @var list<string> */
    private array $titles = ['Kindred'];

    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        if ($request->getPathInfo() !== '/') {
            return new Response('<h1>Not found</h1>', 404);
        }
        $token = $request->cookies->getString('csrf');
        if ($request->isMethod('GET')) {
            return $this->page($token, '', '');
        }
        if (!$request->isMethod('POST')) {
            return new Response('', 405, ['Allow' => 'GET, POST']);
        }
        if ($token === '' || !hash_equals($token, $request->request->getString('_token'))) {
            return new Response('<h1>This form has expired</h1>', 403);
        }
        $title = trim($request->request->getString('title'));
        $error = match (true) {
            $title === '' => 'Name a book.',
            mb_strlen($title) >= 100 => 'At most 100 letters.',
            default => '',
        };
        if ($error !== '') {
            return $this->page($token, $title, $error);
        }
        $this->titles[] = $title;

        return new RedirectResponse('/', 303);
    }

    private function page(string $token, string $title, string $error): Response
    {
        $new = $token === '';
        $token = $new ? bin2hex(random_bytes(16)) : $token;
        $e = static fn(string $text): string => htmlspecialchars($text, ENT_QUOTES);
        $items = implode('', array_map(static fn(string $t): string => '<li>' . $e($t) . '</li>', $this->titles));
        $response = new Response(
            "<!DOCTYPE html><title>Suggestions</title><h1>Suggestions</h1><ul>$items</ul>"
            . ($error === '' ? '' : '<p class="error">' . $e($error) . '</p>')
            . '<form method="post" action="/"><input type="hidden" name="_token" value="' . $e($token) . '">'
            . '<input name="title" value="' . $e($title) . '"><button>Suggest</button></form>',
            $error === '' ? 200 : 422,
        );
        if ($new) {
            $response->headers->setCookie(Cookie::create('csrf', $token)->withSecure(false)->withHttpOnly(true)->withSameSite('lax'));
        }

        return $response;
    }
}
