<?php 
require_once ("../connect.php");
require_once ("./verifyLogin.php");

$selected_class = filter_input(INPUT_POST, 'charClass');
$selected_name = filter_input(INPUT_POST, 'charName');
$selected_gender = filter_input(INPUT_POST, 'gender');


//Need to check if the user already has a character by the given name, if they do then don't allow them to create one with that name!

session_start();
$user = $_SESSION['login'];
$nameQuer = "SELECT * FROM `characters` WHERE `username`='$user' AND `character`='$selected_name'";
$check = mysqli_query($con, $nameQuer);

if (mysqli_num_rows($check) > 0)
{
	echo "Character name is taken";
}
else
{
	$sql = mysqli_query($con, "INSERT INTO `characters` VALUES(NULL, '$user', '$selected_name', '$selected_class', '$selected_gender', '0', '0', 'none', NULL, '0', '10', 'none', '0', '16', '16', '3.7')");
	if ($sql)
	{
	header ("Location: ../inTown/?locat=browseChars");
	}
	else
	{
		echo "There was a problem while trying to create the character";
	}
}

?>
<html>
<title>Character Browse/Create</title>
<head>
</head>
<body>
</body>
</html>
