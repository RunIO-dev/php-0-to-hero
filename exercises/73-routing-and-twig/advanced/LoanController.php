<?php

declare(strict_types=1);

// The library's loans, as a Symfony controller. Each method gets a #[Route] attribute:
// - member_loans  GET /members/{name}/loans  name: lower-case letters only. The page loans.html.twig,
//                 with name and that member's loans (a list of {title, due}). A member without
//                 loans, or one the library doesn't know: 404, by throwing NotFoundHttpException.
// - loan          GET /loans/{id}            id: digits only. The loan as JSON: {"title": ..., "due": ...}.
//                 A loan that doesn't exist: 404 too.

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final readonly class LoanController
{
    /** @param array<int, array{member: string, title: string, due: string}> $loans by ID */
    public function __construct(private Environment $twig, private array $loans) {}

    // TODO: the route
    public function list(string $name): Response
    {
        $loans = array_values($this->loans); // TODO: only this member's, as {title, due}; 404 for none

        return new Response($this->twig->render('loans.html.twig', ['name' => $name, 'loans' => $loans]));
    }

    // TODO: the route
    public function show(int $id): Response
    {
        // TODO: the loan as JSON, or 404
        return new JsonResponse($this->loans[$id] ?? null);
    }
}
