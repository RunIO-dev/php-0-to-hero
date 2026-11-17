-- Exercise 53, advanced: the members table, with rules the database checks.
--
-- id:     a whole number that identifies the member (the primary key).
-- name:   text, required, and no two members with the same name, in any case of ASCII letters.
-- role:   'reader' or 'staff', and nothing else; 'reader' when an INSERT leaves it out.
-- joined: the date the member joined, as text like '2026-03-02', required.
--         Check its shape with GLOB '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]'.
-- The table is STRICT, so a value of the wrong type is refused.
--
-- Check your work: composer exercise 53 advanced

CREATE TABLE members (
    id INTEGER PRIMARY KEY,
    name TEXT,
    role TEXT,
    joined TEXT
);
