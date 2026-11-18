<?php

declare(strict_types=1);

// Before a deploy, check the production php.ini (chapter 78). problems($ini) takes the file's text and
// returns what is wrong, in this order, each message exactly as here:
//   "display_errors must be Off"             (missing counts as wrong: the default shows errors)
//   "log_errors must be On"
//   "expose_php must be Off"
//   "opcache.validate_timestamps must be 0"  (missing counts as wrong: the default is 1)
// On, Off, true, false, yes, no, 1, 0 and "" all appear in real files; INI_SCANNER_TYPED reads them.

/** @return list<string> */
function problems(string $ini): array
{
    return []; // TODO
}
