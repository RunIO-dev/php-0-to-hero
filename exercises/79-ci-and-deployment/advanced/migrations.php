<?php

declare(strict_types=1);

// Before CI deploys on its own (chapter 79), it reads the new migrations: a statement that removes or
// renames data needs a person, a backup and a plan. needsCare($sql) takes a migration's SQL and returns
// those statements, in their order, each on one line: trimmed, every run of white space as one space,
// without its ";". Words in upper or lower case alike. A statement needs care when it:
// - drops a table, an index or a column:      DROP TABLE ..., DROP INDEX ..., ALTER TABLE ... DROP ...
// - renames:                                  ALTER TABLE ... RENAME ..., RENAME TABLE ...
// - empties a table:                          TRUNCATE ...
// - deletes or updates every row:             DELETE FROM ... or UPDATE ... without WHERE
// Statements end at ";". A ";" or a word inside a string ('...', with '' for a quote) or a comment
// (-- to the end of the line, or /* ... */) is not SQL: it ends nothing, and needs no care. Comments are
// left out of the statements returned; strings stay as they were written.

/** @return list<string> */
function needsCare(string $sql): array
{
    return []; // TODO
}
