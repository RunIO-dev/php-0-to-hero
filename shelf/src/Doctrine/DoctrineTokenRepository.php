<?php

declare(strict_types=1);

namespace Shelf\Doctrine;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Shelf\ApiToken;
use Shelf\MemberNotFound;
use Shelf\MemberRepository;
use Shelf\TokenRepository;

/** The API's tokens (chapter 69), through Doctrine (chapter 74). */
final readonly class DoctrineTokenRepository implements TokenRepository
{
    public function __construct(private EntityManagerInterface $entities, private MemberRepository $members) {}

    public function add(string $hash, string $member, DateTimeImmutable $expires): void
    {
        $owner = $this->members->find($member) ?? throw new MemberNotFound('There is no member named "{name}"', ['name' => $member]);
        $this->entities->persist(new ApiToken($hash, $owner, $expires));
        $this->entities->flush();
    }

    public function memberName(string $hash, DateTimeImmutable $now): ?string
    {
        $token = $this->entities->find(ApiToken::class, $hash);

        return $token !== null && $token->expires > $now ? $token->member->name : null;
    }

    public function remove(string $hash): void
    {
        $this->entities->createQuery('DELETE ' . ApiToken::class . ' t WHERE t.hash = :hash')->setParameter('hash', $hash)->execute();
    }
}
