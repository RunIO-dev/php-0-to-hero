-- The books table in MariaDB's words. The same table as Shelf's in SQLite (chapter 53).
DROP TABLE IF EXISTS books;

CREATE TABLE books (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL UNIQUE,
    author VARCHAR(200) NOT NULL,
    year SMALLINT NOT NULL CHECK (year BETWEEN 1 AND 2100),
    copies SMALLINT NOT NULL DEFAULT 1 CHECK (copies >= 0),
    isbn CHAR(13) NULL UNIQUE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO books (title, author, year, copies) VALUES
    ('Things Fall Apart', 'Chinua Achebe', 1958, 2),
    ('Kindred', 'Octavia E. Butler', 1979, 0),
    ('Kitchen', 'Banana Yoshimoto', 1988, 1),
    ('Parable of the Sower', 'Octavia E. Butler', 1993, 1);
