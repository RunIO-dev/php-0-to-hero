<?php

$genres = ['crime', 'poetry', 'sf'];
$text = implode(',', $genres);
echo $text, "\n";

$tags = explode(',', 'history,africa,novel');
echo count($tags), " tags\n";
echo $tags[1], "\n";
