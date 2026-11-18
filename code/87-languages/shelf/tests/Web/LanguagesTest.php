<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use DateTimeImmutable;
use Shelf\Library;
use Shelf\MemberRepository;
use Shelf\Message\SendReminders;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;

/**
 * Shelf in English, German and Arabic (chapter 87): the language is the start of the address, /de/ or
 * /ar/, and English has none. The texts come from translations/; the refusals Shelf throws are
 * translated where they are shown; the member's language is kept for the emails.
 */
final class LanguagesTest extends ShelfWebTestCase
{
    use ClockSensitiveTrait;

    public function testThePageIsInTheLanguageOfItsAddress(): void
    {
        $page = $this->client->request('GET', '/de/?q=butler');

        self::assertResponseIsSuccessful();
        self::assertSame('de', $page->filter('html')->attr('lang'));
        self::assertSelectorTextSame('h1', 'Bücher');
        self::assertSelectorTextContains('.count', '1 Buch für „butler“');

        $page = $this->client->request('GET', '/ar/');
        self::assertSame('rtl', $page->filter('html')->attr('dir'));
        self::assertSelectorTextSame('h1', 'الكتب');
        self::assertSelectorTextContains('.count', 'كتاب واحد');
    }

    public function testTheLanguageLinksKeepThePageAndItsSearch(): void
    {
        $page = $this->client->request('GET', '/book?title=Kindred');

        self::assertSame('/de/book?title=Kindred', $page->filter('nav.languages a[hreflang=de]')->attr('href'));
        self::assertSame('/ar/book?title=Kindred', $page->filter('nav.languages a[hreflang=ar]')->attr('href'));
        self::assertSame('/book?title=Kindred', $page->filter('nav.languages a[aria-current]')->attr('href'));
        self::assertSame('https://localhost/de/book?title=Kindred', $page->filter('link[rel=alternate][hreflang=de]')->attr('href'));
    }

    public function testARefusalIsSaidInThePagesLanguage(): void
    {
        $this->logIn('Jonas');
        $this->client->request('GET', '/de/desk');
        $this->client->submitForm('Verleihen', ['title' => 'Kindred', 'member' => 'Nobody']);

        self::assertSelectorTextSame('.error', 'Es gibt kein Mitglied namens „Nobody“');
    }

    public function testAnUnknownAddressIsNotFoundInItsLanguage(): void
    {
        $this->client->request('GET', '/ar/nothing-here');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorTextSame('h1', 'غير موجود');
    }

    public function testTheLoginKeepsTheMembersLanguage(): void
    {
        $this->client->request('GET', '/ar/login');
        $this->client->submitForm('تسجيل الدخول', ['name' => 'Amira', 'password' => self::AMIRA]);

        self::assertResponseRedirects('/ar/');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'أهلًا بك يا Amira.');
        self::assertSame('ar', self::service(MemberRepository::class)->find('Amira')?->language);
    }

    public function testAReminderIsWrittenInTheMembersLanguage(): void
    {
        $members = self::service(MemberRepository::class);
        $amira = $members->find('Amira') ?? self::fail('No Amira');
        $amira->confirmEmail('amira@example.org');
        $amira->speaks('de');
        $members->update($amira);
        self::service(Library::class)->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        self::mockTime('2026-03-14 08:00');

        self::service(MessageBusInterface::class)->dispatch(new SendReminders());

        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertEmailHeaderSame($email, 'Subject', '„Kindred“ ist am Montag, 16. März zurückzugeben');
        self::assertEmailTextBodyContains($email, 'Hallo Amira,');
        self::assertEmailTextBodyContains($email, 'http://localhost:8000/de/loans');
    }

    public function testTheApiStaysInEnglish(): void
    {
        $this->client->request('GET', '/de/api/books');

        self::assertResponseStatusCodeSame(404);
    }
}
