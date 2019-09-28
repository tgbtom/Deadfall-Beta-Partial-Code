<?php 
require_once ("../../data/items.php");
require_once ("../../model/database.php");

$x = filter_input(INPUT_POST, "x");
$y = filter_input(INPUT_POST, "y");

$id = $x;
$itemName = $itemsMaster[$x][0];
$itemDescription = $itemsMaster[$x][4];
$itemMass = $itemsMaster[$x][2];

$compressedX = array(
    'i' => $y,
    'id' => $id,
    'name' => $itemName, 
    'desc' => $itemDescription, 
    'mass' => $itemMass
);

$x = json_encode($compressedX);

echo $x;

?>