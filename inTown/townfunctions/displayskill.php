<?php 
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

$characterObject = new Character($charId);
$townId = $characterObject->townId;
if($townId != null){
	$townObject = new Towns($townId);
	$dayNumber = $townObject->dayNumber;
}
else{
	$dayNumber = 0;
}
$skillId = filter_input(INPUT_POST, "skillid");

$currentSkill = new Skill($skillId);
$skillName = "<h4><b>" . str_replace("_", " ", $currentSkill->getName()) . "</b></h4>";
$skillDescription = $currentSkill->getDescription();

$skillArray = array('name' => $skillName,
'description' => $skillDescription,
'skillId' => $skillId,
'townId' => $townId,
'day' => $dayNumber);

echo json_encode($skillArray);


?>