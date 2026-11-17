-- The library for this chapter's examples.
CREATE TABLE books (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL UNIQUE,
    author TEXT NOT NULL,
    year INTEGER NOT NULL,
    copies INTEGER NOT NULL DEFAULT 1
) STRICT;

INSERT INTO books (title, author, year, copies) VALUES
    ('Things Fall Apart', 'Chinua Achebe', 1958, 2),
    ('Kindred', 'Octavia E. Butler', 1979, 0),
    ('Kitchen', 'Banana Yoshimoto', 1988, 1),
    ('Sula', 'Toni Morrison', 1973, 3);
