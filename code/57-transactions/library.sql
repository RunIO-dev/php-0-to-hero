-- A small library for this chapter's examples: books and loans.
CREATE TABLE books (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL UNIQUE,
    copies INTEGER NOT NULL CHECK (copies >= 0)
) STRICT;

CREATE TABLE loans (
    id INTEGER PRIMARY KEY,
    book_id INTEGER NOT NULL REFERENCES books (id),
    member TEXT NOT NULL,
    due TEXT NOT NULL
) STRICT;

INSERT INTO books (title, copies) VALUES ('Kindred', 1), ('Sula', 2);
