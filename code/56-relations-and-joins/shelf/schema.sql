-- Shelf's tables (chapters 53 and 56). Shelf runs this file each time it opens its database;
-- IF NOT EXISTS makes that safe once the tables are there.
-- STRICT: SQLite refuses a value of the wrong type, as other databases do.
CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL UNIQUE COLLATE NOCASE,
    author TEXT NOT NULL,
    year INTEGER NOT NULL CHECK (year BETWEEN 1 AND 2100),
    copies INTEGER NOT NULL DEFAULT 1 CHECK (copies >= 0),
    isbn TEXT UNIQUE
) STRICT;

CREATE TABLE IF NOT EXISTS members (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL UNIQUE COLLATE NOCASE,
    role TEXT NOT NULL CHECK (role IN ('reader', 'staff'))
) STRICT;

-- One row per book lent: which book, to whom, and until when (an ISO date, 2026-03-16).
CREATE TABLE IF NOT EXISTS loans (
    id INTEGER PRIMARY KEY,
    book_id INTEGER NOT NULL REFERENCES books (id),
    member_id INTEGER NOT NULL REFERENCES members (id),
    due TEXT NOT NULL
) STRICT;
