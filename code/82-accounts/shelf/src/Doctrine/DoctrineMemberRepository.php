<?php

declare(strict_types=1);

namespace Shelf\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\MemberRepository;

/** The members, through Doctrine (chapter 74), with their password hashes (61) and emails (82). */
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
        // Chapter 82: UPDATE goes straight to the table; a Member already loaded still holds the old hash.
        $member = $this->find($name);
        if ($member !== null) {
            $this->entities->refresh($member);
        }
    }

    public function findByEmail(string $email): ?Member
    {
        return $this->entities->getRepository(Member::class)->findOneBy(['email' => Member::normalEmail($email)]);
    }

    public function findById(int $id): ?Member
    {
        return $this->entities->find(Member::class, $id);
    }

    public function update(Member $member): void
    {
        $this->entities->flush();
    }

    public function remove(Member $member): void
    {
        $this->entities->remove($member);
        $this->entities->flush();
    }
}
