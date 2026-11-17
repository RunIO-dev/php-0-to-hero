-- Exercise 53, advanced: the members table, with rules the database checks.

CREATE TABLE members (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL UNIQUE COLLATE NOCASE,
    role TEXT NOT NULL DEFAULT 'reader' CHECK (role IN ('reader', 'staff')),
    joined TEXT NOT NULL CHECK (joined GLOB '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]')
) STRICT;
