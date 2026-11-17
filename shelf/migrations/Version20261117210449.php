<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Shelf's tables (chapter 74), as doctrine:migrations:diff wrote them from the entities. They take
 * the place of chapter 58's SQL migrations, for a new database.
 */
final class Version20261117210449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Shelf's tables: books, members, loans and API tokens";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE api_tokens (hash VARCHAR(255) NOT NULL, expires DATETIME NOT NULL, member_id INTEGER NOT NULL, PRIMARY KEY (hash), CONSTRAINT FK_2CAD560E7597D3FE FOREIGN KEY (member_id) REFERENCES members (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2CAD560E7597D3FE ON api_tokens (member_id)');
        $this->addSql('CREATE TABLE books (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL COLLATE "NOCASE", author VARCHAR(255) NOT NULL, year INTEGER NOT NULL, copies INTEGER NOT NULL, isbn VARCHAR(13) DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A1B2A922B36786B ON books (title)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A1B2A92CC1CF4E6 ON books (isbn)');
        $this->addSql('CREATE TABLE loans (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, due DATE NOT NULL, book_id INTEGER NOT NULL, member_id INTEGER NOT NULL, CONSTRAINT FK_82C24DBC16A2B381 FOREIGN KEY (book_id) REFERENCES books (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_82C24DBC7597D3FE FOREIGN KEY (member_id) REFERENCES members (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_82C24DBC16A2B381 ON loans (book_id)');
        $this->addSql('CREATE INDEX IDX_82C24DBC7597D3FE ON loans (member_id)');
        $this->addSql('CREATE TABLE members (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, password_hash VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE "NOCASE", role VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_45A0D2FF5E237E06 ON members (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE api_tokens');
        $this->addSql('DROP TABLE books');
        $this->addSql('DROP TABLE loans');
        $this->addSql('DROP TABLE members');
    }
}
