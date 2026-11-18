<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Chapter 86: the day each loan was lent, which the dashboard counts by, with an index. SQLite can't
 * add a NOT NULL column to a table, so Doctrine's SQL (doctrine:schema:update --dump-sql) builds the
 * table again and copies the rows. Its copy leaves lent_on out, which fails on the first row: the copy
 * here works the day out from the due date. A loan lasts its member's loan period (Role::loanDays()),
 * once more for each time it was extended.
 */
final class Version20261121090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'The day each loan was lent';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__loans AS SELECT id, due, book_id, member_id, renewals, returned_on, reminded_on FROM loans');
        $this->addSql('DROP TABLE loans');
        $this->addSql('CREATE TABLE loans (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, due DATE NOT NULL, book_id INTEGER NOT NULL, member_id INTEGER NOT NULL, renewals INTEGER DEFAULT 0 NOT NULL, returned_on DATE DEFAULT NULL, reminded_on DATE DEFAULT NULL, lent_on DATE NOT NULL, CONSTRAINT FK_82C24DBC16A2B381 FOREIGN KEY (book_id) REFERENCES books (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_82C24DBC7597D3FE FOREIGN KEY (member_id) REFERENCES members (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql("INSERT INTO loans (id, due, book_id, member_id, renewals, returned_on, reminded_on, lent_on)
            SELECT t.id, t.due, t.book_id, t.member_id, t.renewals, t.returned_on, t.reminded_on,
                   DATE(t.due, '-' || ((CASE m.role WHEN 'staff' THEN 28 ELSE 14 END) * (t.renewals + 1)) || ' days')
            FROM __temp__loans t JOIN members m ON m.id = t.member_id");
        $this->addSql('DROP TABLE __temp__loans');
        $this->addSql('CREATE INDEX IDX_82C24DBC7597D3FE ON loans (member_id)');
        $this->addSql('CREATE INDEX IDX_82C24DBC16A2B381 ON loans (book_id)');
        $this->addSql('CREATE INDEX loans_lent_on ON loans (lent_on)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX loans_lent_on');
        $this->addSql('ALTER TABLE loans DROP COLUMN lent_on');
    }
}
