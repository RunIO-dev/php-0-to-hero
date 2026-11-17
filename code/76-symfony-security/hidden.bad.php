<?php

declare(strict_types=1);

// ✗ The template hides the link for readers; the controller checks nothing.

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StaffController extends AbstractController
{
    #[Route('/remove-all-loans', methods: ['POST'])]
    public function removeAllLoans(): Response
    {
        // {% if is_granted('ROLE_STAFF') %} in the template: but anyone can send this request
        return new Response('All loans removed');
    }
}
