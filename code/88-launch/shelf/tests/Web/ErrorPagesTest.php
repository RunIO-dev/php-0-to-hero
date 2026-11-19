<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use RuntimeException;
use Throwable;
use Shelf\EventSubscriber\ErrorPages;
use Shelf\Tests\ArrayLogger;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Translation\LocaleSwitcher;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * An error nothing caught, on a server (chapter 88): debug off, as in production. The visitor gets
 * Shelf's page with a reference, in the page's language; the details go into the log only.
 */
final class ErrorPagesTest extends ShelfWebTestCase
{
    public function testAnErrorGetsShelfsOwnPageInTheLanguageOfTheAddress(): void
    {
        $request = Request::create('https://localhost/de/');
        self::service(RequestStack::class)->push($request);
        $logger = new ArrayLogger();

        $response = $this->answer(self::service(Environment::class, 'twig'), $logger, $request, new RuntimeException('SQLSTATE[HY000]: no such table: books'));

        self::assertSame(500, $response->getStatusCode());
        $page = new Crawler((string) $response->getContent());
        self::assertSame('Etwas ist schiefgegangen', $page->filter('h1')->text());
        self::assertCount(1, $page->filter('header.site'), 'In the layout');
        self::assertMatchesRegularExpression('/Referenz ([0-9a-f]{8}) vermerkt/', $page->filter('.problem p:not(.code)')->text());
        self::assertStringNotContainsString('SQLSTATE', (string) $response->getContent(), 'No detail of the error');
        self::assertStringContainsString('Uncaught RuntimeException, reference', $logger->lines[0] ?? '', 'The details are in the log');
    }

    public function testATemplateThatFailsLeavesPlainHtml(): void
    {
        $response = $this->answer(new Environment(new ArrayLoader()), new ArrayLogger(), Request::create('/'), new RuntimeException('The disk is full'));

        self::assertSame(500, $response->getStatusCode());
        self::assertMatchesRegularExpression('~<h1>Something went wrong</h1>\n<p>It has been noted, with the reference [0-9a-f]{8}\.~', (string) $response->getContent());
    }

    /** ErrorPages as on a server, with debug off, answering an exception that nothing caught. */
    private function answer(Environment $twig, ArrayLogger $logger, Request $request, Throwable $e): Response
    {
        $pages = new ErrorPages($twig, $logger, self::service(Security::class), self::service(LocaleSwitcher::class, 'translation.locale_switcher'), false);
        $event = new ExceptionEvent(self::service(HttpKernelInterface::class, 'http_kernel'), $request, HttpKernelInterface::MAIN_REQUEST, $e);
        $pages->answer($event);

        return $event->getResponse() ?? self::fail('No answer');
    }
}
