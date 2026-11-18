<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Chapter 83: loans can be extended and come back, and members can be blocked. Columns added in
 * place, as in chapter 82; the loans that exist stay out, never extended, and nobody is blocked.
 */
final class Version20261118120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renewals and returns of loans; blocked members';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE loans ADD COLUMN renewals INTEGER DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE loans ADD COLUMN returned_on DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE members ADD COLUMN blocked BOOLEAN DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE members DROP COLUMN blocked');
        $this->addSql('ALTER TABLE loans DROP COLUMN returned_on');
        $this->addSql('ALTER TABLE loans DROP COLUMN renewals');
    }
}
