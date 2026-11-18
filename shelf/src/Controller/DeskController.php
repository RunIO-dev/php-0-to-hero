<?php

declare(strict_types=1);

namespace Shelf\Controller;

use InvalidArgumentException;
use Psr\Clock\ClockInterface;
use Shelf\Book;
use Shelf\Library;
use Shelf\LoanNotFound;
use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\Role;
use Shelf\ShelfException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The lending desk (chapter 83): what the staff did with bin/console until now, in the browser.
 * Lend a book to a member, take it back, extend a loan, and look after the members: block one, or
 * give one another role. Every rule stays in Library and Loan; each action answers with a message,
 * or with the desk again and what went wrong.
 * Readers extend their own loans from "My loans"; renew() is for both.
 */
final class DeskController extends AbstractController
{
    public function __construct(private readonly Library $library, private readonly ClockInterface $clock) {}

    #[Route('/desk', name: 'desk', methods: ['GET'])]
    #[IsGranted('ROLE_STAFF')]
    public function desk(#[MapQueryParameter] string $title = ''): Response
    {
        return $this->deskPage(typed: ['title' => trim($title)]); // from "Lend this book" on a book's page
    }

    #[Route('/desk/lend', name: 'desk_lend', methods: ['POST'])]
    #[IsGranted('ROLE_STAFF')]
    public function lend(Request $request): Response
    {
        $title = trim($request->request->getString('title'));
        $name = trim($request->request->getString('member'));
        try {
            $loan = $this->library->lend($title, $name, $this->clock->now());
        } catch (ShelfException|InvalidArgumentException $e) { // no such book or member, no copy left, a rule
            return $this->deskPage($e->getMessage(), ['title' => $title, 'member' => $name]);
        }
        $this->addFlash('notice', $loan->line() . '.');

        return $this->redirectToRoute('desk', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/desk/return', name: 'desk_return', methods: ['POST'])]
    #[IsGranted('ROLE_STAFF')]
    public function giveBack(Request $request): Response
    {
        try {
            $loan = $this->library->giveBack($request->request->getInt('loan'), $this->clock->now());
        } catch (LoanNotFound) {
            throw $this->createNotFoundException();
        } catch (InvalidArgumentException $e) {
            return $this->deskPage($e->getMessage());
        }
        $this->addFlash('notice', "\"{$loan->book->title}\" is back from {$loan->member->name}.");

        return $this->redirectToRoute('desk', status: Response::HTTP_SEE_OTHER);
    }

    /**
     * One more loan period. The staff may extend any loan, a reader only their own: someone else's
     * loan is answered as if it didn't exist, so its number tells a reader nothing.
     */
    #[Route('/renew', name: 'renew', methods: ['POST'])]
    #[IsGranted('ROLE_READER')]
    public function renew(#[CurrentUser] Member $member, Request $request): Response
    {
        $staff = $this->isGranted('ROLE_STAFF') && $request->request->getString('from') === 'desk';
        try {
            $loan = $this->library->loan($request->request->getInt('loan'));
            if (!$staff && $loan->member->name !== $member->name) {
                throw new LoanNotFound();
            }
            $this->library->renew($loan->id(), $this->clock->now());
        } catch (LoanNotFound) {
            throw $this->createNotFoundException();
        } catch (InvalidArgumentException $e) {
            return $staff ? $this->deskPage($e->getMessage())
                : $this->render('loans.html.twig', ['loans' => $this->library->loansOf($member->name), 'error' => $e->getMessage()], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
        }
        $this->addFlash('notice', "\"{$loan->book->title}\" is now due back on {$loan->due->format('l, j F Y')}.");

        return $this->redirectToRoute($staff ? 'desk' : 'loans', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/members', name: 'members', methods: ['GET'])]
    #[IsGranted('ROLE_STAFF')]
    public function members(): Response
    {
        return $this->membersPage();
    }

    #[Route('/members/block', name: 'members_block', methods: ['POST'])]
    #[IsGranted('ROLE_STAFF')]
    public function block(#[CurrentUser] Member $staff, Request $request): Response
    {
        $blocked = $request->request->getString('blocked') === '1';
        try {
            $member = $this->library->setBlocked($request->request->getString('member'), $blocked, $staff);
        } catch (MemberNotFound) {
            throw $this->createNotFoundException();
        } catch (InvalidArgumentException $e) {
            return $this->membersPage($e->getMessage());
        }
        $this->addFlash('notice', $blocked ? "$member->name is blocked: they can't borrow until you unblock them." : "$member->name may borrow again.");

        return $this->redirectToRoute('members', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/members/role', name: 'members_role', methods: ['POST'])]
    #[IsGranted('ROLE_STAFF')]
    public function role(#[CurrentUser] Member $staff, Request $request): Response
    {
        $role = Role::tryFrom($request->request->getString('role')) ?? throw $this->createNotFoundException();
        try {
            $member = $this->library->changeRole($request->request->getString('member'), $role, $staff);
        } catch (MemberNotFound) {
            throw $this->createNotFoundException();
        } catch (InvalidArgumentException $e) {
            return $this->membersPage($e->getMessage());
        }
        $this->addFlash('notice', "$member->name is {$role->value} now.");

        return $this->redirectToRoute('members', status: Response::HTTP_SEE_OTHER);
    }

    /** @param array{title?: string, member?: string} $typed what the lend form had, after a mistake */
    private function deskPage(?string $error = null, array $typed = []): Response
    {
        return $this->render('desk.html.twig', [
            'loans' => $this->library->currentLoans(),
            'members' => $this->library->members(),
            'available' => array_filter($this->library->books(), fn(Book $book): bool => $book->isAvailable()),
            'today' => $this->clock->now(),
            'error' => $error,
            'typed' => $typed + ['title' => '', 'member' => ''],
        ], new Response(status: $error === null ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    private function membersPage(?string $error = null): Response
    {
        $members = $this->library->members();
        $counts = [];
        foreach ($members as $member) {
            $counts[$member->name] = count($this->library->loansOf($member->name));
        }

        return $this->render(
            'members.html.twig',
            ['members' => $members, 'counts' => $counts, 'error' => $error],
            new Response(status: $error === null ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY),
        );
    }
}
