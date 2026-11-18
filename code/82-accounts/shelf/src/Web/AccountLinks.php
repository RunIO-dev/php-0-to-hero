<?php

declare(strict_types=1);

namespace Shelf\Web;

use DateInterval;
use Shelf\Member;
use Shelf\MemberRepository;
use Symfony\Component\HttpFoundation\Exception\SignedUriException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The links in Shelf's emails (chapter 82), as signed addresses: UriSigner adds an expiry time and a
 * hash of the whole address, made with the app's secret. Nobody can make or change such a link
 * without the secret, so the database needs no table of tokens.
 * - confirm: this member owns this email address; valid for two days.
 * - reset: a new password for this member; valid for an hour, and only while the password is the one
 *   it was made for: the link carries a fingerprint of the password's hash, so it works once.
 */
final readonly class AccountLinks
{
    public const string CONFIRM_FOR = 'P2D';
    public const string RESET_FOR = 'PT1H';

    public function __construct(
        private UriSigner $signer,
        private UrlGeneratorInterface $urls,
        private MemberRepository $members,
    ) {}

    public function confirm(Member $member, string $email): string
    {
        $url = $this->urls->generate('confirm', ['member' => $member->id(), 'email' => Member::normalEmail($email)], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->signer->sign($url, new DateInterval(self::CONFIRM_FOR));
    }

    public function reset(Member $member): string
    {
        $check = self::fingerprint($this->members->passwordHash($member->name));
        $url = $this->urls->generate('reset', ['member' => $member->id(), 'check' => $check], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->signer->sign($url, new DateInterval(self::RESET_FOR));
    }

    /** The member a confirm link is for, or null if the link is wrong, changed or too old. */
    public function confirmed(Request $request): ?Member
    {
        return $this->valid($request) ? $this->members->findById($request->query->getInt('member')) : null;
    }

    /** The member a reset link is for, or null if the link is wrong, too old, or already used. */
    public function resetting(Request $request): ?Member
    {
        $member = $this->valid($request) ? $this->members->findById($request->query->getInt('member')) : null;
        if ($member === null || !hash_equals(self::fingerprint($this->members->passwordHash($member->name)), $request->query->getString('check'))) {
            return null;
        }

        return $member;
    }

    /** A short sign of the current hash: it changes when the password changes. */
    private static function fingerprint(?string $hash): string
    {
        return substr(hash('sha256', $hash ?? ''), 0, 16);
    }

    private function valid(Request $request): bool
    {
        try {
            $this->signer->verify($request);

            return true;
        } catch (SignedUriException) {
            return false;
        }
    }
}
