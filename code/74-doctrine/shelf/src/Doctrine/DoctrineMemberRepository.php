<?php

declare(strict_types=1);

namespace Shelf\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\MemberRepository;

/** The members, through Doctrine (chapter 74), with their password hashes (61). */
final readonly class DoctrineMemberRepository implements MemberRepository
{
    public function __construct(private EntityManagerInterface $entities) {}

    public function find(string $name): ?Member
    {
        return $this->entities->getRepository(Member::class)->findOneBy(['name' => trim($name)]);
    }

    public function add(Member $member): void
    {
        $this->entities->persist($member);
        $this->entities->flush();
    }

    public function passwordHash(string $name): ?string
    {
        $hash = $this->entities->createQuery('SELECT m.passwordHash FROM ' . Member::class . ' m WHERE m.name = :name')
            ->setParameter('name', trim($name))
            ->getOneOrNullResult();

        return is_array($hash) && is_string($hash['passwordHash'] ?? null) ? $hash['passwordHash'] : null;
    }

    public function changePasswordHash(string $name, string $hash): void
    {
        $changed = $this->entities->createQuery('UPDATE ' . Member::class . ' m SET m.passwordHash = :hash WHERE m.name = :name')
            ->setParameters(['hash' => $hash, 'name' => trim($name)])
            ->execute();
        if ($changed === 0) {
            throw new MemberNotFound('There is no member named "' . trim($name) . '"');
        }
    }
}
