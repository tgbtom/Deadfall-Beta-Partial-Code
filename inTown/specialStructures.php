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

<?php 



//All information here should be retrieved from database simply using the login session and character session

$playerName = $_SESSION['login'];
$charName = $_SESSION['char'];
$charId = $_SESSION['char_id'];


//Set Variables which correspond with the character that is in session (town name, level, class, etc.)
$query1 = "SELECT * FROM `characters` WHERE `id` = '$charId' AND `username` = '$playerName'";
$query2 = mysqli_query($con, $query1);

	while ($row = mysqli_fetch_assoc($query2))
	{
		$townId = $row['town_id'];
		$townName = Towns::getTownNameById($townId);
		$charLevel = $row['level'];
		$charClass = $row['class'];
	}	

	$specialFunction = filter_input(INPUT_POST, "specialFunction");
	if (isset($specialFunction)){
		switch($specialFunction){

			case 'convertWoodToMetal':
			if (TownBankDB::getItemAmount(2, $townId) >= 3){
				TownBankDB::removeItem(2, 3, $townId);
				TownBankDB::addItem(3, 1, $townId);
			}
			break;

			case 'convertWoodToBrick':
			if (TownBankDB::getItemAmount(2, $townId) >= 3){
				TownBankDB::removeItem(2, 3, $townId);
				TownBankDB::addItem(10, 1, $townId);
			}
			break;

			case 'convertMetalToWood':
			if (TownBankDB::getItemAmount(3, $townId) >= 3){
				TownBankDB::removeItem(3, 3, $townId);
				TownBankDB::addItem(2, 1, $townId);
			}
			break;

			case 'convertMetalToBrick':
			if (TownBankDB::getItemAmount(3, $townId) >= 3){
				TownBankDB::removeItem(3, 3, $townId);
				TownBankDB::addItem(10, 1, $townId);
			}
			break;

			case 'convertBrickToWood':
			if (TownBankDB::getItemAmount(10, $townId) >= 3){
				TownBankDB::removeItem(10, 3, $townId);
				TownBankDB::addItem(2, 1, $townId);
			}
			break;

			case 'convertBrickToMetal':
			if (TownBankDB::getItemAmount(10, $townId) >= 3){
				TownBankDB::removeItem(10, 3, $townId);
				TownBankDB::addItem(3, 1, $townId);
			}
			break;
	
			default:
			//Special Function not found
			break;
		}
	}

?>
</head>
<body>

<div class="Container">

	<?php include("../universal/header.php"); ?>
	
	<div class="centralBox">
		<div class="centralBoxTop">
			<h3 style="text-align:center;">Special Structures</h3>
		</div>
		<div>
		<!-- Here we Check for Structures with Special Functionality -->

        <?php
        $specialStructures = SpecialStructures::specialStructuresStatus($townId);

        if(empty($specialStructures)){
            echo "<h2>There Are Currently No Special Structures Built</h2>";
        }
        else{
            //Will Do the loop for each Special Structure that is atleast built to 1 level
            foreach ($specialStructures as $current){
                echo "<div class='specialStructure'><h4><u>" . $current . "</u></h4>";
				$htmlContent = SpecialStructures::getHtmlContent($current, $townId);
				echo $htmlContent;
                echo "</div><hr style='border-color:black;'>";
            }    
        }
        ?>
		
		</div>
	
	</div>
</div>
	<?php
	Include ("../universal/hyperlinks.php");
	?>
</body>
</html>