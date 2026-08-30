<?php

$js = file_get_contents('captain_admin.js');
$pos = strpos($js, '.bundle.js');
echo substr($js, $pos - 200, 200) . "\n";
