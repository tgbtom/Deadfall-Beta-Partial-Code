<?php
require_once ("../functions/verifyLogin.php");
require_once ("../functions/queryFunctions.php");
require_once ("../connect.php");
require_once ("../model/zone.php");
require_once ("../model/database.php");
Include("../data/buildings.php");
Include("../data/items.php");


$dir = filter_input(INPUT_GET, 'dir');
$loc = $_SERVER['REQUEST_URI'];

	
/*////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
WAREHOUSE ITEMS ARE STORED ON MAP AT CO_ORDS 0,0.
/////////////////////////////////////////////
///////////////////////////////////////////////
//////////////////////////////////////////////
////////////////////////////////////////////////*/
	
	
//All information here is retrieved from database simply using the login session and character session

$playerName = $_SESSION['login'];
$charName = $_SESSION['char'];
$charId = $_SESSION['char_id'];


//Set Variables which correspond with the character that is in session (town name, level, class, etc.)
$charDetails = getCharDetails();

$townId = $charDetails['town_id'];
$townName = Towns::getTownNameById($townId);
$townTableName = Towns::getTownTableName($townId);
$charLevel = $charDetails['level'];
$charClass = $charDetails['class'];
$currentAp = $charDetails['currentAP'];
$itemsMass = $charDetails['itemsMass'];
$maxItems = $charDetails['maxItems'];
$capacityLeft = $maxItems - $itemsMass;
	
	$tempX = $_SESSION['x'];
	$tempY = $_SESSION['y'];
	
	$loot = filter_input(INPUT_POST, 'loot');
	if (isset($loot))
	{
		if ($loot == 'Loot')
		{
			if (!($tempX == 0 && $tempY == 0)) //Continue only if character isn't in town
			{
				if (canMove(2, true))
				{
					reduceAp(2);
					$itemToLoot = lootItem();
					for ($i = 0; $i < sizeOf($itemsMaster); $i++)
					{
						if ($itemsMaster[$i][0] == $itemToLoot)
						{
							$itemWeightToLoot = $itemsMaster[$i][2];
							$itemIdToLoot = $i;
						}
					}
		
					if ($itemWeightToLoot <= $capacityLeft)
					{
						//add to inventory
						pickUpItem($itemIdToLoot, $itemWeightToLoot);
					}
					else
					{
						//add to the floor
						dropItem($itemIdToLoot);
					}
				}
				else
				{
					//not enough AP to loot
					echo "<script>window.location.href='.?locat=outside&e=Not Enough AP to loot.'</script>";
				}
			}
			else
			{
				//Cannot loot while in Town
				echo "<script>window.location.href='.?locat=outside&e=Must leave the town to loot.'</script>";
			}
		}
	}
	

	//When the page is loaded if a GET request was sent for moving the charabacter then move the character and change the sessions
	if (isset($_GET['dir']))
	{
	$oldX = $_SESSION['x'];
	$oldY = $_SESSION['y'];
	$updatedOldZone = '';
	$canMove = canMove();
			
		if($dir == 1 && $canMove) //UP
		{
			reduceAp();
			if($_SESSION['y'] < 5)
			{
				// Code Below removes the character from the zone it is leaving, then adds them to the new zone respectively
				$findChar = "SELECT * FROM `" . $townTableName . "` WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
				$findChar2 = mysqli_query($con, $findChar);
				while ($row = mysqli_fetch_assoc($findChar2))
				{
					$listedChar = explode(".", $row['charactersHere']);
					for ($i = 0; $i < count($listedChar); $i++)
					{
						if ($listedChar[$i] != $charId)
						{
							if ($updatedOldZone != NULL && $updatedOldZone != '')
							{
								$updatedOldZone = $updatedOldZone . '.' . $listedChar[$i];
							}
							else
							{
								$updatedOldZone = $listedChar[$i];
							}
						}
					}
					$updateOld = "UPDATE `" . $townTableName . "` SET `charactersHere`='" . $updatedOldZone ."' WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
					mysqli_query($con, $updateOld);
				}
				$_SESSION['y']++;
				
				$newX = $_SESSION['x'];
				$newY = $_SESSION['y'];
				$findLoc = "SELECT * FROM `" . $townTableName . "` WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				$findLoc2 = mysqli_query($con, $findLoc);
				while ($row = mysqli_fetch_assoc($findLoc2))
				{
					if ($row["charactersHere"] != NULL && $row["charactersHere"] != '')
					{
						$updatedNewZone = $row["charactersHere"] . "." . $charId;
					}
					else
					{
						$updatedNewZone = $charId;
					}
				}
				$updateNew = "UPDATE `" . $townTableName . "` SET `charactersHere`='" . $updatedNewZone ."' WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				mysqli_query($con, $updateNew);
				echo "<script>window.location.href='.?locat=outside'</script>";
			}
		}
		elseif($dir == 2 && $canMove) //RIGHT
		{
			reduceAp();
			if($_SESSION['x'] < 5)
			{
				// Code Below removes the character from the zone it is leaving, then adds them to the new zone respectively
				$findChar = "SELECT * FROM `" . $townTableName . "` WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
				$findChar2 = mysqli_query($con, $findChar);
				while ($row = mysqli_fetch_assoc($findChar2))
				{
					$listedChar = explode(".", $row['charactersHere']);
					for ($i = 0; $i < count($listedChar); $i++)
					{
						if ($listedChar[$i] != $charId)
						{
							if ($updatedOldZone != NULL && $updatedOldZone != '')
							{
								$updatedOldZone = $updatedOldZone . '.' . $listedChar[$i];
							}
							else
							{
								$updatedOldZone = $listedChar[$i];
							}
						}
					}
					$updateOld = "UPDATE `" . $townTableName . "` SET `charactersHere`='" . $updatedOldZone ."' WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
					mysqli_query($con, $updateOld);
				}
				$_SESSION['x']++;
				
				$newX = $_SESSION['x'];
				$newY = $_SESSION['y'];
				$findLoc = "SELECT * FROM `" . $townTableName . "` WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				$findLoc2 = mysqli_query($con, $findLoc);
				while ($row = mysqli_fetch_assoc($findLoc2))
				{
					if ($row["charactersHere"] != NULL && $row["charactersHere"] != '')
					{
						$updatedNewZone = $row["charactersHere"] . "." . $charId;
					}
					else
					{
						$updatedNewZone = $charId;
					}
				}
				$updateNew = "UPDATE `" . $townTableName . "` SET `charactersHere`='" . $updatedNewZone ."' WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				mysqli_query($con, $updateNew);
				echo "<script>window.location.href='.?locat=outside'</script>";
			}
		}
		
		elseif($dir == 3 && $canMove) //DOWN
		{
			reduceAp();
			if($_SESSION['y'] > -5)
			{
				// Code Below removes the character from the zone it is leaving, then adds them to the new zone respectively
				$findChar = "SELECT * FROM `" . $townTableName . "` WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
				$findChar2 = mysqli_query($con, $findChar);
				while ($row = mysqli_fetch_assoc($findChar2))
				{
					$listedChar = explode(".", $row['charactersHere']);
					for ($i = 0; $i < count($listedChar); $i++)
					{
						if ($listedChar[$i] != $charId)
						{
							if ($updatedOldZone != NULL && $updatedOldZone != '')
							{
								$updatedOldZone = $updatedOldZone . '.' . $listedChar[$i];
							}
							else
							{
								$updatedOldZone = $listedChar[$i];
							}
						}
					}
					$updateOld = "UPDATE `" . $townTableName . "` SET `charactersHere`='" . $updatedOldZone ."' WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
					mysqli_query($con, $updateOld);
				}
				$_SESSION['y']--;
				
				$newX = $_SESSION['x'];
				$newY = $_SESSION['y'];
				$findLoc = "SELECT * FROM `" . $townTableName . "` WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				$findLoc2 = mysqli_query($con, $findLoc);
				while ($row = mysqli_fetch_assoc($findLoc2))
				{
					if ($row["charactersHere"] != NULL && $row["charactersHere"] != '')
					{
						$updatedNewZone = $row["charactersHere"] . "." . $charId;
					}
					else
					{
						$updatedNewZone = $charId;
					}
				}
				$updateNew = "UPDATE `" . $townTableName . "` SET `charactersHere`='" . $updatedNewZone ."' WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				mysqli_query($con, $updateNew);
				echo "<script>window.location.href='.?locat=outside'</script>";
			}
		}
		
		elseif($dir == 4 && $canMove) //LEFT
		{
			reduceAp();
			if($_SESSION['x'] > -5)
			{
				// Code Below removes the character from the zone it is leaving, then adds them to the new zone respectively
				$findChar = "SELECT * FROM `" . $townTableName . "` WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
				$findChar2 = mysqli_query($con, $findChar);
				while ($row = mysqli_fetch_assoc($findChar2))
				{
					$listedChar = explode(".", $row['charactersHere']);
					for ($i = 0; $i < count($listedChar); $i++)
					{
						if ($listedChar[$i] != $charId)
						{
							if ($updatedOldZone != NULL && $updatedOldZone != '')
							{
								$updatedOldZone = $updatedOldZone . '.' . $listedChar[$i];
							}
							else
							{
								$updatedOldZone = $listedChar[$i];
							}
						}
					}
					$updateOld = "UPDATE `" . $townTableName . "` SET `charactersHere`='" . $updatedOldZone ."' WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
					mysqli_query($con, $updateOld);
				}
				$_SESSION['x']--;
				
				$newX = $_SESSION['x'];
				$newY = $_SESSION['y'];
				$findLoc = "SELECT * FROM `" . $townTableName . "` WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				$findLoc2 = mysqli_query($con, $findLoc);
				while ($row = mysqli_fetch_assoc($findLoc2))
				{
					if ($row["charactersHere"] != NULL && $row["charactersHere"] != '')
					{
						$updatedNewZone = $row["charactersHere"] . "." . $charId;
					}
					else
					{
						$updatedNewZone = $charId;
					}
				}
				$updateNew = "UPDATE `" . $townTableName . "` SET `charactersHere`='" . $updatedNewZone ."' WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				mysqli_query($con, $updateNew);
				echo "<script>window.location.href='.?locat=outside'</script>";
			}
		}
		else
		{
			echo "<script>window.location.href='.?locat=outside&e=Not enough AP to move.'</script>";
		}

		if ($dir >= 0 && $dir <= 4 && $canMove){
			//Character must have been moved, now reduce the danger level in the new zone
			$controlValue = 4; //This value can be modified by weapons being carried, character class, etc.
			$affectedZone = new Zone($townId, $newX, $newY);
			$affectedZone->addControlPoints($controlValue);
		}

	}
	
	/**
	 * Check if the character has enough ap to move
	 * @param int apRequired this parameter specifies how much ap is required
	 * @param bool callForLoot set this to true if you are calling to see if the character can loot rather than move
	 */
	function canMove($apRequired = 1, $callForLoot = false)
	{
		global $dbCon;
		global $playerName;
		global $charId;
		global $tempX, $tempY;
		$query = 'SELECT * FROM `characters` WHERE `username` = :user AND `id` = :id';
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':user', $playerName);
		$statement->bindValue(':id', $charId);
		$statement->execute();
		$result = $statement->fetch();
		$statement->closeCursor();
		
		$zoneObject = new Zone($result["town_id"], $tempX, $tempY);
		$dangerValue = $zoneObject->dangerValue;
		//Need to compare controlpoints against zeds in zone
		$controlPoints = $zoneObject->controlPoints;
		$zedsHere = $zoneObject->zeds;
		//0 or negative number means zone is safe to travel through
		$controlValue = $zedsHere - $controlPoints;

		if ($result['currentAP'] >= $apRequired && $controlValue <= 0){
			return true;
		}
		elseif ($result['currentAP'] >= $apRequired && $callForLoot == true){
			return true;
		}
		else{
			return false;
		}
	}
	
	function reduceAp($amountToReduce  =  1)
	{
		global $dbCon;
		global $playerName;
		global $charId;
		global $currentAp;
		
		$newAp = $currentAp - $amountToReduce;
		$query = 'UPDATE `characters` SET `currentAp` = :newAp WHERE `username` = :user AND `id` = :id';
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':newAp', $newAp);
		$statement->bindValue(':user', $playerName);
		$statement->bindValue(':id', $charId);
		$statement->execute();
		$statement->closeCursor();
	}
	
	?>
	
