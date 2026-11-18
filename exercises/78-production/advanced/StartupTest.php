<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'startup.php');

final class StartupTest extends TestCase
{
    private const array GOOD = [
        'APP_ENV' => 'prod',
        'APP_SECRET' => '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08',
        'DATABASE_URL' => 'sqlite:////app/data/shelf.sqlite',
        'SHELF_HOST' => 'shelf.example',
    ];

    public function testAGoodServer(): void
    {
        self::assertSame([], missingSettings(self::GOOD));
        self::assertSame([], missingSettings([...self::GOOD, 'APP_DEBUG' => '0', 'DATABASE_URL' => 'mariadb://shelf_app:pw@db:3306/shelf']));
    }

    public function testNothingSet(): void
    {
        self::assertSame([
            'APP_ENV must be prod',
            'APP_SECRET is missing',
            'DATABASE_URL is missing',
            "SHELF_HOST must be the site's domain",
        ], missingSettings([]));
    }

    /** @return iterable<string, array{array<string, mixed>, list<string>}> */
    public static function mistakes(): iterable
    {
        yield 'dev' => [['APP_ENV' => 'dev'], ['APP_ENV must be prod']];
        yield 'debug on' => [['APP_DEBUG' => '1'], ['APP_DEBUG must be 0']];
        yield 'a short secret' => [['APP_SECRET' => 'secret'], ['APP_SECRET must be at least 32 characters']];
        yield 'the dev secret' => [['APP_SECRET' => DEV_SECRET], ['APP_SECRET is the one from .env.dev']];
        yield 'only spaces' => [['APP_SECRET' => '   '], ['APP_SECRET is missing']];
        yield 'another database' => [['DATABASE_URL' => 'postgresql://db/shelf'], ['DATABASE_URL must start with sqlite:, mysql: or mariadb:']];
        yield 'localhost' => [['SHELF_HOST' => 'LocalHost'], ["SHELF_HOST must be the site's domain"]];
        yield 'an address' => [['SHELF_HOST' => '203.0.113.7'], ["SHELF_HOST must be the site's domain"]];
        yield 'an IPv6 address' => [['SHELF_HOST' => '::1'], ["SHELF_HOST must be the site's domain"]];
    }

    /**
     * @param array<string, mixed> $change
     * @param list<string> $expected
     */
    #[DataProvider('mistakes')]
    public function testEachMistake(array $change, array $expected): void
    {
        self::assertSame($expected, missingSettings([...self::GOOD, ...$change]));
    }

    public function testEveryProblemAtOnceInOrder(): void
    {
        $env = ['APP_ENV' => 'test', 'APP_DEBUG' => 'true', 'APP_SECRET' => DEV_SECRET, 'DATABASE_URL' => 'db', 'SHELF_HOST' => '127.0.0.1'];

        self::assertSame([
            'APP_ENV must be prod',
            'APP_DEBUG must be 0',
            'APP_SECRET is the one from .env.dev',
            'DATABASE_URL must start with sqlite:, mysql: or mariadb:',
            "SHELF_HOST must be the site's domain",
        ], missingSettings($env));
    }
}
