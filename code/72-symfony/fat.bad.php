<?php

declare(strict_types=1);

// ✗ A controller that does the library's work itself.

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LoanController
{
    public function __construct(private PDO $db) {}

    #[Route('/books/{id}/loans', methods: ['POST'])]
    public function lend(int $id, Request $request): Response
    {
        $copies = $this->db->prepare('SELECT copies FROM books WHERE id = ?');
        $copies->execute([$id]);
        if ((int) $copies->fetchColumn() < 1) {
            return new Response('No copy left', 409);
        }
        $due = new DateTimeImmutable('+21 days'); // the library's rule, here and nowhere else
        $this->db->prepare('INSERT INTO loans (book_id, member, due) VALUES (?, ?, ?)')
            ->execute([$id, $request->request->getString('member'), $due->format('Y-m-d')]);

        return new Response('', 303, ['Location' => '/']);
    }
}
