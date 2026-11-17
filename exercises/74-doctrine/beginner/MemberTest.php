<?php

declare(strict_types=1);

use Club\Member;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Member.php');

final class MemberTest extends TestCase
{
    private EntityManager $entities;

    protected function setUp(): void
    {
        $config = ORMSetup::createAttributeMetadataConfig([], isDevMode: true);
        $config->enableNativeLazyObjects(true);
        $this->entities = new EntityManager(DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $config), $config);
        new SchemaTool($this->entities)->createSchema([$this->entities->getClassMetadata(Member::class)]);
    }

    public function testAMemberIsStoredInTheMembersTable(): void
    {
        $this->entities->persist(new Member('Amira', new DateTimeImmutable('2026-01-15'), 'amira@example.org'));
        $this->entities->flush();

        $row = $this->entities->getConnection()->fetchAssociative('SELECT * FROM members');
        self::assertSame(['id' => 1, 'name' => 'Amira', 'joined' => '2026-01-15', 'email' => 'amira@example.org'], $row);
    }

    public function testAMemberComesBackAsAnObject(): void
    {
        $this->entities->persist(new Member('Jonas', new DateTimeImmutable('2026-02-01')));
        $this->entities->flush();
        $this->entities->clear();

        $jonas = $this->entities->getRepository(Member::class)->findOneBy(['name' => 'Jonas']);

        self::assertNotNull($jonas);
        self::assertSame('2026-02-01', $jonas->joined->format('Y-m-d'));
        self::assertNull($jonas->email);
        self::assertSame(1, $jonas->id);
    }

    public function testANameOnlyOnce(): void
    {
        $this->entities->persist(new Member('Ana', new DateTimeImmutable('2026-01-01')));
        $this->entities->persist(new Member('Ana', new DateTimeImmutable('2026-03-01')));

        $this->expectException(UniqueConstraintViolationException::class);
        $this->entities->flush();
    }
}
