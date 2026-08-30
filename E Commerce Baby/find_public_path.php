<?php

$js = file_get_contents('captain_admin.js');
$pos = strpos($js, '.bundle.js');
if ($pos !== false) {
    echo "Around .bundle.js:\n";
    echo substr($js, max(0, $pos - 300), 600) . "\n";
}

$pos2 = strpos($js, 'n.p =');
if ($pos2 !== false) {
    echo "n.p = " . substr($js, $pos2, 100) . "\n";
}
