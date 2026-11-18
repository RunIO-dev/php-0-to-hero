<?php

declare(strict_types=1);

namespace Shelf\Controller;

use InvalidArgumentException;
use Psr\Clock\ClockInterface;
use Shelf\Accounts;
use Shelf\EmailInUse;
use Shelf\HoldNotFound;
use Shelf\Library;
use Shelf\Member;
use Shelf\MemberRepository;
use Shelf\Web\AccountLinks;
use Shelf\Web\AccountMail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * The login page (chapter 62) and a member's loans (63). From chapter 76, Symfony's security does the
 * logging in and out itself: the main firewall in config/packages/security.yaml reads the form's
 * POST, checks the password and the CSRF token, and starts the session. This page only shows the form.
 *
 * Chapter 82: accounts. Signing up, confirming an email address, a new password for who forgot it,
 * and a page for one's own account: a new password, a new address, or no account at all. Every form
 * that sends an email is limited per visitor (config/packages/rate_limiter.yaml).
 */
final class AccountController extends AbstractController
{
    public function __construct(
        private readonly Accounts $accounts,
        private readonly MemberRepository $members,
        private readonly AccountLinks $links,
        private readonly AccountMail $mail,
        private readonly RateLimiterFactoryInterface $accountMailLimiter,
    ) {}

    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function logIn(AuthenticationUtils $authentication): Response
    {
        // After a wrong password, the firewall sends the visitor back here, with the name they typed.
        // Chapter 82: a right password for an account that waits for its email gets that message.
        $error = $authentication->getLastAuthenticationError();
        $message = match (true) {
            $error instanceof CustomUserMessageAccountStatusException => $error->getMessageKey(),
            $error !== null => 'Wrong name or password.',
            default => null,
        };

        return $this->render('login.html.twig', ['name' => $authentication->getLastUsername(), 'error' => $message]);
    }

