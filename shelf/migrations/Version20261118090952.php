<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Chapter 82: members get an email address and "confirmed". doctrine:migrations:diff wrote this for
 * SQLite as a new members table: copied, dropped and made again. Dropping members would delete every
 * API token with it (ON DELETE CASCADE), and break the loans that point to it. SQLite can add a
 * column in place, so that is what this does. Members who were there before are confirmed.
 */
final class Version20261118090952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Members' email addresses, and whether each is confirmed";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE members ADD COLUMN email VARCHAR(255) DEFAULT NULL COLLATE "NOCASE"');
        $this->addSql('ALTER TABLE members ADD COLUMN confirmed BOOLEAN DEFAULT 1 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_45A0D2FFE7927C74 ON members (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_45A0D2FFE7927C74');
        $this->addSql('ALTER TABLE members DROP COLUMN confirmed');
        $this->addSql('ALTER TABLE members DROP COLUMN email');
    }
}
