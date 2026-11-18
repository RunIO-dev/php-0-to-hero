<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Chapter 81: indexes for sorting by author and by year. doctrine:migrations:diff wrote this
 * for SQLite as a copy of the whole books table, dropped and made again: SQLite can't change a table
 * in place, so Doctrine plays safe. An index needs no such thing, so these are the two lines it takes.
 */
final class Version20261118084441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Indexes on the books\' author and year';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX books_author ON books (author)');
        $this->addSql('CREATE INDEX books_year ON books (year)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX books_author');
        $this->addSql('DROP INDEX books_year');
    }
}
