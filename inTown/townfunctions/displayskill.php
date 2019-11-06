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

$skillId = filter_input(INPUT_POST, "skillid");

$currentSkill = new Skill($skillId);
$skillName = "<h4><b>" . $currentSkill->getName() . "</b></h4>";
$skillDescription = $currentSkill->getDescription();

$skillArray = array('name' => $skillName,
'description' => $skillDescription,
'skillId' => $skillId);

echo json_encode($skillArray);


?>