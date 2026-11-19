<?php

declare(strict_types=1);

namespace Shelf\Controller;

use InvalidArgumentException;
use Psr\Clock\ClockInterface;
use Shelf\Book;
use Shelf\BookNotFound;
use Shelf\BookQuery;
use Shelf\BookSort;
use Shelf\Demo;
use Shelf\Form\BookData;
use Shelf\Form\BookType;
use Shelf\Hold;
use Shelf\Library;
use Shelf\Member;
use Shelf\Refused;
use Shelf\Security\BookVoter;
use Shelf\Web\Covers;
use Shelf\Web\Pager;
use Shelf\Web\Said;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

/**
 * The pages about books (chapter 73), from App's routes: the list, one book with its forms, adding
 * a book with its cover (a Symfony form from chapter 75), and the cover itself. Each method reads the request, asks Library, and
 * answers; the rules stay in Library. Who may change books: #[IsGranted] asks BookVoter (chapter 76).
 */
final class BookController extends AbstractController
{
    public function __construct(private readonly Library $library, private readonly Covers $covers, private readonly Demo $demo) {}

    /** Books per page of the list (chapter 81): four rows of three on a wide screen. */
    public const int PER_PAGE = 12;

    /** The longest search the list takes; a longer one is cut, not refused. */
    public const int MAX_SEARCH = 100;

    /**
     * The list (chapter 81): ?q= searches titles, authors and ISBNs, ?available=1 keeps the books on the
     * shelf, ?sort= orders them, ?page= picks the page. A value that isn't one of these (?sort=price,
     * ?page=0) is a 400; a page after the last is a 404, as for any address that shows nothing.
     */
    #[Route('/', name: 'books', methods: ['GET'])]
    public function list(
        #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        string $q = '',
        #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        bool $available = false,
        #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        BookSort $sort = BookSort::Title,
        #[MapQueryParameter(options: ['min_range' => 1], validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        int $page = 1,
    ): Response {
        $q = mb_substr(trim($q), 0, self::MAX_SEARCH);
        $found = $this->library->page(new BookQuery(availableOnly: $available, text: $q, sort: $sort), $page, self::PER_PAGE);
        if ($page > $found['pages']) {
            throw $this->createNotFoundException("There is no page $page.");
        }
        $covered = array_filter($found['books'], fn(Book $book): bool => $this->covers->find($book->title) !== null);
        // What the address keeps from page to page: only what differs from the defaults, so /?page=2 stays short.
        $search = array_filter(['q' => $q, 'available' => $available ? '1' : '', 'sort' => $sort === BookSort::Title ? '' : $sort->value]);

        return $this->render('list.html.twig', [
            'books' => $found['books'],
            'covered' => array_column($covered, 'title'),
            'total' => $found['total'],
            'page' => $page,
            'pages' => $found['pages'],
            'numbers' => Pager::numbers($page, $found['pages']),
            'search' => $search,
            'q' => $q,
            'available' => $available,
            'sort' => $sort,
            'sorts' => BookSort::cases(),
        ]);
    }

    #[Route('/book', name: 'book', methods: ['GET'])]
    public function book(#[MapQueryParameter] string $title = ''): Response
    {
        return $this->bookPage(trim($title));
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted(BookVoter::CHANGE)]
    public function add(Request $request, TranslatorInterface $translator): Response
    {
        $book = new BookData();
        $form = $this->createForm(BookType::class, $book, ['action' => $this->generateUrl('add')])->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($book->cover !== null && $this->demo->on) {
                    // Chapter 88: every visitor would see a picture any other visitor chose.
                    throw new Refused('The demo takes no covers: everyone would see the pictures anyone uploads.');
                }
                if ($book->cover !== null) {
                    $this->covers->check($book->cover); // the content, before the book is added: all or nothing
                }
                $added = $this->library->add($book->fields()); // and Library's own rules: a book only once
                if ($book->cover !== null) {
                    $this->covers->save($added->title, $book->cover);
                }
                $this->addFlash('notice', t('Added "{title}".', ['title' => $added->title]));

                return $this->redirectToRoute('books', status: Response::HTTP_SEE_OTHER);
            } catch (InvalidArgumentException $e) {
                $form->addError(new FormError($e instanceof TranslatableInterface ? $e->trans($translator) : $e->getMessage()));
            }
        }

