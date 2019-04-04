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


//Set Variables which correspond with the character that is in session (town name, level, class, etc.)
$query1 = "SELECT * FROM `characters` WHERE `character` = '$charName' AND `username` = '$playerName'";
$query2 = mysqli_query($con, $query1);

	while ($row = mysqli_fetch_assoc($query2))
	{
		$townName = $row['townName'];
		$charLevel = $row['level'];
		$charClass = $row['class'];
	}	

	$specialFunction = filter_input(INPUT_POST, "specialFunction");
	if (isset($specialFunction)){
		switch($specialFunction){

			case 'convertWoodToMetal':
			if (TownBankDB::getItemAmount(2, $townName) >= 3){
				TownBankDB::removeItem(2, 3, $townName);
				TownBankDB::addItem(3, 1, $townName);
			}
			break;

			case 'convertWoodToBrick':
			if (TownBankDB::getItemAmount(2, $townName) >= 3){
				TownBankDB::removeItem(2, 3, $townName);
				TownBankDB::addItem(10, 1, $townName);
			}
			break;

			case 'convertMetalToWood':
			if (TownBankDB::getItemAmount(3, $townName) >= 3){
				TownBankDB::removeItem(3, 3, $townName);
				TownBankDB::addItem(2, 1, $townName);
			}
			break;

			case 'convertMetalToBrick':
			if (TownBankDB::getItemAmount(3, $townName) >= 3){
				TownBankDB::removeItem(3, 3, $townName);
				TownBankDB::addItem(10, 1, $townName);
			}
			break;

			case 'convertBrickToWood':
			if (TownBankDB::getItemAmount(10, $townName) >= 3){
				TownBankDB::removeItem(10, 3, $townName);
				TownBankDB::addItem(2, 1, $townName);
			}
			break;

			case 'convertBrickToMetal':
			if (TownBankDB::getItemAmount(10, $townName) >= 3){
				TownBankDB::removeItem(10, 3, $townName);
				TownBankDB::addItem(3, 1, $townName);
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
        $specialStructures = SpecialStructures::specialStructuresStatus($townName);

        if(empty($specialStructures)){
            echo "<h2>There Are Currently No Special Structures Built</h2>";
        }
        else{
            //Will Do the loop for each Special Structure that is atleast built to 1 level
            foreach ($specialStructures as $current){
                echo "<div class='specialStructure'><h4><u>" . $current . "</u></h4>";
				$htmlContent = SpecialStructures::getHtmlContent($current, $townName);
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