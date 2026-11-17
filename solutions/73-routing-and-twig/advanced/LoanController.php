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

    #[Route('/members/{name}/loans', name: 'member_loans', requirements: ['name' => '[a-z]+'], methods: ['GET'])]
    public function list(string $name): Response
    {
        $loans = array_values(array_map(
            static fn(array $loan): array => ['title' => $loan['title'], 'due' => $loan['due']],
            array_filter($this->loans, static fn(array $loan): bool => $loan['member'] === $name),
        ));
        if ($loans === []) {
            throw new NotFoundHttpException("No loans for $name.");
        }

        return new Response($this->twig->render('loans.html.twig', ['name' => $name, 'loans' => $loans]));
    }

    #[Route('/loans/{id}', name: 'loan', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $loan = $this->loans[$id] ?? throw new NotFoundHttpException("There is no loan $id.");

        return new JsonResponse(['title' => $loan['title'], 'due' => $loan['due']]);
    }
}
