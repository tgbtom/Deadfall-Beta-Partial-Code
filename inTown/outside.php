<?php
require_once ("../functions/verifyLogin.php");
require_once ("../functions/queryFunctions.php");
require_once ("../connect.php");
Include("../data/buildings.php");
Include("../data/items.php");


$dir = filter_input(INPUT_GET, 'dir');

	
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


//Set Variables which correspond with the character that is in session (town name, level, class, etc.)
$charDetails = getCharDetails();

$townName = $charDetails['townName'];
$charLevel = $charDetails['level'];
$charClass = $charDetails['class'];
$currentAp = $charDetails['currentAP'];
$itemsMass = $charDetails['itemsMass'];
$maxItems = $charDetails['maxItems'];
$capacityLeft = $maxItems - $itemsMass;


/*$query1 = "SELECT * FROM `characters` WHERE `character` = '$charName' AND `username` = '$playerName'";
$query2 = mysqli_query($con, $query1);

	while ($row = mysqli_fetch_assoc($query2))
	{
		$townName = $row['townName'];
		$charLevel = $row['level'];
		$charClass = $row['class'];
		$currentAp = $row['currentAP'];
	}	*/
	
	$tempX = $_SESSION['x'];
	$tempY = $_SESSION['y'];
	
	$danger = getDangerLevel($tempX, $tempY, $townName);
	//zedSpread($townName);
	
	
	$loot = filter_input(INPUT_POST, 'loot');
	if (isset($loot))
	{
		if ($loot == 'Loot')
		{
			if (!($tempX == 0 && $tempY == 0)) //Continue only if character isn't in town
			{
				if (canMove())
				{
					reduceAp();
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
				$findChar = "SELECT * FROM `" . $townName . "` WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
				$findChar2 = mysqli_query($con, $findChar);
				while ($row = mysqli_fetch_assoc($findChar2))
				{
					$listedChar = explode(".", $row['charactersHere']);
					for ($i = 0; $i < count($listedChar); $i++)
					{
						if ($listedChar[$i] != $charName)
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
					$updateOld = "UPDATE `" . $townName . "` SET `charactersHere`='" . $updatedOldZone ."' WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
					mysqli_query($con, $updateOld);
				}
				$_SESSION['y']++;
				
				$newX = $_SESSION['x'];
				$newY = $_SESSION['y'];
				$findLoc = "SELECT * FROM `" . $townName . "` WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				$findLoc2 = mysqli_query($con, $findLoc);
				while ($row = mysqli_fetch_assoc($findLoc2))
				{
					if ($row["charactersHere"] != NULL && $row["charactersHere"] != '')
					{
						$updatedNewZone = $row["charactersHere"] . "." . $charName;
					}
					else
					{
						$updatedNewZone = $charName;
					}
				}
				$updateNew = "UPDATE `" . $townName . "` SET `charactersHere`='" . $updatedNewZone ."' WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
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
				$findChar = "SELECT * FROM `" . $townName . "` WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
				$findChar2 = mysqli_query($con, $findChar);
				while ($row = mysqli_fetch_assoc($findChar2))
				{
					$listedChar = explode(".", $row['charactersHere']);
					for ($i = 0; $i < count($listedChar); $i++)
					{
						if ($listedChar[$i] != $charName)
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
					$updateOld = "UPDATE `" . $townName . "` SET `charactersHere`='" . $updatedOldZone ."' WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
					mysqli_query($con, $updateOld);
				}
				$_SESSION['x']++;
				
				$newX = $_SESSION['x'];
				$newY = $_SESSION['y'];
				$findLoc = "SELECT * FROM `" . $townName . "` WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				$findLoc2 = mysqli_query($con, $findLoc);
				while ($row = mysqli_fetch_assoc($findLoc2))
				{
					if ($row["charactersHere"] != NULL && $row["charactersHere"] != '')
					{
						$updatedNewZone = $row["charactersHere"] . "." . $charName;
					}
					else
					{
						$updatedNewZone = $charName;
					}
				}
				$updateNew = "UPDATE `" . $townName . "` SET `charactersHere`='" . $updatedNewZone ."' WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
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
				$findChar = "SELECT * FROM `" . $townName . "` WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
				$findChar2 = mysqli_query($con, $findChar);
				while ($row = mysqli_fetch_assoc($findChar2))
				{
					$listedChar = explode(".", $row['charactersHere']);
					for ($i = 0; $i < count($listedChar); $i++)
					{
						if ($listedChar[$i] != $charName)
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
					$updateOld = "UPDATE `" . $townName . "` SET `charactersHere`='" . $updatedOldZone ."' WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
					mysqli_query($con, $updateOld);
				}
				$_SESSION['y']--;
				
				$newX = $_SESSION['x'];
				$newY = $_SESSION['y'];
				$findLoc = "SELECT * FROM `" . $townName . "` WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				$findLoc2 = mysqli_query($con, $findLoc);
				while ($row = mysqli_fetch_assoc($findLoc2))
				{
					if ($row["charactersHere"] != NULL && $row["charactersHere"] != '')
					{
						$updatedNewZone = $row["charactersHere"] . "." . $charName;
					}
					else
					{
						$updatedNewZone = $charName;
					}
				}
				$updateNew = "UPDATE `" . $townName . "` SET `charactersHere`='" . $updatedNewZone ."' WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
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
				$findChar = "SELECT * FROM `" . $townName . "` WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
				$findChar2 = mysqli_query($con, $findChar);
				while ($row = mysqli_fetch_assoc($findChar2))
				{
					$listedChar = explode(".", $row['charactersHere']);
					for ($i = 0; $i < count($listedChar); $i++)
					{
						if ($listedChar[$i] != $charName)
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
					$updateOld = "UPDATE `" . $townName . "` SET `charactersHere`='" . $updatedOldZone ."' WHERE `x`='" . $oldX . "' AND `y`='" . $oldY . "'";
					mysqli_query($con, $updateOld);
				}
				$_SESSION['x']--;
				
				$newX = $_SESSION['x'];
				$newY = $_SESSION['y'];
				$findLoc = "SELECT * FROM `" . $townName . "` WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				$findLoc2 = mysqli_query($con, $findLoc);
				while ($row = mysqli_fetch_assoc($findLoc2))
				{
					if ($row["charactersHere"] != NULL && $row["charactersHere"] != '')
					{
						$updatedNewZone = $row["charactersHere"] . "." . $charName;
					}
					else
					{
						$updatedNewZone = $charName;
					}
				}
				$updateNew = "UPDATE `" . $townName . "` SET `charactersHere`='" . $updatedNewZone ."' WHERE `x`='" . $newX . "' AND `y`='" . $newY . "'";
				mysqli_query($con, $updateNew);
				echo "<script>window.location.href='.?locat=outside'</script>";
			}
		}
		else
		{
			echo "<script>window.location.href='.?locat=outside&e=Not enough AP to move.'</script>";
		}
	}
	
	function canMove()
	{
		global $dbCon;
		global $playerName;
		global $charName;
		$query = 'SELECT * FROM `characters` WHERE `username` = :user AND `character` = :char';
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':user', $playerName);
		$statement->bindValue(':char', $charName);
		$statement->execute();
		$result = $statement->fetch();
		$statement->closeCursor();
		
		if ($result['currentAP'] > 0)
		{return true;}
		else
		{return false;}
	}
	
	function reduceAp()
	{
		global $dbCon;
		global $playerName;
		global $charName;
		global $currentAp;
		
		$newAp = $currentAp - 1;
		$query = 'UPDATE `characters` SET `currentAp` = :newAp WHERE `username` = :user AND `character` = :char';
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':newAp', $newAp);
		$statement->bindValue(':user', $playerName);
		$statement->bindValue(':char', $charName);
		$statement->execute();
		$result = $statement->fetch();
		$statement->closeCursor();
	}
	
	?>
	
<html>

<head>

	<link rel="stylesheet" type="text/css" href="mainDesignTown.css">
	<style>
	th{border-bottom:1px solid black; border-top:1px solid black; background-color:#6d5846;}
	#comma{width:5%;}
	#zedCount {width: 50%;text-align: left; padding-left: 5px; padding-top: 2px;}
	#xco {width: 22.5%;text-align: right;}
	#yco {width: 22.5%;text-align: left;}
	.data {border-bottom:1px solid black; background-color:#6d5846;}
	.data2 {border-bottom:1px solid black;}
	#leftArrow {position: relative; top: -68px; left: -5px;}
	#rightArrow {position: relative; top: -68px; left: -25px;}
	#upArrow {position: relative; top: -118px; left: 5px;}
	#downArrow {position: relative; top: -33px; left: -35px;}
	.Arrow:hover {filter:brightness(0.8);}
	.bulletinHead {border: 1px solid black; float: center; width: 65%; height: 15px; padding: 5px; margin-top: 5px; overflow: hidden;}
	.bulletin {border: 1px solid black; float: center; max-height: 200px; width: 65%; padding: 5px; margin: 0px; overflow: auto;}
	.topBulletin { text-align: center; margin: 0px; padding: 0px;}
	
	
	.selected {border: 1px solid black;}
	.notSelected {border: 0px solid red;}
	</style>
	
	<title>Outside Map</title>
	<script type='text/javascript'>
	
	function moveCharacter()
	{
		var xhttp = new XMLHttpRequest();
	}
	
	function displayItem(item, desc)
	{
		document.getElementById("itemName").innerHTML = item;
		document.getElementById("itemName2").value = item;
		document.getElementById("itemDesc").innerHTML = desc;
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
			//Note: Arguments[0 - 2] come before the groundItems
			document.getElementById("items").innerHTML = "";
			for (i = 0; i < arguments.length; i++)
			{
				// > 3 to ensure it skips past coordinates and Zed Count AND depletion amount
				if (i > 3)
				{
					if (arguments[i] != "-1")
					{
						var itemNameNow = itemsInfo[arguments[i]][0];
						var itemDescNow = itemsInfo[arguments[i]][1];
						document.getElementById("items").innerHTML = document.getElementById("items").innerHTML + '<img onclick="displayItem(`' + itemNameNow + '`,`' + itemDescNow + '`); this.className = `selected`" title="' + itemNameNow + '" src="../images/items/' + itemNameNow + '.png">';
					}
				}

			}
		}
		
	</script>
	</head>
	<body>
	

<div class="Container">

	<?php include("../universal/header.php"); ?>
	
	<div class="centralBox">
	
		<?php 
		/*<object width="176" height="176">
		<param name="movie" value="outside/Deadfall World Beyond.swf">
		<embed src="outside/Deadfall World Beyond.swf" width="176" height="176">
		</embed>
		</object>*/
		 
		
		
		//connect to, then query the settlements database to determine # of Z in each zone before drawing it, then colour determined by # of zeds
		
		if(! $con)
		{
			die('Connection Failed'.mysql_error());
			print('Couldnt Connect');
		}
		$query3 = "SELECT *	FROM " . $townName;
		$completeQuery = mysqli_query($con, $query3);
		
		echo '<a href=".?locat=outside&dir=4"><img src="../images/leftArrow.png" class="Arrow" id="leftArrow"></a>'; 
		echo '<a href=".?locat=outside&dir=1"><img src="../images/upArrow.png" class="Arrow" id="upArrow"></a>';	
		echo '<a href=".?locat=outside&dir=3"><img src="../images/downArrow.png" class="Arrow" id="downArrow"></a>';
		echo '<a href=".?locat=outside&dir=2"><img src="../images/rightArrow.png" class="Arrow" id="rightArrow"></a>';
		

		
		echo '<svg width="176" height="176" style="box-shadow: 7px 7px #333333;">';
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
			$chars2 = ' ';
			for($i = 0; $i < count($charactersList); $i++)
			{
				//adds a '...' and # of remaining characters after the list hits specified maximum size
				if($i > 10)
				{
					$chars2 = $chars2 . '</br> ... ' . (count($charactersList) - 11) . ' more.';
					$i = count($charactersList);
				}
				else
				{
					$chars2 = $chars2 . '</br>' . $charactersList[$i];
				}
			}
						
			

			
			/*for ($indx = 0; $indx < count($groundSplit2); $indx++)
			{
				if ($groundSplit2[$indx] != "-1")
				{
				$currentindex = $groundSplit2[$indx];
				$currentItem = $itemsInfo[$currentindex][0];
				$itemNames = $itemNames . ", " . $currentItem;
				echo "<p>" . $itemNames . "</p>";
				}

			}*/
			
			//:0.3:2.5
			
			//***If the current logged-in character is in the current co-ordinates, draw a small white square in the middle of the plot***
			
			
			if ($x >= 176)
			{
				$x = 0;
			}
			$y = floor($row["id"] / 11) * 16;
			
			if ($realX == 0 && $realY == 0)
			{
				$drawHere = '<rect onclick="top.display(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(140,89,32);stroke-width:1;stroke:rgb(0,0,0)" />';
			}
			
			else if ($zeds == 0)
			{
				$drawHere = '<rect onclick="top.display(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(2,148,23);stroke-width:1;stroke:rgb(0,0,0)" />';
			}

			else if ($zeds == 1)
			{
				$drawHere = '<rect onclick="top.display(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(2,122,19);stroke-width:1;stroke:rgb(0,0,0)" />';
			}
			
			else if ($zeds == 2 || $zeds == 3)
			{
				$drawHere = '<rect onclick="top.display(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(2,97,15);stroke-width:1;stroke:rgb(0,0,0)" />';
			}
			
			else if ($zeds >= 4 && $zeds <= 6)
			{
				$drawHere = '<rect onclick="top.display(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(168,159,24);stroke-width:1;stroke:rgb(0,0,0)" />';
			}
			
			else if ($zeds >= 7 && $zeds <= 12)
			{
				$drawHere = '<rect onclick="top.display(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(252,150,23);stroke-width:1;stroke:rgb(0,0,0)" />';
			}
			
			else if ($zeds >= 13 && $zeds <= 25)
			{
				$drawHere = '<rect onclick="top.display(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(252,26,23);stroke-width:1;stroke:rgb(0,0,0)" />';
			}
			
			else if ($zeds >= 26 && $zeds <= 40)
			{
				$drawHere = '<rect onclick="top.display(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(179,18,168);stroke-width:1;stroke:rgb(0,0,0)" />';
			}
			
			else if ($zeds >= 41)
			{
				$drawHere = '<rect onclick="top.display(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(64,64,64);stroke-width:1;stroke:rgb(0,0,0)" />';
			}

	
			
			
			echo $drawHere;
			
			//If current character is in the zone, draw a small white square, otherwise if there are other characters it will draw a blue square
			for($i = 0; $i < count($charactersList); $i++)
			{
				if ($charactersList[0] != NULL && $charactersList[0] != '')
				{
					if(in_array($charName, $charactersList)) 
					{
						$drawHere2 = '<rect onclick="top.display(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="4" height="4" x="' . ($x + 6) . '" y="' . ($y + 6) . '" style="fill:rgb(255,255,255);stroke-width:1;stroke:rgb(155,155,155)"><title>' . $chars2 . '</title></rect>';
						echo $drawHere2;
					}
				
					else
					{
						$drawHere2 = '<rect onclick="top.display(' . $zeds . ',' . $realX . ',' . $realY . ',' . $lootability . ',' . $groundSplit . ')" width="4" height="4" x="' . ($x + 6) . '" y="' . ($y + 6) . '" style="fill:rgb(0,204,255);stroke-width:1;stroke:rgb(0,122,153)"><title>' . $chars2 . '</title></rect>';
						echo $drawHere2;
					}
				}
			}
			
			
			$x = $x + 16;
		}
		
		echo '</svg>';

		/*for ($index = 0; $index <= 121; $index++) 
		{
			$x = $x + 16;
			if ($x >= 176)
			{
				$x = 0;
			}
			$y = floor($index / 11) * 16;
			echo $x;
			$drawHere = '<rect width="16" height="16" x="' . $x . '" y="' . $y . '" style="fill:rgb(0,204,204);stroke-width:1;stroke:rgb(0,0,0)" />';
			echo $drawHere;
		}*/
		
		
		?>
	<?php
	
	echo '<table class="zoneInfo">';
	echo '<tr><th style="padding-left:5px;"><img align="left" src="../images/icons/zombie.png"></th><th style=""><img align="left" src="../images/icons/lootability.png" title="Loots Remaining"></th><th style="text-align:right">x</th><th></th><th style="text-align:left">y</th></tr>';
	echo '<tr><td id="zedCount">0</td><td id="lootability">0</td><td id="xco">0</td><td id="comma">,</td><td id="yco">0</td></tr>';
	echo '<tr><th colspan="5">Items</th></tr>';
	echo '<tr style="height:50px;"><td style="border: 1px solid #000000; max-width:140px;" id="items" colspan="5"><b><i>Use the navigation arrows on the left, to leave the town</i></b></td></tr>';
	echo '<tr><td colspan="5" style="border: 1px solid #000000; max-width:140px;">';
	if (!($tempX == 0 && $tempY == 0)) //active loot button only if character isn't in town
	{
		echo '<form action=".?locat=outside" method="post"><input style="float:center; text-align:center;" type="submit" name="loot" value="Loot"></form>';
	}
	else
	{
		echo '<form action=".?locat=outside" method="post"><input style="float:center; text-align:center;" type="submit" name="loot" value="Loot" disabled></form>';
	}
	echo '</td></tr>';
	echo '</table>';
	
	echo "<br>" . getHordeSize($townName);
	
	echo '<table class="itemInfo">';
	echo '<form action="' . $root . '../functions/pickUpItem.php" method="post">';
	echo '<tr style="height:25px;"><td id="itemName" class="data"></td></tr>';
	echo '<input type="hidden" name="location" value="/deadfall/outside.php">';
	echo '<input type="hidden" value="none" name="itemName2" id="itemName2">';
	echo '<tr><td id="itemDesc" style="padding-left:5px;" class ="data2"></td></tr>';
	echo '<tr style="height:15%;"><td><input type="submit" id="PickUp" disabled value="Select an Item ->" style="float:center; width: 100%;"></form></td></tr>';
	echo '</table>';
	
		$curX = $_SESSION['x'];
		$curY = $_SESSION['y'];
		
		//determine zone details
		$query1 = 'SELECT * from `' . $townName . '` WHERE `x` = :x AND `y` = :y';
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
		
		if ($curX != 0 || $curY != 0)
		{		
		//Updates the map when you first load into it from an external page
		echo '<script>display(' . $zeds . ', ' . $curX . ', ' . $curY . ', ' . $lootability . ', ' . $groundItems . ');</script>';
		}
	?>
	
		<div class='bulletinHead'>
		<h5 class='topBulletin'>Bulletin Board (<?php echo $curX . ',' . $curY;?>)</h5>
		</div>
		<div class='bulletin'>
		<ul>
		<?php $i = 0; foreach ($bulletinArray as $bul) : $i++?>
		<li><?php echo $bul;?></li>
		<?php endforeach;?>
		</ul>
		</div>
		</div>
		
</div>
	<?php	
	Include ("../universal/hyperlinks.php");
	?>

	
</body>
</html>