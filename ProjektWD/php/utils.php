<?php
function demand($price) {
    $A = 10;
    $B = 4000;
    if ($price - $A == 0) return 0;
    return floor($B / ($price - $A));
}
