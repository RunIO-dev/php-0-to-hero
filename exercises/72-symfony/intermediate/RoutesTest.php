<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

require_once subject(__DIR__, 'routes.php');

final class RoutesTest extends TestCase
{
    /** @return array<string, array{string, string, array<string, string>}> */
    public static function addresses(): array
    {
        return [
            'the list' => ['GET', '/books', ['_route' => 'books']],
            'one book' => ['GET', '/books/12', ['_route' => 'book', 'id' => '12']],
            'a loan' => ['POST', '/books/12/loans', ['_route' => 'lend', 'id' => '12']],
            "a member's loans" => ['GET', '/members/amira/loans', ['_route' => 'member_loans', 'name' => 'amira']],
            'a name with a hyphen' => ['GET', '/members/ana-lucia/loans', ['_route' => 'member_loans', 'name' => 'ana-lucia']],
        ];
    }

    /** @param array<string, string> $expected */
    #[DataProvider('addresses')]
    public function testEachAddressFindsItsRoute(string $method, string $path, array $expected): void
    {
        $match = (new UrlMatcher(libraryRoutes(), new RequestContext(method: $method)))->match($path);
        ksort($match);
        ksort($expected);

        self::assertSame($expected, $match);
    }

    /** @return array<string, array{string}> */
    public static function unknown(): array
    {
        return [
            'an id that is not a number' => ['/books/kindred'],
            'a loan for no number' => ['/books/x/loans'],
            'a name in capitals' => ['/members/Amira/loans'],
            'a name with a slash' => ['/members/amira/../loans'],
        ];
    }

    #[DataProvider('unknown')]
    public function testOtherAddressesAreNotFound(string $path): void
    {
        $this->expectException(ResourceNotFoundException::class);
        (new UrlMatcher(libraryRoutes(), new RequestContext()))->match($path);
    }

    public function testEachRouteAllowsItsMethodOnly(): void
    {
        $cases = ['/books' => ['POST', ['GET']], '/books/12' => ['DELETE', ['GET']], '/books/12/loans' => ['GET', ['POST']]];
        foreach ($cases as $path => [$method, $allowed]) {
            try {
                (new UrlMatcher(libraryRoutes(), new RequestContext(method: $method)))->match($path);
                self::fail("$method $path should be refused");
            } catch (MethodNotAllowedException $e) {
                self::assertSame($allowed, $e->getAllowedMethods(), "$method $path");
            }
        }
    }

    public function testRoutesMakeTheirAddresses(): void
    {
        $generator = new UrlGenerator(libraryRoutes(), new RequestContext());

        self::assertSame('/books/7/loans', $generator->generate('lend', ['id' => 7]));
        self::assertSame('/members/jonas/loans', $generator->generate('member_loans', ['name' => 'jonas']));
    }
}
