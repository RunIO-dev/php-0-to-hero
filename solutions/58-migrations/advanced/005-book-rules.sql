-- Exercise 58, advanced: rules for a table that holds data, by rebuilding it.
CREATE TABLE new_books (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL UNIQUE COLLATE NOCASE,
    copies INTEGER NOT NULL CHECK (copies >= 0)
) STRICT;

INSERT INTO new_books (id, title, copies) SELECT id, title, copies FROM books;

DROP TABLE books;

ALTER TABLE new_books RENAME TO books;
