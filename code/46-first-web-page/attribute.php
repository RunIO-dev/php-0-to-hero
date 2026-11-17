<?php

declare(strict_types=1);

// In an attribute, a quote would end the value early: escaping turns it into &quot;.

$title = 'Kindred" onmouseover="alert(1)';

echo '<input name="title" value="', htmlspecialchars($title), '">', "\n";