    #[Route('/loans', name: 'loans', methods: ['GET'])]
    public function loans(#[CurrentUser] Member $member, Library $library): Response
    {
        $loans = $library->loansOf($member->name);

        // Chapter 84: and the member's reservations; a loan someone waits for can't be extended.
        return $this->render('loans.html.twig', ['loans' => $loans, 'reserved' => $library->reserved($loans), 'holds' => $library->holdsOf($member)]);
    }

    /** Chapter 84: the member cancels one of their reservations; anyone else's is a 404. */
    #[Route('/reservations/cancel', name: 'cancel_hold', methods: ['POST'])]
    public function cancelHold(#[CurrentUser] Member $member, Request $request, Library $library, ClockInterface $clock): Response
    {
        try {
            [$hold] = $library->cancelHold($request->request->getInt('hold'), $member, $clock->now());
        } catch (HoldNotFound) {
            throw $this->createNotFoundException();
        }
        $this->addFlash('notice', "Your reservation of \"{$hold->book->title}\" is cancelled.");

        return $this->redirectToRoute('loans', status: Response::HTTP_SEE_OTHER);
    }

    /**
     * Sign-up. The answer is the same whether the address is new or already has an account, so the
     * page can't be used to find out who is a member: the email tells the address's owner which it was.
     */
    #[Route('/signup', name: 'signup', methods: ['GET', 'POST'])]
    public function signUp(Request $request): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('account');
        }
        $fields = ['name' => trim($request->request->getString('name')), 'email' => trim($request->request->getString('email'))];
        if ($request->isMethod('POST')) {
            $this->limit($request);
            try {
                $member = $this->accounts->signUp($fields['name'], $fields['email'], $request->request->getString('password'));
                $this->mail->confirm($member, $fields['email']);
            } catch (EmailInUse) {
                $owner = $this->members->findByEmail($fields['email']);
                if ($owner !== null) {
                    $this->mail->alreadyMember($owner);
                }
            } catch (InvalidArgumentException $e) {
                return $this->render('signup.html.twig', $fields + ['error' => $e->getMessage()], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
            }
            $this->addFlash('notice', "Almost done: Shelf has sent a link to {$fields['email']}. Open it within two days to confirm your address.");

            return $this->redirectToRoute('login', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('signup.html.twig', $fields + ['error' => null]);
    }

    /**
     * The link from a confirm email. GET only shows a button: mail programs open links to check them,
     * and a GET must never change anything (chapter 47). The form posts back to the same signed address.
     */
    #[Route('/confirm', name: 'confirm', methods: ['GET', 'POST'])]
    public function confirm(Request $request): Response
    {
        $member = $this->links->confirmed($request);
        if ($member === null) {
            return $this->render('link.html.twig', [], new Response(status: Response::HTTP_GONE));
        }
        $email = $request->query->getString('email');
        if ($request->isMethod('POST')) {
            $owner = $this->members->findByEmail($email);
            if ($owner !== null && $owner !== $member) {
                return $this->render('confirm.html.twig', ['person' => $member, 'email' => $email, 'error' => 'This address belongs to another account.'], new Response(status: Response::HTTP_CONFLICT));
            }
            $member->confirmEmail($email);
            $this->members->update($member);
            $this->addFlash('notice', "Your address $email is confirmed.");

            return $this->redirectToRoute($this->getUser() !== null ? 'account' : 'login', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('confirm.html.twig', ['person' => $member, 'email' => $email, 'error' => null]);
    }

    /** "Forgot your password?": the same answer for every address, known or not. */
    #[Route('/forgot', name: 'forgot', methods: ['GET', 'POST'])]
    public function forgot(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $this->limit($request);
            $email = trim($request->request->getString('email'));
            $member = $this->members->findByEmail($email);
            if ($member !== null) {
                $this->mail->reset($member);
            }
            $this->addFlash('notice', "If an account has the address $email, Shelf has sent it a link. It works for an hour.");

            return $this->redirectToRoute('forgot', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('forgot.html.twig');
    }

    /** The link from a reset email: a form for the new password, which posts to the same signed address. */
    #[Route('/reset', name: 'reset', methods: ['GET', 'POST'])]
    public function reset(Request $request): Response
    {
        $member = $this->links->resetting($request);
        if ($member === null) {
            return $this->render('link.html.twig', [], new Response(status: Response::HTTP_GONE));
        }
        if ($request->isMethod('POST')) {
            try {
                $this->accounts->setPassword($member->name, $request->request->getString('password'));
            } catch (InvalidArgumentException $e) {
                return $this->render('reset.html.twig', ['person' => $member, 'error' => $e->getMessage()], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
            }
            // The link came to the member's inbox, so the address is theirs: confirmed too.
            $member->confirmEmail((string) $member->email);
            $this->members->update($member);
            $this->addFlash('notice', 'Your new password is saved. Log in with it.');

            return $this->redirectToRoute('login', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('reset.html.twig', ['person' => $member, 'error' => null]);
    }

    #[Route('/account', name: 'account', methods: ['GET'])]
    public function account(): Response
    {
        return $this->render('account.html.twig', ['errors' => []]);
    }

    /**
     * A new password ends every session of the member: Symfony sees that the hash has changed, so a
     * session someone else holds, on another computer, is over. This one starts again at once.
     */
    #[Route('/account/password', name: 'account_password', methods: ['POST'])]
    public function changePassword(#[CurrentUser] Member $member, Request $request, Security $security): Response
    {
        try {
            $this->accounts->changePassword($member->name, $request->request->getString('current'), $request->request->getString('password'));
        } catch (InvalidArgumentException $e) {
            return $this->accountError('password', $e);
        }
        $security->login($member, 'form_login', 'main');

        return $this->accountDone('Your password is changed.');
    }

    /** A new address is the member's only once its link is used; until then, the old one stays. */
    #[Route('/account/email', name: 'account_email', methods: ['POST'])]
    public function changeEmail(#[CurrentUser] Member $member, Request $request): Response
    {
        $this->limit($request);
        $email = trim($request->request->getString('email'));
        try {
            $this->accounts->checkCurrent($member->name, $request->request->getString('current'));
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                throw new InvalidArgumentException('This is not an email address.');
            }
        } catch (InvalidArgumentException $e) {
            return $this->accountError('email', $e);
        }
        $this->mail->confirm($member, $email);

        return $this->accountDone("Shelf has sent a link to $email. Your address changes when you open it.");
    }

    #[Route('/account/delete', name: 'account_delete', methods: ['POST'])]
    public function delete(#[CurrentUser] Member $member, Request $request, Library $library, Security $security): Response
    {
        try {
            $this->accounts->checkCurrent($member->name, $request->request->getString('current'));
            $library->removeMember($member);
        } catch (InvalidArgumentException $e) {
            return $this->accountError('delete', $e);
        }
        $security->logout(validateCsrfToken: false); // the form's token was checked already, by CheckCsrf
        $this->addFlash('notice', 'Your account is deleted. Goodbye, and thank you for reading with us.');

        return $this->redirectToRoute('books', status: Response::HTTP_SEE_OTHER);
    }

    private function accountError(string $form, InvalidArgumentException $e): Response
    {
        return $this->render('account.html.twig', ['errors' => [$form => $e->getMessage()]], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    private function accountDone(string $message): Response
    {
        $this->addFlash('notice', $message);

        return $this->redirectToRoute('account', status: Response::HTTP_SEE_OTHER);
    }

    /** At most a few emails per hour from one visitor's address; then a 429, until the hour has passed. */
    private function limit(Request $request): void
    {
        $limit = $this->accountMailLimiter->create($request->getClientIp())->consume();
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp() - time(), 'Too many emails from this address. Please try again later.');
        }
    }
}
