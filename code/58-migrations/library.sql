-- The library as it was before this chapter: books and members, with some data to keep.
CREATE TABLE books (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL UNIQUE,
    author TEXT NOT NULL
) STRICT;

CREATE TABLE members (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL UNIQUE
) STRICT;

CREATE TABLE loans (
    id INTEGER PRIMARY KEY,
    book_id INTEGER NOT NULL REFERENCES books (id),
    member_id INTEGER NOT NULL REFERENCES members (id),
    due TEXT NOT NULL
) STRICT;

INSERT INTO books (title, author) VALUES ('Things Fall Apart', 'Chinua Achebe'), ('Kindred', 'Octavia E. Butler');
INSERT INTO members (name) VALUES ('Amira'), ('Jonas');
INSERT INTO loans (book_id, member_id, due) VALUES (2, 1, '2026-03-16'), (1, 2, '2026-03-20');
