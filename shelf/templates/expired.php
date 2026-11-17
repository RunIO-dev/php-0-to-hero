<?php

declare(strict_types=1);

// A form without the right CSRF token: sent by another site, or opened before the session ended.

return static function (): void { ?>
    <h1>This form has expired</h1>
    <p class="error" role="alert">Nothing was saved.</p>
    <p>Please <a href="/add">open the form again</a> and send it once more.</p>
<?php };
