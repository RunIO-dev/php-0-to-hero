<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

require_once subject(__DIR__, 'page.php');

final class PageTest extends TestCase
{
    public function testABookHasItsTitleAsHeading(): void
    {
        $response = bookPage(Request::create('/book?title=Kindred'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('<h1>Kindred</h1>', $response->getContent());
    }

    public function testTheTitleIsEscaped(): void
    {
        $response = bookPage(Request::create('/book', parameters: ['title' => '<b>Kitchen</b> & "more"']));

        self::assertSame('<h1>&lt;b&gt;Kitchen&lt;/b&gt; &amp; &quot;more&quot;</h1>', $response->getContent());
    }

    public function testNoTitleIsABadRequest(): void
    {
        foreach (['/book', '/book?title=', '/book?title=+++'] as $path) {
            $response = bookPage(Request::create($path));
            self::assertSame(400, $response->getStatusCode(), $path);
            self::assertSame('<p>Which book?</p>', $response->getContent(), $path);
        }
    }

    public function testEveryAnswerSaysNosniff(): void
    {
        foreach (['/book?title=Sula', '/book'] as $path) {
            self::assertSame('nosniff', bookPage(Request::create($path))->headers->get('X-Content-Type-Options'), $path);
        }
    }
}
