<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Chapter 85: Messenger's queue, and the day of the last reminder of each loan and reservation. A new
 * table and two new columns: nothing that exists changes, and every loan starts with no reminder sent.
 * The SQL is Doctrine's own (doctrine:schema:update --dump-sql).
 */
final class Version20261120090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'The message queue; reminders sent for loans and reservations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
        $this->addSql('ALTER TABLE holds ADD COLUMN notified_on DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE loans ADD COLUMN reminded_on DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE loans DROP COLUMN reminded_on');
        $this->addSql('ALTER TABLE holds DROP COLUMN notified_on');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
