<html>
<?php
//in the equation x is equal to characters current level
$x = 1;
$xpReq = 100 + (100 * $x);

function getRequiredXp($currentLevel){
    // return floor(100 + (100 * $currentLevel) + (0.015 * (100 * $currentLevel)));
    return floor(100 + 1.015 * (100 * $currentLevel));
}
?>
</html>