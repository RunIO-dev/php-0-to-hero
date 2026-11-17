-- Members, and the loans that connect them to books (chapter 56). IF NOT EXISTS, as in 001.
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
