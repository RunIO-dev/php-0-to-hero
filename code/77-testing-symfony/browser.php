<?php

declare(strict_types=1);

// A browser for tests (symfony/browser-kit): it sends requests straight to the application's
// handle(), without a server, keeps cookies, follows redirects and fills in forms.
// WebTestCase's KernelBrowser is this browser, for a Symfony Kernel.

use Symfony\Component\HttpKernel\HttpKernelBrowser;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Guestbook.php';

$browser = new HttpKernelBrowser(new Guestbook());

$page = $browser->request('GET', '/');
echo $browser->getResponse()->getStatusCode(), ' ', $page->filter('h1')->text(), "\n";

// The form as a browser would send it: the button's form, its fields, its method and address.
$browser->submitForm('Sign', ['message' => 'Quiet & warm.']);
echo $browser->getResponse()->getStatusCode(), ' to ', $browser->getResponse()->headers->get('Location'), "\n";

$page = $browser->followRedirect();
echo implode(' | ', $page->filter('li')->each(static fn($li): string => $li->text())), "\n";

$page = $browser->submitForm('Sign', ['message' => '   ']);
echo $browser->getResponse()->getStatusCode(), ' ', $page->filter('.error')->text(), "\n";

$browser->request('GET', '/admin');
echo $browser->getResponse()->getStatusCode(), "\n";
