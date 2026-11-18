<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use DateTimeImmutable;
use Shelf\Book;
use Shelf\BookRepository;
use Shelf\Library;
use Shelf\MemberRepository;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Mime\Email;

/**
 * Accounts (chapter 82), as a visitor uses them: sign-up, the link in the email, a forgotten password,
 * and the account page. In the tests, Symfony Mailer sends nothing (MAILER_DSN=null://null in
 * .env.test); assertEmailCount() and getMailerMessage() read what it would have sent.
 */
final class AccountsTest extends ShelfWebTestCase
{
    use ClockSensitiveTrait;

    private const string ANA = 'a lantern on the night ferry';

    public function testASignUpWaitsForTheEmailsLink(): void
    {
        $this->signUp('Ana', 'ana@example.org');
        self::assertEmailCount(1);
        $link = $this->linkIn('ana@example.org', 'Confirm your email for Shelf');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.flash', 'Shelf has sent a link to ana@example.org');

        $this->logInWith('Ana', self::ANA);
        self::assertSelectorTextSame('.error', 'Please confirm your email first: the link is in the message Shelf sent you.');

        $this->client->request('GET', $link);
        self::assertSelectorTextSame('.lead', 'ana@example.org, for Ana.');
        self::assertNotTrue(self::service(MemberRepository::class)->find('Ana')?->confirmed, 'Opening the link changes nothing');
        $this->client->submitForm('Confirm this address');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Your address ana@example.org is confirmed.');

        $this->logInWith('Ana', self::ANA);
        self::assertSelectorTextSame('.flash', 'Welcome, Ana.');
    }

    public function testASignUpSaysWhatIsWrongAndKeepsWhatWasTyped(): void
    {
        $this->client->request('GET', '/signup');
        $this->client->submitForm('Sign up', ['name' => 'Ana', 'email' => 'ana@example.org', 'password' => 'short']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'at least 15 characters');
        self::assertInputValueSame('email', 'ana@example.org');
        self::assertInputValueSame('password', '');
        self::assertEmailCount(0);
    }

    /** The page says the same for a known address; only the address's owner learns what happened. */
    public function testASignUpWithAKnownAddressTellsOnlyItsOwner(): void
    {
        $this->giveAmiraAnAddress();

        $this->signUp('Someone', 'AMIRA@example.org');

        self::assertResponseRedirects('/login', 303);
        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertEmailAddressContains($email, 'To', 'amira@example.org');
        self::assertEmailSubjectContains($email, 'You already have an account');
        self::assertNull(self::service(MemberRepository::class)->find('Someone'));
    }

    public function testAForgottenPasswordGetsANewOneOnce(): void
    {
        $this->giveAmiraAnAddress();
        $this->client->request('GET', '/forgot');
        $this->client->submitForm('Send the link', ['email' => 'nobody@example.org']);
        self::assertEmailCount(0);
        $this->client->followRedirect();
        $unknown = (string) $this->client->getCrawler()->filter('.flash')->text();

        $this->client->submitForm('Send the link', ['email' => 'amira@example.org']);
        $link = $this->linkIn('amira@example.org', 'A new password for Shelf');
        $this->client->followRedirect();
        self::assertSame(str_replace('nobody', 'amira', $unknown), $this->client->getCrawler()->filter('.flash')->text(), 'The same answer for both');

        $this->client->request('GET', $link);
        $this->client->submitForm('Save the password', ['password' => 'a brand new set of words']);
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Your new password is saved. Log in with it.');
        $this->logInWith('Amira', 'a brand new set of words');
        self::assertSelectorTextSame('.flash', 'Welcome, Amira.');

        $this->client->request('GET', $link);
        self::assertResponseStatusCodeSame(410, 'A used link works no more');
    }

    public function testALinkThatIsChangedOrTooOldIsRefused(): void
    {
        self::mockTime('2026-11-20 10:00');
        $this->signUp('Ana', 'ana@example.org');
        $link = $this->linkIn('ana@example.org', 'Confirm your email for Shelf');

        $this->client->request('GET', str_replace('ana%40example.org', 'eve%40example.org', $link));
        self::assertResponseStatusCodeSame(410);
        self::assertSelectorTextSame('h1', 'This link has expired');

        self::mockTime('2026-11-22 10:01');
        $this->client->request('GET', $link);
        self::assertResponseStatusCodeSame(410, 'Two days and a minute later');
    }

