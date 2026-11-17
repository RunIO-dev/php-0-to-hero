<?php

declare(strict_types=1);

// A voter answers one kind of question about one kind of thing: may this user edit this review?
// The access decision manager asks every voter, and decides: here, yes if one says yes.

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\InMemoryUser;

require __DIR__ . '/vendor/autoload.php';

final readonly class Review
{
    public function __construct(public string $author, public string $text) {}
}

/** @extends Voter<string, Review> */
final class ReviewVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'EDIT' && $subject instanceof Review;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $token->getUserIdentifier() === $subject->author; // only its author
    }
}

$decide = new AccessDecisionManager([new ReviewVoter()]);
$review = new Review('Amira', 'Read it in one night.');

foreach (['Amira', 'Jonas'] as $name) {
    $token = new UsernamePasswordToken(new InMemoryUser($name, null, ['ROLE_READER']), 'main', ['ROLE_READER']);
    echo "$name may edit Amira's review: ", $decide->decide($token, ['EDIT'], $review) ? 'yes' : 'no', "\n";
}