        // A form that was sent with mistakes comes back with them, and render() answers 422.
        return $this->render('add.html.twig', ['form' => $form]);
    }

    #[Route('/copies', name: 'copies', methods: ['POST'])]
    #[IsGranted(BookVoter::CHANGE)]
    public function copies(Request $request, ClockInterface $clock): Response
    {
        $title = trim($request->request->getString('title'));
        $copies = trim($request->request->getString('copies'));
        try {
            [$book, $served] = $this->library->changeCopies($title, $copies, $clock->now());
        } catch (BookNotFound) {
            throw $this->createNotFoundException();
        } catch (InvalidArgumentException $e) {
            return $this->bookPage($title, $copies, Said::of($e));
        }
        $this->addFlash('notice', t(
            'Saved: the library has {copies, plural, one {# copy} other {# copies}} on the shelf{served, plural, =0 {.} other {, and # set aside for {names}.}}',
            ['copies' => $book->copies, 'served' => count($served), 'names' => implode(', ', array_map(fn(Hold $hold): string => $hold->member->name, $served))],
        ));

        return $this->redirectToRoute('book', ['title' => $book->title], Response::HTTP_SEE_OTHER);
    }

    #[Route('/remove', name: 'remove', methods: ['POST'])]
    #[IsGranted(BookVoter::CHANGE)]
    public function remove(Request $request): Response
    {
        $title = trim($request->request->getString('title'));
        try {
            $book = $this->library->remove($title);
        } catch (BookNotFound) {
            throw $this->createNotFoundException();
        } catch (InvalidArgumentException $e) {
            return $this->bookPage($title, error: Said::of($e));
        }
        $this->covers->remove($book->title);
        $this->addFlash('notice', t('Removed "{title}".', ['title' => $book->title]));

        return $this->redirectToRoute('books', status: Response::HTTP_SEE_OTHER);
    }

    /** Chapter 84: a member reserves a book whose copies are all out, and joins its queue. */
    #[Route('/reserve', name: 'reserve', methods: ['POST'])]
    #[IsGranted('ROLE_READER')]
    public function reserve(#[CurrentUser] Member $member, Request $request, ClockInterface $clock): Response
    {
        $title = trim($request->request->getString('title'));
        try {
            $hold = $this->library->reserve($title, $member, $clock->now());
        } catch (BookNotFound) {
            throw new NotFoundHttpException();
        } catch (InvalidArgumentException $e) {
            return $this->bookPage($title, error: Said::of($e));
        }
        $this->addFlash('notice', t('You reserved "{title}". The next copy that comes back is kept for you.', ['title' => $hold->book->title]));

        return $this->redirectToRoute('book', ['title' => $hold->book->title], Response::HTTP_SEE_OTHER);
    }

    #[Route('/cover', name: 'cover', methods: ['GET'])]
    public function cover(#[MapQueryParameter] string $title = ''): Response
    {
        $cover = $this->covers->find($title) ?? throw $this->createNotFoundException();

        return new BinaryFileResponse($cover['path'], headers: ['Content-Type' => $cover['type']]);
    }

    /** One book's page. $error is set when a form comes back with a mistake, and $copies when it was the copies form. */
    private function bookPage(string $title, ?string $copies = null, TranslatableInterface|string|null $error = null): Response
    {
        try {
            $book = $this->library->find($title);
        } catch (BookNotFound) {
            throw new NotFoundHttpException();
        }

        // Chapter 84: the member's reservation of this book, with its place in the queue.
        $member = $this->getUser();
        $hold = $member instanceof Member
            ? array_find($this->library->holdsOf($member), fn(array $entry): bool => $entry['hold']->book->title === $book->title)
            : null;

        return $this->render('book.html.twig', [
            'book' => $book,
            'hold' => $hold,
            'hasCover' => $this->covers->find($book->title) !== null,
            'copies' => $copies ?? (string) $book->copies,
            'error' => $error,
        ], new Response(status: $error === null ? 200 : 422));
    }
}
