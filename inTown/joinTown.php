<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");
require_once ("../model/database.php");

//Check amount of Residents to display later
// xax

	//Check if there was multiple checkboxes checked for multi-join
	$multiJoin = filter_input(INPUT_POST, 'selectedChars', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

	//STORES the ID of the character that was selected separate from checkboxes
	$singleJoin = filter_input(INPUT_GET, 'selectedChar');

	if(!isset($singleJoin)){
		$singleJoin = filter_input(INPUT_POST, 'selectedChar');
	}
	$_SESSION['char_id'] = $singleJoin;

	$temp = filter_input(INPUT_GET, 'tempChar');
	if (!isset($temp))
	{
		$temp = filter_input(INPUT_POST, 'tempChar');
		if(isset($_SESSION["multijoin"])){
			unset($_SESSION["multijoin"]);
			$_SESSION["multijoin"] = [];
		}
	}
	else{
		$tempSession = $_SESSION["multijoin"];
		foreach($tempSession as $current){
			$current = unserialize($current);
			if(!is_array($multiJoin)){
				$multiJoin = [];
			}
			if(!in_array($current["id"], $multiJoin)){
				//Wont reach this line if the character is already in the list. To prevent duplicates
				$multiJoin[] = $current["id"];
			}
		}
	}
	$characterAmount = 1;

	if(isset($singleJoin)){
		if(!is_array($multiJoin)){
			$multiJoin = [];
		}
		if(!in_array($singleJoin, $multiJoin)){
			$multiJoin[] = $singleJoin;
		}
	}

	//multiJoin is an array of character ID's that need to join
	if(!empty($multiJoin)){
		$characterAmount = sizeof($multiJoin);
	}
	else{
		$multiJoin = [$singleJoin];
	}

	

	//The character that was hovered over when Join town was pressed
	$_SESSION['char'] = $temp;

	//SET CHAR ID SESSION HERE

	$cName = $temp;
	$pName = $_SESSION['login'];

	//if the character is already in a town, forward the player to inTown.php
	$query1 = "SELECT * FROM `characters` WHERE `username` = '$pName' AND `id` = '$singleJoin'";
	$query2 = mysqli_query($con, $query1);
	while ($row = mysqli_fetch_assoc($query2))
	{
		if ($row['town_id'] != NULL)
		{
		$townName = Towns::getTownNameById($row['town_id']);
		$townTableName = Towns::getTownTableName($row['town_id']);

			//***SET SESSION for the character's current location ***ALSO add this code to 'addToTown.php' for players that join a new town
			//First we search every zone to figure out where the current character is ($con = settlements DB)
			$querySelect = "SELECT * FROM `" . $townTableName . "`";
			$querySelect2 = mysqli_query($con,$querySelect);
			while ($row = mysqli_fetch_assoc($querySelect2))
			{
				$currentRow = explode('.', $row['charactersHere']);
				for ($i = 0; $i < count($currentRow); $i++)
				{
					//Once we found the location of the 'single join' character, set sessions for x  and y
					if ($currentRow[$i] == $singleJoin)
					{
						$_SESSION['x'] = $row['x'];
						$_SESSION['y'] = $row['y'];
					}
				}
			}
			$_SESSION['char'] = $cName;
			echo '<script>window.location = "' . $root . '/inTown/?locat=inTown";</script>';
			exit();
		}
	}
?>

<html>
<head>
<link rel="stylesheet" type="text/css" href="../mainDesign.css">
<link rel="stylesheet" type="text/css" href="../css/joinTown.css">
<!-- source below grants access to JQuery,through Microsoft network (Can download Jquery file and host it through the website too) -->
<!-- <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.3.min.js"></script> -->
</head>

<body bgcolor="#1A0000">
	<div class="Container">
		<div class="header">
		<img src="../images/DeadFallLogo2.png">
		</div>

		<div class="browseBlock3">
		
		<div class="multiJoin">
			<!-- Display all characters that have not joined a town yet -->
			<?php 
				//NEW METHOD OF DB CONNECTION
				$dbCon = Database::getDB();
				$query = "SELECT * FROM `characters` WHERE `username` = :username";
				$statement = $dbCon->prepare($query);
				$statement->bindValue(':username', $pName);
				$statement->execute();
				$characters = $statement->fetchAll();
				$statement->closeCursor();

				if($characterAmount > 1){
					foreach($multiJoin as $characterId){
						$character = Character::getCharacterById($characterId);
						echo "<li><img src='../images/icons/" . lcfirst($character["class"]) . ".png' title='" . $character["class"] . "'> " . $character["character"] . " {Lv. " . $character["level"] . "}</li>";

						//Add them to the Session List of MultiJoin
						//***WILL NEED TO UNSERIALIZE TO UTILIZE THE OBJECTS WHEN ADDING CHARACTERS ON FUTURE PAGES */
						$_SESSION["multijoin"][] = serialize($character);
					}
				}
				else{
					$character = Character::getCharacterById($singleJoin);
					echo "<li>" . $character["character"] . " {Lv. " . $character["level"] . "}</li>";
					$_SESSION["multijoin"][] = serialize($character);
				}
			?>
		</div>

		<table class='townList'><thead>
		<tr>
			<th>Town Name</th>
			<th>Residents (Current/Max)</th>
			<th>Join Town</th>
		</tr>
		</thead>
		
		<?php

		$query = "SELECT * FROM `towns` WHERE `townFull`=0";
		$statement = $dbCon->prepare($query);
		$statement->execute();
		$results = $statement->fetchAll();
		$statement->closeCursor();

		foreach($results as $key => $result){
			$t = $result["townName"];
			$townId = $result["town_id"];
			$c = $cName;
			//$result["maxResidents"] - $result["amountResidents"] >= $characterAmount
			if($result["maxResidents"] - $result["amountResidents"] >= $characterAmount){
				$availableTown = "<tr><td>" . $t . "</td><td>[" . $result["amountResidents"] . "/" . $result["maxResidents"] . "]  </td><td><form method='post' action='../functions/addToTown.php'><input type='hidden' name='newTown' value=$townId><input type='hidden' name='char' value=$c><button type='submit' value='Submit' class='joinButton'><span>Join Town</span></button></form></td></tr>";
				echo $availableTown;
			}
		}
		echo "</table>";
		?>
		
		<br><hr><br>
			<div class="createTown">
				<form id='create' method='post' action='../functions/createTown.php'>
				<h3>***Note: Do NOT use any spaces in town-name***</h3>
				<label for='townName'><i>Town Name</i></label><br>
					<input id='townName' type='text' name='newTown' required style="margin: 5px;"><br>
					<label for='townSize'><i>Size</i></label><br>
					<select id='townSize' required>
					<option value="" selected disabled>Select Town Size</option>
					<option value="1">(10) Band  of Survivors | 11x11 Map</option>
					</select>

					<input type='hidden' name='cName' value='<?php echo $cName;?>'>
					<input type='hidden' name='char' value=$c>
					<input type='submit' value='Create Town'>
				</form>
			</div>
		</div>

		
</body>
</html>