<?php
function demand($price) {
    $A = 10;
    $B = 4000;
    if ($price <= $A) return 0; // zapobiega dzieleniu przez 0 i ekstremalnemu popytowi
    return floor($B / ($price - $A));
}


