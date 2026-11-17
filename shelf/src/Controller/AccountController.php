<?php

declare(strict_types=1);

namespace Shelf\Controller;

use Shelf\Library;
use Shelf\Member;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * The login page (chapter 62) and a member's loans (63). From chapter 76, Symfony's security does the
 * logging in and out itself: the main firewall in config/packages/security.yaml reads the form's
 * POST, checks the password and the CSRF token, and starts the session. This page only shows the form.
 */
final class AccountController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function logIn(AuthenticationUtils $authentication): Response
    {
        // After a wrong password, the firewall sends the visitor back here, with the name they typed.
        $failed = $authentication->getLastAuthenticationError() !== null;

        return $this->render('login.html.twig', ['name' => $authentication->getLastUsername(), 'failed' => $failed]);
    }

    #[Route('/loans', name: 'loans', methods: ['GET'])]
    public function loans(#[CurrentUser] Member $member, Library $library): Response
    {
        return $this->render('loans.html.twig', ['loans' => $library->loansOf($member->name)]);
    }
}
