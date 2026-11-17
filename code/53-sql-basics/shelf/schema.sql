-- Shelf's tables (chapter 53). From chapter 54, Shelf runs this file each time it opens its
-- database; IF NOT EXISTS makes that safe once the table is there.
-- STRICT: SQLite refuses a value of the wrong type, as other databases do.
CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL UNIQUE COLLATE NOCASE,
    author TEXT NOT NULL,
    year INTEGER NOT NULL CHECK (year BETWEEN 1 AND 2100),
    copies INTEGER NOT NULL DEFAULT 1 CHECK (copies >= 0),
    isbn TEXT UNIQUE
) STRICT;
