<?php

declare(strict_types=1);

// A very small application for the examples: a guestbook page with a form. Any class that turns a
// Request into a Response, as Symfony's Kernel does (chapter 72), can be tested with a browser.

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class Guestbook implements HttpKernelInterface
{
    /** @var list<string> the messages, as long as this object lives */
    private array $messages = ['Lovely reading room.'];

    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        if ($request->getPathInfo() !== '/') {
            return new Response('<h1>Not found</h1>', 404);
        }
        $error = '';
        if ($request->isMethod('POST')) {
            $message = trim($request->request->getString('message'));
            if ($message !== '') {
                $this->messages[] = $message;

                return new RedirectResponse('/', 303);
            }
            $error = '<p class="error">Write a message first.</p>';
        }
        $items = implode('', array_map(static fn(string $m): string => '<li>' . htmlspecialchars($m) . '</li>', $this->messages));

        return new Response(
            "<h1>Guestbook</h1><ul>$items</ul>$error"
            . '<form method="post"><textarea name="message"></textarea><button>Sign</button></form>',
            $error === '' ? 200 : 422,
        );
    }
}
