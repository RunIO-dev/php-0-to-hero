<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Catalogs.php');

final class CatalogsTest extends TestCase
{
    private const array ENGLISH = [
        'Log out' => 'Log out',
        'Welcome, {name}.' => 'Welcome, {name}.',
        '{count, plural, one {# copy} other {# copies}}' => '{count, plural, one {# copy} other {# copies}}',
        'Lent: {title} to {member}.' => 'Lent: {title} to {member}.',
    ];

    public function testATextWithoutATranslationIsMissing(): void
    {
        $german = ['Log out' => 'Abmelden', 'Welcome, {name}.' => 'Willkommen, {name}.'];

        self::assertSame(['{count, plural, one {# copy} other {# copies}}', 'Lent: {title} to {member}.'], Catalogs::missing(self::ENGLISH, $german));
        self::assertSame([], Catalogs::missing(self::ENGLISH, self::ENGLISH));
    }

    public function testTheValuesOfAMessage(): void
    {
        self::assertSame([], Catalogs::values('Log out'));
        self::assertSame(['member', 'title'], Catalogs::values('Lent: {title} to {member}.'));
        self::assertSame(['count', 'title'], Catalogs::values('{title}: {count, plural, one {# copy} other {# copies}}'));
    }

    public function testTheCasesOfAPluralOrSelectAreText(): void
    {
        self::assertSame(['role'], Catalogs::values('{role, select, staff {a member of staff} other {a reader}}'));
        self::assertSame(['count'], Catalogs::values('{count, plural, zero {لا نسخ} one {نسخة واحدة} two {نسختان} other {# نسخة}}'));
        self::assertSame(['n', 'name'], Catalogs::values('{n, plural, other {# for {name}}}'), 'A value inside a case');
    }

    public function testATranslationWithOtherValuesIsWrong(): void
    {
        $arabic = [
            'Welcome, {name}.' => 'أهلًا بك يا {اسم}.',
            '{count, plural, one {# copy} other {# copies}}' => '{count, plural, one {نسخة واحدة} two {نسختان} other {# نسخة}}',
            'Lent: {title} to {member}.' => 'أُعير: {title}.',
        ];

        self::assertSame(['Welcome, {name}.', 'Lent: {title} to {member}.'], Catalogs::wrongValues(self::ENGLISH, $arabic));
    }
}
