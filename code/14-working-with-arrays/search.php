<?php

$titles = ['Kindred', 'Beloved', 'Kitchen'];

var_dump(in_array('Beloved', $titles, true));
var_dump(in_array('Sula', $titles, true));

var_dump(array_search('Kitchen', $titles, true));
var_dump(array_search('Sula', $titles, true));
