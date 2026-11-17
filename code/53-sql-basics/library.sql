-- A small library for this chapter's examples. db.php runs this file in a new, empty database.
CREATE TABLE books (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL UNIQUE,
    author TEXT NOT NULL,
    year INTEGER NOT NULL,
    copies INTEGER NOT NULL DEFAULT 1,
    isbn TEXT
);

INSERT INTO books (title, author, year, copies, isbn) VALUES
    ('Things Fall Apart', 'Chinua Achebe', 1958, 2, '9780385474542'),
    ('Kindred', 'Octavia E. Butler', 1979, 0, '9780807083697'),
    ('Kitchen', 'Banana Yoshimoto', 1988, 1, NULL),
    ('Parable of the Sower', 'Octavia E. Butler', 1993, 1, NULL),
    ('Sula', 'Toni Morrison', 1973, 3, NULL),
    ('Beloved', 'Toni Morrison', 1987, 1, NULL);
