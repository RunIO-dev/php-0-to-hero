<?php

$codes = ['10', '20', '30'];

var_dump(in_array('1e1', $codes));
var_dump(in_array('1e1', $codes, true));
