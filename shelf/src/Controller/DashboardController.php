<?php

declare(strict_types=1);

namespace Shelf\Controller;

use Psr\Clock\ClockInterface;
use Shelf\Library;
use Shelf\Report\Statistics;
use Shelf\Web\Pager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The librarian's dashboard (chapter 86): what needs doing today (the loans that are overdue, the
 * copies set aside), and how the library is used, counted by Statistics. And the history of every
 * loan, a page at a time. Staff only: the history says who read what.
 */
#[IsGranted('ROLE_STAFF')]
final class DashboardController extends AbstractController
{
    private const int PER_PAGE = 25;

    public function __construct(private readonly Library $library, private readonly Statistics $statistics, private readonly ClockInterface $clock) {}

    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $today = $this->clock->now();
        $year = $today->modify('-1 year');
        $months = $this->statistics->perMonth($today);

        return $this->render('dashboard.html.twig', [
            'today' => $today,
            'overview' => $this->statistics->overview($today),
            'overdue' => $this->library->overdueLoans($today),
            'ready' => $this->library->readyHolds(),
            'popular' => $this->statistics->mostBorrowed($today->modify('-90 days')),
            'months' => $months,
            'busiest' => max(1, ...array_map(fn($month): int => $month->count, $months)),
            'returns' => $this->statistics->returns($year),
            'unborrowed' => $this->statistics->unborrowed($year),
        ]);
    }

    #[Route('/history', name: 'history', methods: ['GET'])]
    public function history(
        #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        string $title = '',
        #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        string $member = '',
        #[MapQueryParameter(options: ['min_range' => 1], validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        int $page = 1,
    ): Response {
        $title = trim($title);
        $member = trim($member);
        $total = $this->statistics->historyCount($title, $member);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        if ($page > $pages) {
            throw $this->createNotFoundException("There is no page $page.");
        }

        return $this->render('history.html.twig', [
            'loans' => $this->statistics->history($title, $member, ($page - 1) * self::PER_PAGE, self::PER_PAGE),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'numbers' => Pager::numbers($page, $pages),
            'search' => array_filter(['title' => $title, 'member' => $member]),
            'by_title' => $title, // not 'member': every page has that, the member logged in
            'by_member' => $member,
            'today' => $this->clock->now(),
        ]);
    }
}
