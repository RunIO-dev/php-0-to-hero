<?php

declare(strict_types=1);

// Exercise 49, beginner: a member's card as a template.
//
// Return a function with three typed parameters: string $name, string $role, int $loans.
// It prints, with every text escaped:
//   <div class="card"><h2>NAME</h2><p>ROLE · LOANS loans</p></div>
// Nothing may be printed when the file is loaded, only when the function is called.

return static function (): void { ?>
<div class="card"></div>
<?php };
// Check your work: composer exercise 49 beginner
