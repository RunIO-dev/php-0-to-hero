<?php

declare(strict_types=1);

// Reading HTML in a test (symfony/dom-crawler, with css-selector): find elements by CSS selector,
// and read their text, attributes and form fields. The browser returns a Crawler for every page.

use Symfony\Component\DomCrawler\Crawler;

require __DIR__ . '/vendor/autoload.php';

$page = new Crawler(<<<'HTML'
    <h1>Books</h1>
    <ul class="books">
        <li class="book"><a href="/book?title=Kindred">Kindred</a> <span class="status">Available</span></li>
        <li class="book"><a href="/book?title=Sula">Sula</a> <span class="status on-loan">On loan</span></li>
    </ul>
    <form method="post" action="/login"><input name="name" value="Amira"><button>Log in</button></form>
    HTML, 'https://localhost/');

echo $page->filter('h1')->text(), "\n";
echo $page->filter('.book')->count(), " books\n";
echo $page->filter('.on-loan')->closest('li')?->filter('a')->text(), " is on loan\n";
echo $page->selectLink('Sula')->link()->getUri(), "\n";

$form = $page->selectButton('Log in')->form();
echo $form->getMethod(), ' ', $form->getUri(), ' name=', $form['name']->getValue(), "\n";
