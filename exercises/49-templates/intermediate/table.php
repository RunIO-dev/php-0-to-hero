<?php

declare(strict_types=1);

// Exercise 49, intermediate: a table template.
//
// Return a function with the parameters string $caption and string ...$titles (any number of
// titles). It prints, escaped:
//   <table><caption>CAPTION</caption>
//   <tr><td>TITLE</td></tr>        one row per title, in their order
//   </table>
// With no titles at all, it prints <p class="empty">No books yet.</p> instead of the table.
// Use if/else and foreach with their alternative syntax (if (...): else: endif;).

return static function (string $caption, string ...$titles): void { ?>
<table><caption></caption>
</table>
<?php };
// Check your work: composer exercise 49 intermediate
