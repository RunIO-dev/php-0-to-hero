<?php

declare(strict_types=1);

// ✗ The second test needs the book that the first one added: alone, or in another order, it fails.

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BooksTest extends WebTestCase
{
    public function testStaffAddABook(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/books', content: '{"title": "Sula", "author": "Toni Morrison", "year": 1973}');
        self::assertResponseStatusCodeSame(201);
    }

    public function testTheBookIsInTheList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        self::assertSelectorTextContains('.books', 'Sula');
    }
}
