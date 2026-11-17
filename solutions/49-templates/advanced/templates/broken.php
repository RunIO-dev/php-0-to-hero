<?php

declare(strict_types=1);

return static function (): void { ?>
<p>Half a page
<?php throw new RuntimeException('The template failed');
};
