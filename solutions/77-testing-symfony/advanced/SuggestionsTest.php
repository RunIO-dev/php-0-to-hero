<?php

declare(strict_types=1);

// Exercise 77, advanced: tests through a browser.

use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

require_once __DIR__ . '/suggestions.php';

final class SuggestionsTest extends TestCase
{
    private HttpKernelBrowser $browser;

    protected function setUp(): void
    {
        $this->browser = new HttpKernelBrowser(new Suggestions());
    }

    public function testThePageShowsTheSuggestions(): void
    {
        $page = $this->browser->request('GET', '/');

        self::assertSame(200, $this->statusCode());
        self::assertSame('Suggestions', $page->filter('h1')->text());
        self::assertSame(['Kindred'], self::items($page));
    }

    public function testASuggestionIsAddedThenTheListIsShown(): void
    {
        $this->suggest('Sula');

        self::assertSame(303, $this->statusCode());
        self::assertSame('/', $this->browser->getResponse()->headers->get('Location'));
        self::assertSame(['Kindred', 'Sula'], self::items($this->browser->followRedirect()));
    }

    public function testAnEmptyTitleIsRefused(): void
    {
        $page = $this->suggest('   ');

        self::assertSame(422, $this->statusCode());
        self::assertSame('Name a book.', $page->filter('.error')->text());
        self::assertSame(['Kindred'], self::items($page));
    }

    public function testATitleOfAHundredLettersIsTheLongest(): void
    {
        $this->suggest(str_repeat('é', 100));
        self::assertSame(303, $this->statusCode());

        $page = $this->suggest(str_repeat('é', 101));
        self::assertSame(422, $this->statusCode());
        self::assertSame('At most 100 letters.', $page->filter('.error')->text());
    }

    public function testAMistakeKeepsWhatWasTyped(): void
    {
        $page = $this->suggest(str_repeat('a', 101));

        self::assertSame(str_repeat('a', 101), $page->filter('input[name=title]')->attr('value'));
    }

    public function testAFormWithoutItsTokenIsRefused(): void
    {
        $this->browser->request('GET', '/');
        $this->browser->request('POST', '/', ['title' => 'Sula']);

        self::assertSame(403, $this->statusCode());
        self::assertSame(['Kindred'], self::items($this->browser->request('GET', '/')), 'Nothing was added');
    }

    public function testAGuessedTokenIsRefused(): void
    {
        $this->browser->request('GET', '/');
        $this->browser->request('POST', '/', ['title' => 'Sula', '_token' => 'guessed']);

        self::assertSame(403, $this->statusCode());
        self::assertSame(['Kindred'], self::items($this->browser->request('GET', '/')), 'Nothing was added');
    }

    public function testASuggestionIsText(): void
    {
        $this->suggest('<b>Sula</b>');

        $page = $this->browser->followRedirect();
        self::assertSame(['Kindred', '<b>Sula</b>'], self::items($page));
        self::assertCount(0, $page->filter('li b'));
    }

    public function testOtherMethodsAndPaths(): void
    {
        $this->browser->request('DELETE', '/');
        self::assertSame(405, $this->statusCode());
        self::assertSame('GET, POST', $this->browser->getResponse()->headers->get('Allow'));

        $this->browser->request('GET', '/admin');
        self::assertSame(404, $this->statusCode());
    }

    /** The form, filled in and sent as a browser does: with its token, after the page set the cookie. */
    private function suggest(string $title): Crawler
    {
        $this->browser->request('GET', '/');

        return $this->browser->submitForm('Suggest', ['title' => $title]);
    }

    private function statusCode(): int
    {
        return $this->browser->getResponse()->getStatusCode();
    }

    /** @return list<string> */
    private static function items(Crawler $page): array
    {
        return $page->filter('li')->each(static fn(Crawler $li): string => $li->text());
    }
}
