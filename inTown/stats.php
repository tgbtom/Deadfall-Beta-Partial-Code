<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");
require_once ("../model/database.php");
require_once ("../model/structures.php");

$errorMessage = FILTER_INPUT(INPUT_GET, 'e');
if (isset($errorMessage))
{
	echo "<script type='text/javascript'>alert('$errorMessage');</script>";
}
?>

<html>
<head>

<link rel="stylesheet" type="text/css" href="mainDesignTown.css">
<link rel="stylesheet" type="text/css" href="../css/specialStructures.css">
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php 
$singleJoin = filter_input(INPUT_POST, 'selectedCharEdit');
if(!isset($singleJoin)){
	$singleJoin = filter_input(INPUT_GET, 'selectedCharEdit');
}

$_SESSION['char_id'] = $singleJoin;
$charId = $_SESSION['char_id'];

$characterObject = new Character($charId);

$playerName = $_SESSION['login'];
$charName = $characterObject->character;
$_SESSION["char"] = $charName;

$townId = $characterObject->townId; 

?>
</head>
<body>

<div class="Container">

	<?php 
	if($townId != null){
		include("../universal/header.php"); 
	}
	else{
		echo '<a href="../inTown/?locat=browseChars"> Return to Character Selection</a>';
	}
	?>
	
	<div class="centralBox">
		<div class="centralBoxTop">
			<h3 style="text-align:center;">Character <small>(*Skills cannot be removed when in town after day 1*)</small></h3>
		</div>
		<div>
        <!-- If we are not in town show character.php otherwise show a more restricted page -->
        <?php 

            include "character.php";

        ?>
		
		</div>
	
	</div>
</div>
	<?php
	Include ("../universal/hyperlinks.php");
	?>
</body>
</html>