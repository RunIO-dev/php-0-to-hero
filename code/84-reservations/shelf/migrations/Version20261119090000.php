<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Chapter 84: reservations. A new table, so nothing that exists changes, and the SQL is Doctrine's own
 * (doctrine:migrations:diff); holds_queue finds a book's queue without reading every reservation.
 */
final class Version20261119090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reservations: a queue for each book';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE holds (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, status VARCHAR(255) NOT NULL, ready_until DATE DEFAULT NULL, placed_on DATE NOT NULL, book_id INTEGER NOT NULL, member_id INTEGER NOT NULL, CONSTRAINT FK_857590A516A2B381 FOREIGN KEY (book_id) REFERENCES books (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_857590A57597D3FE FOREIGN KEY (member_id) REFERENCES members (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX holds_queue ON holds (book_id, status)');
        $this->addSql('CREATE INDEX IDX_857590A516A2B381 ON holds (book_id)');
        $this->addSql('CREATE INDEX IDX_857590A57597D3FE ON holds (member_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE holds');
    }
}
