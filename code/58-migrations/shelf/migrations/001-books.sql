-- The books (chapter 53). IF NOT EXISTS: a database made by schema.sql in chapters 54 to 57
-- already has this table, and keeps it as it is.
CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL UNIQUE COLLATE NOCASE,
    author TEXT NOT NULL,
    year INTEGER NOT NULL CHECK (year BETWEEN 1 AND 2100),
    copies INTEGER NOT NULL DEFAULT 1 CHECK (copies >= 0),
    isbn TEXT UNIQUE
) STRICT;
