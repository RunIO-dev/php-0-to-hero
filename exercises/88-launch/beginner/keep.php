<?php

declare(strict_types=1);

// Which backups to delete (chapter 88). Each backup is a folder named by its time, such as
// "2026-03-09T023000" (Y-m-d\THis). Given the names in the backups folder, in any order, return the
// names of the backups to delete: all but the newest $keep, the oldest first. A name that is not a
// time, such as "notes" or "2026-13-01T023000", is not a backup: never delete it.

/**
 * @param list<string> $names
 * @return list<string>
 */
function toDelete(array $names, int $keep): array
{
    return []; // TODO
}
