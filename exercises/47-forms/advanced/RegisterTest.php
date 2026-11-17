<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'rules.php');

final class RegisterTest extends TestCase
{
    private const GOOD = ['name' => 'Amira', 'email' => 'amira@example.org', 'year' => '1990', 'terms' => 'yes'];

    public function testValuesAreTrimmedStringsOfTheFourFields(): void
    {
        $values = values(['name' => '  Amira ', 'email' => ['a@b.c'], 'year' => '1990', 'admin' => '1']);

        self::assertSame(['name' => 'Amira', 'email' => '', 'year' => '1990', 'terms' => ''], $values);
    }

    public function testGoodValuesHaveNoErrors(): void
    {
        self::assertSame([], errors(self::GOOD));
    }

    public function testEveryWrongFieldIsReportedAtOnce(): void
    {
        $errors = errors(['name' => '', 'email' => 'amira', 'year' => '1890', 'terms' => '']);

        self::assertSame([
            'name' => 'From 1 to 50 characters.',
            'email' => 'A valid email address, please.',
            'year' => 'A year from 1900 to 2026.',
            'terms' => 'Please accept the rules.',
        ], $errors);
    }

    public function testTheNameCountsCharactersNotBytes(): void
    {
        self::assertSame([], errors(['name' => str_repeat('ä', 50)] + self::GOOD));
        self::assertArrayHasKey('name', errors(['name' => str_repeat('ä', 51)] + self::GOOD));
    }

    public function testThePageRedirectsOrAnswers422(): void
    {
        $post = "POST /register.php\nContent-Type: application/x-www-form-urlencoded\n\n";
        [$good, $array] = responses_of(
            dirname(subject(__DIR__, 'register.php')),
            $post . "name=Amira&email=amira%40example.org&year=1990&terms=yes\n\n###\n" . $post . "name[]=x&email=%3Cb%3E&year=1990\n",
        );

        self::assertSame(303, $good['status']);
        self::assertSame('/welcome.php', $good['headers']['location'] ?? null);
        self::assertSame(422, $array['status'], 'name[]=x must be an error for the visitor, not a crash');
        self::assertStringContainsString('value="&lt;b&gt;"', $array['body']);
        self::assertStringContainsString('Please accept the rules.', $array['body']);
    }
}
