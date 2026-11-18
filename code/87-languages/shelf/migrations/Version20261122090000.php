<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Chapter 87: the language each member reads Shelf in, for the emails that are sent while nobody is
 * on a page. A column with a default: SQLite can add it to a table with rows, and every member who
 * exists starts in English. The SQL is Doctrine's own (doctrine:schema:update --dump-sql).
 */
final class Version20261122090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'The language of each member';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE members ADD COLUMN language VARCHAR(2) DEFAULT 'en' NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE members DROP COLUMN language');
    }
}