<html>

<head>

	<link rel="stylesheet" type="text/css" href="mainDesignTown.css">
	<link rel="stylesheet" type="text/css" href="../css/outside.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>

	</style>
	
	<title>Outside Map</title>
	<!-- <script type="text/javascript" src="../js/jquery-3.4.1.min.js"></script> -->
	<script type='text/javascript'>
		var itemsList = [];
		<?php 
			foreach ($itemsMaster as $current){
				echo "itemsList.push(['" . $current[0] . "', '" . $current[1]  . "', " . $current[2]  . ", " . $current[3]  . "]);";
			}
		?>

	function moveCharacter()
	{
		var xhttp = new XMLHttpRequest();
	}
	
	function displayItem(item, desc, mass)
	{
		document.getElementById("itemName").innerHTML = item;
		document.getElementById("itemName2").value = item;
		document.getElementById("itemDesc").innerHTML = desc;
		document.getElementById("itemDescMass").innerHTML = "Mass: " + mass;
		document.getElementById("PickUp").value = "Pick Up " + item;
		document.getElementById("PickUp").disabled = false;
		//unselect previous items to change which item has the selected border
		var sel = document.getElementsByClassName("selected");
		for (i = 0; i < sel.length; i++) 
		{
		sel[i].className = "notSelected";
		}
	}

	function display(Z, Co, Co2, Dep)
		{
			document.getElementById("zedCount").innerHTML = Z;
			document.getElementById("xco").innerHTML = Co;
			document.getElementById("yco").innerHTML = Co2;
			document.getElementById("lootability").innerHTML = Dep;

			var root = "<?php echo $root ?>";
			var loc = "<?php echo $loc ?>";
                        
			//Note: Arguments[0 - 2] come before the groundItems
			document.getElementById("itemsDiv").innerHTML = "";
                        if ((Co == "0" && Co2 == "0")){
                            document.getElementById("itemsDiv").innerHTML = "<b><i>Use the navigation arrows on the left, to leave the town.</i></b>";
                        }
                        else if (arguments.length <= 4){
                            document.getElementById("itemsDiv").innerHTML = "<b><i>Nothing on the ground.</i></b>";
                        }
                        else{
                            for (i = 0; i < arguments.length; i++)
                            {
								// > 3 to ensure it skips past coordinates and Zed Count AND depletion amount
								if (i > 3)
								{
                    				if (arguments[i] != "-1"){
										//arguments[i] is current item id
										var xmlhttp = new XMLHttpRequest();
										xmlhttp.onreadystatechange = function(){
											if (this.readyState == 4 && this.status == 200){
												var itemJson = JSON.parse(this.responseText);

												var loopIndex = itemJson["i"];
												var itemId = itemJson["id"];
												var itemNameNow = itemJson["name"];
												var itemDescNow = itemJson["desc"];
												var itemMassNow = itemJson["mass"];

												document.getElementById("itemsDiv").innerHTML = document.getElementById("itemsDiv").innerHTML +
												'<form action="../functions/pickUpItem.php" method="post" style="display: inline-block;">' + 
												'<input type="hidden" name="location" value="/deadfall/outside.php"><input type="hidden" value="' + itemNameNow + '" name="itemName2" id="itemName2">' +
												'<div class="popup" onclick="popUpMenuA(`popUpA' + loopIndex + '`)"><img alt="test" src="../images/items/' + itemNameNow + '.png" class="item"><img src="../images/rarity/' + getRarityString(itemId) + '.png" title="' + itemNameNow + '" class="rarityBanner">' + 
												'<span class="popuptexta" style="visibility:hidden;" id="popUpA' + loopIndex + '">' +
												'<p><u>' + itemNameNow + '</u></p><p class="rarity">' + getRarityString(itemId) + '</p><p class="weight">Weight: ' + getItemMass(itemId) + '</p>' +
												'<input type="submit" class="act_button" value="Pick Up">' +
												'</span></div></form>'; 
												colourizeRarity();
											}
										};
										xmlhttp.open("POST", "townfunctions/displayItem.php", true);

										//Allow data to be POSTed
										xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

										xmlhttp.send("x=" + arguments[i] + "&" + "y=" + i);
										
                    				}
								}
                            }
						}
						document.getElementById("remoteItemsDiv").innerHTML = "<b><i>Click on a Zone to see the last known information</i></b>";                     
                        if (Dep <= 0){
                            document.getElementById("lootWarning").innerHTML = "This Zone is Depleted";
                        }
		}
		
		function remoteDisplay(Z, Co, Co2, Dep)
		{
			document.getElementById("remoteZedCount").innerHTML = Z;
			document.getElementById("remoteXco").innerHTML = Co;
			document.getElementById("remoteYco").innerHTML = Co2;
			document.getElementById("remoteLootability").innerHTML = Dep;
                        
			//Note: Arguments[0 - 2] come before the groundItems
			document.getElementById("remoteItemsDiv").innerHTML = "";
				if ((Co == "0" && Co2 == "0")){
					document.getElementById("remoteItemsDiv").innerHTML = "<b><i>Click on a Zone to see the last known information</i></b>";
				}
				else if (arguments.length <= 4){
					document.getElementById("remoteItemsDiv").innerHTML = "<b><i>Nothing seen at The zone.</i></b>";
				}
				else{
					for (i = 0; i < arguments.length; i++)
					{
						// > 3 to ensure it skips past coordinates and Zed Count AND depletion amount
						if (i > 3)
						{
							if (arguments[i] != "-1")
							{
								var xmlhttp = new XMLHttpRequest();
										xmlhttp.onreadystatechange = function(){
											if(this.readyState == 4 && this.status == 200){
												var itemJson = JSON.parse(this.responseText);

												var itemNameNow = itemJson["name"];
												var itemDescNow = itemJson["desc"];
												var itemMassNow = itemJson["mass"];
												document.getElementById("remoteItemsDiv").innerHTML = document.getElementById("remoteItemsDiv").innerHTML + '<img title="' + itemNameNow + '" src="../images/items/' + itemNameNow + '.png">';

											}
										};
										xmlhttp.open("POST", "../intown/townfunctions/displayItem.php", true);

										//Allow data to be POSTed
										xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

										xmlhttp.send("x=" + arguments[i]);
							}
						}
					}
			}
		}

