-- The library the exercise tests use. Each test runs it in a new database in memory.
CREATE TABLE books (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL UNIQUE,
    author TEXT NOT NULL,
    year INTEGER NOT NULL,
    copies INTEGER NOT NULL DEFAULT 1
);

INSERT INTO books (title, author, year, copies) VALUES
    ('Things Fall Apart', 'Chinua Achebe', 1958, 2),
    ('Kindred', 'Octavia E. Butler', 1979, 0),
    ('Kitchen', 'Banana Yoshimoto', 1988, 1),
    ('Sula', 'Toni Morrison', 1973, 3),
    ('Beloved', 'Toni Morrison', 1987, 0),
    ('No Longer Human', 'Osamu Dazai', 1948, 1),
    ('The Remains of the Day', 'Kazuo Ishiguro', 1989, 2);
