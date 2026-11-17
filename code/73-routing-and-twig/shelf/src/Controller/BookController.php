<?php

declare(strict_types=1);

namespace Shelf\Controller;

use InvalidArgumentException;
use Shelf\Book;
use Shelf\BookNotFound;
use Shelf\Library;
use Shelf\Web\Covers;
use Shelf\Web\CurrentMember;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The pages about books (chapter 73), from App's routes: the list, one book with its forms, adding
 * a book with its cover, and the cover itself. Each method reads the request, asks Library, and
 * answers; the rules stay in Library. Who may change books is checked before, by RequireLogin.
 */
final class BookController extends AbstractController
{
    public function __construct(private readonly Library $library, private readonly Covers $covers, private readonly CurrentMember $current) {}

    #[Route('/', name: 'books', methods: ['GET'])]
    public function list(): Response
    {
        $books = $this->library->books();
        $covered = array_map(fn(Book $book): string => $book->title, array_filter($books, fn(Book $book): bool => $this->covers->find($book->title) !== null));

        return $this->render('list.html.twig', ['books' => $books, 'covered' => array_values($covered)]);
    }

    #[Route('/book', name: 'book', methods: ['GET'])]
    public function book(#[MapQueryParameter] string $title = ''): Response
    {
        return $this->bookPage(trim($title));
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(Request $request): Response
    {
        $fields = ['title' => '', 'author' => '', 'year' => '', 'isbn' => ''];
        if (!$request->isMethod('POST')) {
            return $this->render('add.html.twig', [...$fields, 'error' => null]);
        }
        foreach (array_keys($fields) as $name) {
            $fields[$name] = trim($request->request->getString($name));
        }
        $book = [$fields['title'], $fields['author'], $fields['year'], ...$fields['isbn'] === '' ? [] : [$fields['isbn']]];
        $cover = $request->files->get('cover'); // null when no file was chosen
        try {
            if ($cover instanceof UploadedFile) {
                $this->covers->check($cover); // before the book is added: all or nothing
            }
            $added = $this->library->add($book);
        } catch (InvalidArgumentException $e) {
            return $this->render('add.html.twig', [...$fields, 'error' => $e->getMessage()], new Response(status: 422));
        }
        if ($cover instanceof UploadedFile) {
            $this->covers->save($added->title, $cover);
        }
        $this->addFlash('notice', "Added \"$added->title\".");

        return $this->redirectToRoute('books', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/copies', name: 'copies', methods: ['POST'])]
    public function copies(Request $request): Response
    {
        $title = trim($request->request->getString('title'));
        $copies = trim($request->request->getString('copies'));
        try {
            $book = $this->library->changeCopies($title, $copies);
        } catch (BookNotFound) {
            throw $this->createNotFoundException();
        } catch (InvalidArgumentException $e) {
            return $this->bookPage($title, $copies, $e->getMessage());
        }
        $this->addFlash('notice', "Saved: the library has $book->copies " . ($book->copies === 1 ? 'copy' : 'copies') . '.');

        return $this->redirectToRoute('book', ['title' => $book->title], Response::HTTP_SEE_OTHER);
    }

    #[Route('/remove', name: 'remove', methods: ['POST'])]
    public function remove(Request $request): Response
    {
        $title = trim($request->request->getString('title'));
        try {
            $book = $this->library->remove($title);
        } catch (BookNotFound) {
            throw $this->createNotFoundException();
        } catch (InvalidArgumentException $e) {
            return $this->bookPage($title, error: $e->getMessage());
        }
        $this->covers->remove($book->title);
        $this->addFlash('notice', "Removed \"$book->title\".");

        return $this->redirectToRoute('books', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/cover', name: 'cover', methods: ['GET'])]
    public function cover(#[MapQueryParameter] string $title = ''): Response
    {
        $cover = $this->covers->find($title) ?? throw $this->createNotFoundException();

        return new BinaryFileResponse($cover['path'], headers: ['Content-Type' => $cover['type']]);
    }

    /** One book's page. $error is set when a form comes back with a mistake, and $copies when it was the copies form. */
    private function bookPage(string $title, ?string $copies = null, ?string $error = null): Response
    {
        try {
            $book = $this->library->find($title);
        } catch (BookNotFound) {
            throw new NotFoundHttpException();
        }

        return $this->render('book.html.twig', [
            'book' => $book,
            'hasCover' => $this->covers->find($book->title) !== null,
            'copies' => $copies ?? (string) $book->copies,
            'error' => $error,
            'mayChange' => $this->current->member()?->role->mayChangeBooks() ?? false,
        ], new Response(status: $error === null ? 200 : 422));
    }
}