function popUpMenuA(x)
{   
	var popup = document.getElementById(x);
	if (popup.style.visibility === 'visible'){
            var wasUp = true;
	}
	else{
            var wasUp = false;
	}
        
	var popuptext = document.getElementsByClassName('popuptexta');
	for (var i = 0; i < popuptext.length; i++){
		popuptext[i].style.visibility = 'hidden';
	}
        
        if (!wasUp){
		popup.style.visibility = 'visible';
	}

}

	function getRarityString(itemId){

		console.log(itemId);
		// console.log(itemsList[itemId]);
		var returnVal = itemsList[itemId][3];
		switch (returnVal){

		case 0:
		return 'Common';
		break;
		
		case 1:
		return 'Uncommon';
		break;
		
		case 2:
		return 'Rare';
		break;
		
		case 3:
		return 'Ultra-Rare';
		break;
		
		case 4:
		return 'Legendary';
		break;

		case 5:
		return 'Scrap';
		break;
		
		default: return 'Common';
		}
	}

	function getItemMass(itemId){
		return itemsList[itemId][2];
	}

	</script>
	</head>
	<body>
	

<div class="Container">

	<?php include("../universal/header.php"); ?>
	
	<div class="testBox">
		<div class="test test1">

		<table>
			<tr>
				<td></td>
				<td>
					<?php 			$tempZone = new Zone($townId, $tempX, $tempY);
					$zoneControl = $tempZone->zeds - $tempZone->controlPoints;
					echo "<p>Control Value:" . (($zoneControl >= 0) ? $zoneControl : "0") . "</p><br>"; 
					?><a href=".?locat=outside&dir=1"><img src="../images/upArrow.png" class="Arrow" id="upArrow"></a>
				</td>
				<td></td>
			</tr>
			<tr>
				<td><a href=".?locat=outside&dir=4"><img style="float:right;" src="../images/leftArrow.png" class="Arrow" id="leftArrow"></a></td><td colspan="1" style="padding-bottom: 10px; width: 196px;">
					
				<!-- Center of table, insert map here -->

				<?php 
			//connect to, then query the settlements database to determine # of Z in each zone before drawing it, then colour determined by # of zeds

			if(! $con)
			{
				die('Connection Failed'.mysql_error());
				print('Couldnt Connect');
			}

			$query3 = "SELECT *	FROM " . $townTableName;
			$completeQuery = mysqli_query($con, $query3);
			

			
			echo '<div class="mapCanvas">';
			
			echo '<svg width="176" height="176" class="map">';
			$x = 0;
		
			while ($row = mysqli_fetch_assoc($completeQuery))
			{
				echo $row["id"];
				$zeds = $row['zeds'];
				//$coords = $row['coords'];
				//$xy = explode(",", $coords);
				$realX = $row['x'];
				$realY = $row['y'];
				$groundSplit = $row['groundItems'];
				$groundSplit2 = explode(",", $groundSplit);
				$itemNames = "";
				$lootability = $row['lootability'];
				$chars = $row['charactersHere'];
				$charactersList = explode(".", $chars);
				?>
				
				<?php
				//Create a temporary string variable to display characters that are in the zone through the SVG title element
				$chars2 = '';
				for($i = 0; $i < count($charactersList); $i++)
				{
					//adds a '...' and # of remaining characters after the list hits specified maximum size
					if($i > 10)
					{
						$chars2 = $chars2 . '&#13; ... ' . (count($charactersList) - 11) . ' more.';
						$i = count($charactersList);
					}
					else
					{
						$rowToAdd = Character::getCharacterById($charactersList[$i]);
						$chars2 = $chars2 . '&#13;&nbsp;' . $rowToAdd["character"];
					}
				}
				
				if ($x >= 176)
				{
					$x = 0;
				}
				$y = floor($row["id"] / 11) * 16;
				
				if ($realX == 0 && $realY == 0)
				{
					$drawHere = '<rect onclick="top.remoteDisplay(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(140,89,32);stroke-width:1;stroke:rgb(0,0,0)" />';
				}
				
				else if ($zeds == 0)
				{
					$drawHere = '<rect onclick="top.remoteDisplay(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(2,148,23);stroke-width:1;stroke:rgb(0,0,0)" />';
				}

				else if ($zeds == 1)
				{
					$drawHere = '<rect onclick="top.remoteDisplay(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(2,122,19);stroke-width:1;stroke:rgb(0,0,0)" />';
				}
				
				else if ($zeds == 2 || $zeds == 3)
				{
					$drawHere = '<rect onclick="top.remoteDisplay(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(2,97,15);stroke-width:1;stroke:rgb(0,0,0)" />';
				}
				
				else if ($zeds >= 4 && $zeds <= 6)
				{
					$drawHere = '<rect onclick="top.remoteDisplay(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(168,159,24);stroke-width:1;stroke:rgb(0,0,0)" />';
				}
				
				else if ($zeds >= 7 && $zeds <= 12)
				{
					$drawHere = '<rect onclick="top.remoteDisplay(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(252,150,23);stroke-width:1;stroke:rgb(0,0,0)" />';
				}
				
				else if ($zeds >= 13 && $zeds <= 25)
				{
					$drawHere = '<rect onclick="top.remoteDisplay(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(252,26,23);stroke-width:1;stroke:rgb(0,0,0)" />';
				}
				
				else if ($zeds >= 26 && $zeds <= 40)
				{
					$drawHere = '<rect onclick="top.remoteDisplay(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(179,18,168);stroke-width:1;stroke:rgb(0,0,0)" />';
				}
				
				else if ($zeds >= 41)
				{
					$drawHere = '<rect onclick="top.remoteDisplay(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(64,64,64);stroke-width:1;stroke:rgb(0,0,0)" />';
				}
				
				echo $drawHere;
				
				//If current character is in the zone, draw a small white square, otherwise if there are other characters it will draw a blue square
				for($i = 0; $i < count($charactersList); $i++)
				{
					if ($charactersList[0] != NULL && $charactersList[0] != '')
					{
						if(in_array($charId, $charactersList)) 
						{
							$drawHere2 = '<circle onclick="top.display(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" r="3" cx="' . ($x + 8) . '" cy="' . ($y + 8) . '" style="fill:rgb(255,255,255);stroke-width:1;stroke:rgb(155,155,155)"><title>' . $chars2 . '</title></circle>';
							echo $drawHere2;
						}
					
						else
						{
							$drawHere2 = '<circle onclick="top.remoteDisplay(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" r="2.5" cx="' . ($x + 8) . '" cy="' . ($y + 8) . '" style="fill:rgb(0,204,255);stroke-width:1;stroke:rgb(0,122,153)"><title>' . $chars2 . '</title></circle>';
							echo $drawHere2;
						}
					}
				}
				
				
				$x = $x + 16;
			}
			
			echo '</svg></div>';
			//End of box 1, with map
			?>

			
				</td><td><a href=".?locat=outside&dir=2"><img style="float:left;" src="../images/rightArrow.png" class="Arrow" id="rightArrow"></a></td>
			</tr>
			<tr>
				<td></td><td colspan="1"><a href=".?locat=outside&dir=3"><img src="../images/downArrow.png" class="Arrow" id="downArrow"></a></td><td></td>
			</tr>
		</table>
		
		</div>
		<div class="test test2">
			<table class="zoneInfo">
				<tr><th style="padding-left:5px;"><img align="left" src="../images/icons/zombie.png"></th><th style=""><img align="left" src="../images/icons/lootability.png" title="Loots Remaining"></th><th style="text-align:right">x</th><th></th><th style="text-align:left">y</th></tr>
				<tr class="lightRow"><td id="zedCount">0</td><td id="lootability">0</td><td id="xco">0</td><td id="comma">,</td><td id="yco">0</td></tr>
				<tr><th colspan="5">Items</th></tr>
				<tr class="lightRow"><td id="items" colspan="5"><div id="itemsDiv">test</div></td></tr>
				<tr><td colspan="5">
				<?php 
                if (!($tempX == 0 && $tempY == 0)) //active loot button only if character isn't in town
                {
                    echo '<p id="lootWarning" style="text-align: center;"></p><form action=".?locat=outside" method="post" id="lootForm"><button class="lootButton" id="lootButton" type="submit" name="loot" value="Loot"><span>Loot | 2 AP</span></button></form>';
                }
                else
                {
                    echo '<p id="lootWarning"></p><form action=".?locat=outside" method="post" id="lootForm"><button class="lootButton" id="lootButton" type="submit" name="loot" value="Loot" disabled><span>Cannot Loot</span></button></form>';
                }
                ?>
				</td></tr>
			</table>
		</div>
		<div class="test test3">
			<div class="fillerBox">
                <tr><td colspan="5" class="lootCell">
                        
                 <!-- </td></tr><hr style="border-color: black;"><hr style="border-color: black;"> -->
				 <table class="remoteInfo">
				 	<tr><th colspan="5">Remote Zone Scouting</th></tr>
					<tr><th style="padding-left:5px;"><img src="../images/icons/zombie.png"></th><th style=""><img src="../images/icons/lootability.png" title="Loots Remaining"></th><th style="text-align:right">x</th><th></th><th style="text-align:left">y</th></tr>
					<tr class="lightRow"><td id="remoteZedCount">?</td><td id="remoteLootability">?</td><td id="remoteXco">?</td><td id="comma">,</td><td id="remoteYco">?</td></tr>
					<tr><th colspan="5">Items</th></tr>
					<tr class="lightRow"><td id="remoteItems" colspan="5"><div id="remoteItemsDiv"></div></td></tr>
				</table>
			</div>
		</div>
		<div class="test test4">
		<?php
	
	$curX = $_SESSION['x'];
	$curY = $_SESSION['y'];
	
	//determine zone details
	$query1 = 'SELECT * from `' . $townTableName . '` WHERE `x` = :x AND `y` = :y';
	$statement1 = $dbCon->prepare($query1);
	$statement1->bindValue(':x', $curX);
	$statement1->bindValue(':y', $curY);
	$statement1->execute();
	$result1 = $statement1->fetch();
	$statement1->closeCursor();
	
	$zeds = $result1['zeds'];
	$groundItems = $result1['groundItems'];
	$bulletin = $result1['bulletin'];
	$lootability = $result1['lootability'];
	$bulletinArray = explode('.', $bulletin);
	krsort($bulletinArray);
			
	//Updates the map when you first load into it from an external page
	echo '<script>display(' . $zeds . ', ' . $curX . ', ' . $curY . ', ' . $lootability . ', ' . $groundItems . ');</script>';
	echo '<script>colourizeRarity();</script>';
	
?>
			<div class="bulletinDiv">
	<table class="bulletin">
				<thead>
				<tr class='bulletinHead'>
					<th><h5 class='topBulletin'>Bulletin Board (<?php echo $curX . ',' . $curY;?>)</h5></th>
				</tr>
				</thead>
				<tbody>
						<?php $i = 0; foreach ($bulletinArray as $bul) : $i++?>
				<tr><td><?php echo $bul;?></td></tr>
						<?php endforeach;?>
				</tbody>
			</table>
			</div>
	</div>
</div>
<?php	
Include ("../universal/hyperlinks.php");
?>
		</div>
	</div></div>	
</body>
</html>