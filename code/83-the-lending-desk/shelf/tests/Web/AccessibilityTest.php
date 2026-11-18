<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use DOMElement;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * What every page must have for keyboards and screen readers (chapter 80), checked on each page, for a
 * visitor and for the library's staff. A new page that forgets one fails here, not in front of a reader.
 */
final class AccessibilityTest extends ShelfWebTestCase
{
    /** @return iterable<string, array{string, ?string}> the address, and who is logged in */
    public static function pages(): iterable
    {
        yield 'the books' => ['/', null];
        yield 'a search' => ['/?q=butler&sort=newest', null];
        yield 'a search that finds nothing' => ['/?q=nothing', null];
        yield 'log in' => ['/login', null];
        yield 'sign up' => ['/signup', null];
        yield 'a forgotten password' => ['/forgot', null];
        yield 'a link that has expired' => ['/reset?member=1&check=x', null];
        yield 'my account' => ['/account', 'Amira'];
        yield 'the lending desk' => ['/desk', 'Jonas'];
        yield 'the members' => ['/members', 'Jonas'];
        yield "a book's page" => ['/book?title=Kindred', null];
        yield 'a missing page' => ['/nothing-here', null];
        yield 'a request not understood' => ['/?sort=price', null];
        yield 'my loans' => ['/loans', 'Jonas'];
        yield "a book's page, for staff" => ['/book?title=Kindred', 'Amira'];
        yield 'add a book' => ['/add', 'Amira'];
    }

    #[DataProvider('pages')]
    public function testThePageCanBeUsedWithoutAMouseOrEyes(string $path, ?string $member): void
    {
        if ($member !== null) {
            $this->logIn($member);
        }
        $page = $this->client->request('GET', $path);

        self::assertSame('en', $page->filter('html')->attr('lang'), 'The language, for the screen reader');
        self::assertCount(1, $page->filter('h1'), 'One main heading');
        self::assertSame('#main', $page->filter('a.skip')->attr('href'), 'A skip link first');
        self::assertCount(1, $page->filter('main#main'), 'The skip link has a target');
        self::assertCount(1, $page->filter('nav[aria-label]'), 'The menu is a named landmark');

        foreach ($page->filter('img') as $image) {
            self::assertInstanceOf(DOMElement::class, $image);
            self::assertTrue($image->hasAttribute('alt'), 'Every image has alt, empty for decoration');
        }
        foreach ($page->filter('input:not([type=hidden]), select, textarea') as $field) {
            self::assertInstanceOf(DOMElement::class, $field);
            $labelled = $field->parentNode instanceof DOMElement && $field->parentNode->tagName === 'label'
                || $field->getAttribute('id') !== '' && $page->filter('label[for="' . $field->getAttribute('id') . '"]')->count() === 1;
            self::assertTrue($labelled, 'Every field has a label: ' . $field->getAttribute('name'));
        }
    }

    public function testTheMenuMarksTheCurrentPage(): void
    {
        $this->logIn('Jonas');
        $page = $this->client->request('GET', '/loans');

        self::assertSame(['My loans'], $page->filter('nav [aria-current="page"]')->each(static fn($link): string => $link->text()));
    }
}
