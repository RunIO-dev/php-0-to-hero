-- Exercise 58, beginner: a migration for the members table (see ../library.php).
ALTER TABLE members ADD COLUMN email TEXT;
ALTER TABLE members ADD COLUMN joined TEXT NOT NULL DEFAULT '2026-01-01';
