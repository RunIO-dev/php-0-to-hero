<?php

declare(strict_types=1);

// Exercise 77, advanced: tests through a browser.
//
// suggestions.php is right. Write tests that use it as a browser does: new HttpKernelBrowser(new
// Suggestions()), then request(), submitForm(), followRedirect(), and the Crawler each one returns.
// Test the page, a suggestion that is added, every mistake, the token, the other methods and paths,
// and that a suggestion with HTML in it shows as text. Each file in bugs/ is suggestions.php with one
// mistake, and your tests must fail on every one.

use PHPUnit\Framework\TestCase;
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
        self::markTestIncomplete('Write this test, then the others.');
    }

    // your other tests here
}

// Check your work: composer exercise 77 advanced