    public function testAMemberChangesTheirPasswordWithTheCurrentOne(): void
    {
        $this->logIn('Amira');
        $this->client->request('GET', '/account');
        $this->client->submitForm('Change the password', ['current' => 'a wrong guess', 'password' => 'a brand new set of words']);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextSame('.panel .error', 'The current password is wrong.');

        $this->client->submitForm('Change the password', ['current' => self::AMIRA, 'password' => 'a brand new set of words']);
        $this->client->followRedirect();

        self::assertSelectorTextSame('.flash', 'Your password is changed.');
    }

    public function testANewAddressCountsOnceItsLinkIsUsed(): void
    {
        $this->giveAmiraAnAddress();
        $this->logIn('Amira');
        $this->client->request('GET', '/account');
        $this->client->submitForm('Send a link to the new address', ['email' => 'amira@new.example', 'current' => self::AMIRA]);
        $link = $this->linkIn('amira@new.example', 'Confirm your email for Shelf');
        self::assertSame('amira@example.org', self::service(MemberRepository::class)->find('Amira')?->email);

        $this->client->request('GET', $link);
        $this->client->submitForm('Confirm this address');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.lead', 'amira@new.example');
    }

    public function testAMemberDeletesTheirAccountOnceTheirBooksAreBack(): void
    {
        self::service(Library::class)->lend('Kindred', 'Amira', new DateTimeImmutable());
        $this->logIn('Amira');
        $this->client->request('GET', '/account');
        $this->client->submitForm('Delete my account', ['current' => self::AMIRA]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextSame('.panel .error', 'Please return your book first: then you can delete your account.');

        self::service(BookRepository::class)->add(new Book('Dawn', 'Octavia E. Butler', 1987));
        $this->logIn('Jonas');
        $this->client->request('GET', '/account');
        $this->client->submitForm('Delete my account', ['current' => self::JONAS]);
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Your account is deleted. Goodbye, and thank you for reading with us.');
        self::assertSelectorTextSame('nav a[href="/login"]', 'Log in');
        self::assertNull(self::service(MemberRepository::class)->find('Jonas'));
    }

    public function testTheAccountPageIsForMembers(): void
    {
        $this->client->request('GET', '/account');

        self::assertResponseRedirects('/login');
    }

    /** Five emails an hour from one address; the sixth request is refused, and nothing is sent. */
    public function testEmailsAreLimited(): void
    {
        foreach (range(1, 5) as $n) {
            $this->signUp("Reader $n", "reader$n@example.org");
            self::assertResponseRedirects('/login', 303);
        }
        $this->signUp('Reader 6', 'reader6@example.org');

        self::assertResponseStatusCodeSame(429);
        self::assertSelectorTextSame('h1', 'Slow down');
        self::assertEmailCount(0);
    }

    private function signUp(string $name, string $email): void
    {
        $this->client->request('GET', '/signup');
        $this->client->submitForm('Sign up', ['name' => $name, 'email' => $email, 'password' => self::ANA]);
    }

    private function logInWith(string $name, string $password): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Log in', ['name' => $name, 'password' => $password]);
        $this->client->followRedirect();
    }

    /** The link in the one email the last request sent, after checking its address and subject. */
    private function linkIn(string $to, string $subject): string
    {
        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertEmailAddressContains($email, 'To', $to);
        self::assertEmailSubjectContains($email, $subject);
        preg_match('~https://localhost/\S+~', (string) $email->getTextBody(), $link);

        return $link[0] ?? self::fail('No link in the email');
    }

    private function giveAmiraAnAddress(): void
    {
        $members = self::service(MemberRepository::class);
        $amira = $members->find('Amira') ?? self::fail('No Amira');
        $amira->confirmEmail('amira@example.org');
        $members->update($amira);
    }
}
