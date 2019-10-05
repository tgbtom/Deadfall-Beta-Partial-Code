<?php
require_once ("../../model/structures.php");
require_once ("../../data/buildings.php");
require_once ("../../data/items.php");
require_once ("../../model/database.php");

session_start();
//gets the user and current character, and stores them in local variables
if (isset($_SESSION['login'])){
	$user = $_SESSION['login'];
}

if (!isset($_SESSION['char_id']))
{
	$_SESSION['char'] = '';
	$_SESSION['char_id'] = '';
}
else
{
	$char = $_SESSION['char'];
	$charId = $_SESSION['char_id'];
}
    
$dbCon = Database::getDB();

function getCharDetails()
{
	global $charId;
	global $user;
	global $dbCon;
	
	$query = 'SELECT * FROM `characters` WHERE `username` = :user AND `id` = :id';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':user', $user);
	$statement->bindValue(':id', $charId);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	return $result;
}


$x = filter_input(INPUT_POST, "x");

$decodedX = json_decode($x);

$charDetails = getCharDetails();
$charClass = $charDetails['class'];
$townId = $charDetails['town_id'];
$builtDetails = StructuresDB::getBuiltDetails($decodedX->s_name, $townId);

$copyOfStructure = new Structure($decodedX->s_name);

$name = $copyOfStructure->s_name;
$costs = $copyOfStructure->getItemCosts_string();


$buildingCosts = $copyOfStructure->getItemCosts_objects();
$resourceCostsString = '';                           
//Create a string to display the required items for the building
foreach ($buildingCosts->getItemCosts() as $value)
{
    $itemIdNeeded = $value->getItemId();
    $itemName = $itemsMaster[$itemIdNeeded][0];
	$itemAmountNeeded = $value->getItemAmount();
    $resourceCostsString .= "   <span class='individualCost'><img src='../images/items/" . $itemName . ".png' title='" . $itemName . "'> " . TownBankDB::getItemAmount($itemIdNeeded, $townId) . "/" . $itemAmountNeeded . "</span>";
}


$description = $copyOfStructure->getDescription();
$level = $builtDetails['Level'];
$maxLevel = $copyOfStructure->getMaxLevel();
$currentStructureAp = $builtDetails['Ap'];
$maxStructureAp = $copyOfStructure->getApCost();
$defence = $copyOfStructure->getDefence();

$affordable = StructuresDB::isStructureAffordable($copyOfStructure, $townId);


$publicArray = array('name' => $name,
'costs' => $resourceCostsString,
'description' => $description,
'level' => $level,
'maxLevel' => $maxLevel,
'currentAp' => $currentStructureAp,
'affordable' => $affordable,
'charClass' => $charClass,
'defence' => $defence,
'maxAp' => $maxStructureAp);

$x = json_encode($publicArray);

echo $x;
?>