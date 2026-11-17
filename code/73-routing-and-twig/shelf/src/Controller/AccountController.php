<?php

declare(strict_types=1);

namespace Shelf\Controller;

use LogicException;
use Psr\Log\LoggerInterface;
use Shelf\Accounts;
use Shelf\Library;
use Shelf\Web\CurrentMember;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Logging in and out (chapter 62) and a member's loans (63), from App's routes. The session is
 * Symfony's now: $request->getSession(). Chapter 76 hands the login to Symfony's security.
 */
final class AccountController extends AbstractController
{
    public function __construct(private readonly Accounts $accounts, private readonly LoggerInterface $logger) {}

    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function logIn(Request $request): Response
    {
        if (!$request->isMethod('POST')) {
            return $this->render('login.html.twig', ['name' => '', 'error' => null]);
        }
        $name = trim($request->request->getString('name'));
        $member = $this->accounts->authenticate($name, $request->request->getString('password')); // every space counts
        if ($member === null) {
            $this->logger->warning('Failed login for {name}', ['name' => $name]); // never the password

            return $this->render('login.html.twig', ['name' => $name, 'error' => 'Wrong name or password.'], new Response(status: 422));
        }
        $this->logger->info('{name} logged in', ['name' => $member->name]);
        $session = $request->getSession();
        $session->migrate(true); // who the visitor is changes, so the session ID does too
        $session->set('member', $member->name);
        $this->addFlash('notice', "Welcome, $member->name.");

        return $this->redirectToRoute('books', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logOut(Request $request): Response
    {
        $session = $request->getSession();
        $session->remove('member');
        $session->migrate(true);
        $this->addFlash('notice', 'You are logged out.');

        return $this->redirectToRoute('books', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/loans', name: 'loans', methods: ['GET'])]
    public function loans(CurrentMember $current, Library $library): Response
    {
        $member = $current->member() ?? throw new LogicException('RequireLogin lets only members in');

        return $this->render('loans.html.twig', ['loans' => $library->loansOf($member->name)]);
    }
}
