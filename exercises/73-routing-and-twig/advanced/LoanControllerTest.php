<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once subject(__DIR__, 'LoanController.php');

final class LoanControllerTest extends TestCase
{
    private const array LOANS = [
        1 => ['member' => 'amira', 'title' => 'Kindred', 'due' => '2026-12-01'],
        2 => ['member' => 'jonas', 'title' => 'Sula', 'due' => '2026-12-08'],
        3 => ['member' => 'amira', 'title' => 'Kitchen', 'due' => '2026-12-15'],
    ];

    public function testTheRoutesComeFromTheAttributes(): void
    {
        $routes = self::routes();
        $match = (new UrlMatcher($routes, new RequestContext()))->match('/members/amira/loans');

        self::assertSame(['member_loans', 'amira', 'LoanController::list'], [$match['_route'], $match['name'], $match['_controller']]);
        self::assertSame(['GET'], $routes->get('member_loans')?->getMethods());
        self::assertSame('LoanController::show', (new UrlMatcher($routes, new RequestContext()))->match('/loans/2')['_controller']);
        self::assertSame(['GET'], $routes->get('loan')?->getMethods());
    }

    public function testRequirementsKeepOtherAddressesOut(): void
    {
        foreach (['/members/Amira/loans', '/members/amira2/loans', '/loans/two'] as $path) {
            try {
                (new UrlMatcher(self::routes(), new RequestContext()))->match($path);
                self::fail("$path should not match");
            } catch (ResourceNotFoundException) {
                self::assertTrue(true);
            }
        }
    }

    public function testAMembersPageShowsOnlyTheirLoans(): void
    {
        $html = (string) $this->controller()->list('amira')->getContent();

        self::assertStringContainsString('<h1>Loans of amira</h1>', $html);
        self::assertStringContainsString('<li>Kindred, due 2026-12-01</li>', $html);
        self::assertStringContainsString('<li>Kitchen, due 2026-12-15</li>', $html);
        self::assertStringNotContainsString('Sula', $html);
    }

    public function testAMemberWithoutLoansIsNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->controller()->list('ana');
    }

    public function testOneLoanIsJson(): void
    {
        $response = $this->controller()->show(2);

        self::assertSame('application/json', $response->headers->get('Content-Type'));
        self::assertSame(['title' => 'Sula', 'due' => '2026-12-08'], json_decode((string) $response->getContent(), true));
    }

    public function testAnUnknownLoanIsNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->controller()->show(9);
    }

    private function controller(): LoanController
    {
        $twig = new Environment(new FilesystemLoader(dirname(subject(__DIR__, 'LoanController.php'))), ['strict_variables' => true]);

        return new LoanController($twig, self::LOANS);
    }

    /** The routes, read from the attributes as Symfony reads them. */
    private static function routes(): RouteCollection
    {
        $loader = new class extends AttributeClassLoader {
            protected function configureRoute(Route $route, ReflectionClass $class, ReflectionMethod $method, object $attr): void
            {
                $route->setDefault('_controller', $class->getName() . '::' . $method->getName());
            }
        };

        return $loader->load(LoanController::class);
    }
}
