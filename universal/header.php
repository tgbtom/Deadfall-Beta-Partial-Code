<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<script type="text/javascript" src="../js/header.js"></script>

<link rel="stylesheet" href="../css/header.css" type="text/css">
</head>
<body style="text-align:center">


<script>

// When the user clicks on div, open the popup and close all other pop-ups

function popUpMenu(x)
{   
	var popup = document.getElementById(x);
	if (popup.style.visibility === 'visible'){
            var wasUp = true;
	}
	else{
            var wasUp = false;
	}
        
	var popuptext = document.getElementsByClassName('popuptext');
	for (var i = 0; i < popuptext.length; i++){
		popuptext[i].style.visibility = 'hidden';
	}
        
        if (!wasUp){
		popup.style.visibility = 'visible';
	}

}

function newAction(target, hiddenNameId)
{
	if (target === 'drop')
	{	
		document.sendItemData.action = "../functions/dropItem.php?nameTagId=" + hiddenNameId;
	}
	else if (target === 'Eat' || target === 'Drink' || target === 'Load')
	{
		document.sendItemData.action = "../functions/consume.php?nameTagId=" + hiddenNameId;
	}
	else if (target === 'Attack')
	{
		document.sendItemData.action = "../functions/consume.php?nameTagId=" + hiddenNameId;
	}
	else
	{
		document.sendItemData.action = "../inTown/?locat=warehouse";
	}
}

function changeChar(newCharId) {
		if (newCharId.length === 0) 
		{
			return;
		} 
		else
		{
			var xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState === 4 && xmlhttp.status === 200) 
					{
						//document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
					}
				};
			xmlhttp.open("GET", "../functions/changeChar.php?change="+newCharId, true);
			xmlhttp.send();
			window.location.reload();
		}	
	}

</script>

</body>
</html>

<?php 
require_once ("../data/items.php");
require_once ("../data/status.php");

require_once ("../functions/queryFunctions.php");
require_once ("../model/structures.php");
require_once ("../model/database.php");
require_once ("../model/endDay.php");

//See if we are loading from an  end day
$endDay = filter_input(INPUT_POST, 'endDay');
if (isset($endDay)) {
	if ($endDay == 'end') {
		//end the day
		if (doesStatusContain(10)) {
			//Char has already ended the day --> does nothing
		} else {
			replaceStatus(11, 10);
			endDay();

			//Reload the header to properly clear post data so data will not be resubmitted when character is changed
			echo '<meta http-equiv="refresh" content="0">';
		}
	}
}


//gets the user and current character, and stores them in local variables
$user = $_SESSION['login'];
$char = $_SESSION['char'];
$charId = $_SESSION['char_id'];

//Query loads the row in the characters DB that corresponds to the currently logged character
$charDetails = getCharDetails();

$weightCapacity = $charDetails['maxItems'];
$currentMass = $charDetails['itemsMass'];
$itemsHeld = $charDetails['items'];
$itemsHeldArray = explode(',', $itemsHeld);
$status = $charDetails['status'];
$statusArray = explode('.', $status);

$currentAp = $charDetails['currentAP'];
$maxAp = $charDetails['maxAP'];
$townId = $charDetails['town_id'];
$townName = Towns::getTownNameById($townId);
$convertedTownName = str_replace("_", " ", $townName);

$charObject = new Character($charDetails['id']);
$previousChar = Character::getSequentialCharacter($charObject, "prev");
$nextChar = Character::getSequentialCharacter($charObject, "next");
echo "<script>console.log('hmmada" . $nextChar->character . "')</script>";

$loc = $_SERVER['REQUEST_URI'];

//query loads the row in the towns DB that corresponds to the current town 
$query2 = 'SELECT * FROM `towns` WHERE `town_id` = :townId';
$statement2 = $dbCon->prepare($query2);
$statement2->bindValue(':townId', $townId);
$statement2->execute();
$result2 = $statement2->fetch();
$statement2->closeCursor();

$hordeSize = $result2['hordeSize'];
$defenceSize = $result2['defenceSize'];
$dayNumber = $result2['dayNumber'];
$readyRes = $result2['readyResidents'];
$maxRes = $result2['maxResidents'];
$deadRes = $result2['deadResidents'];
$aliveRes = $maxRes - $deadRes;

echo "User ID: " . $_SESSION['user_id'] . " ";
echo "| Char ID: " . $_SESSION['char_id'];

$dropAll = filter_input(INPUT_POST, 'dropAllItems');
if (isset($dropAll)) {
	if ($dropAll == "true") {
		//drop all items
		dropAllItemsExt($charObject->id);
		//Reload the header to properly clear post data so data will not be resubmitted when character is changed
		echo '<meta http-equiv="refresh" content="0">';
	}
}


?>

	<div class="header">
	<a href="<?php echo "../inTown/?locat=inTown"; ?>"><img src="../images/DeadFallLogo2.png"></a>
	</div>	
	
	<div class="infoSet">
		<div class="infoSet1"><p><b>Town: </b><?php echo htmlspecialchars($convertedTownName)?></p></div>
		<!-- <div class="infoSet1"><p><?php echo $readyRes . '/' . $aliveRes . ' Ready'?></p></div> -->
		<div class="infoSet1"><p><?php echo '<img src="../images/icons/sword.png" title="Horde Size"> ' . $hordeSize?> | <?php echo $defenceSize . ' <img src="../images/icons/shield.png" title="Defence Amount"> '?></p></div>
		
		<div class="infoset2"><p><?php echo "<b>User:</b> " . htmlspecialchars($playerName)?></p></div>
		<div class="infoset2"><p><b>Character: </b><img src="../images/leftArrow.png" onclick=changeChar(<?php echo $previousChar->id; ?>) class="headNavArrowLeft" title="<?php echo $previousChar->character; ?>"> <?php echo htmlspecialchars($charName)?> <img src="../images/rightArrow.png" onclick=changeChar(<?php echo $nextChar->id; ?>) title ="<?php echo $nextChar->character; ?>" class="headNavArrowRight"> | 
		<?php echo ' Lv. ' . htmlspecialchars($charLevel)?> | 
		<?php echo ' <img src="' . $root . '/images/icons/' . lcfirst(htmlspecialchars($charClass)) .  '.png" title="' . htmlspecialchars($charClass) . '"> ' . htmlspecialchars($charClass); ?></p></div>
		<br>


		<div class="infoSet2" style="clear: both;">
			<p><b>Location: </b><?php echo $_SESSION['x'] . ", " . $_SESSION['y']?>

			<?php 
			//if Day is ended and the character is outside of town... Draw a tent
			if(($_SESSION["x"] != 0 || $_SESSION["y"] != 0) && doesStatusContain(10)){
				echo "<img src='../images/icons/tent.png' alt='Camping Out' title='Camped Outside'>";
			}
			?>
		</p></div>
		
		
		<div class="infoset2"><p><?php echo $currentAp . '/' . $maxAp . '<img src="../images/icons/ap.png" title="Action Points">'?></p></div>

		<div class="headerBottom">
		<div class="infoSet3"><p><b>Day</b>  <?php echo $dayNumber . " (" . $readyRes . '/' . $aliveRes . ' Ready)'?></p>
			<form action='' method='post' name='end' id='endForm' onsubmit='return confirm("Are you sure you are done with this character for the day?")'>
				<input hidden value='end' name='endDay'>
				<button type='submit' id='endButton' onclick='verify()' class="endButton" value='End Day' id='endDayButtonContainer'><span id="endDayButtonText"><litegreen>Set Ready</litegreen></span></button>
			</form>
			<!-- <button type="submit" value="" class="endButton"><span>Ready</span></button> -->
		</div>
		
		<!-- Display Inventory -->
		<div class="infoset3"><form action="" method="post" style="display: inline;"><p><?php 
		if ($itemsHeld != NULL)
		{
			echo '<u><b>Inventory</b></u> <grp id="carryCapacity">(' . $currentMass . '/' . $weightCapacity . ')</grp> <input type="image" style="top: 2px; position: relative; display: inline;" src="../images/icons/dropIcon.png" title="Drop All Items"><input type="hidden" name="dropAllItems" value="true"></form>';
			echo '<form id="sendItemData" name="sendItemData" method="post">';
			for ($i = 0; $i < sizeOf($itemsHeldArray); $i++)
			{
				$itemName = $itemsMaster[$itemsHeldArray[$i]][0];
				$itemWeight = $itemsMaster[$itemsHeldArray[$i]][2];
				$itemCategory = $itemsMaster[$itemsHeldArray[$i]][1];
				
				echo '<input type="hidden" name="location" value="'. $loc . '">';
				echo '<input type="hidden" name="itemName' . $i . '" value="'. $itemName . '">';				
				echo '<div class="popup" onclick="popUpMenu(`popUp' . $i . '`)"><img src="../images/items/' . $itemName . '.png" class="item"><img src="../images/rarity/' . getRarityString($itemsHeldArray[$i]) . '.png" title="' . $itemName . '" class="rarityBanner">';
				echo '<span class="popuptext" id="popUp' . $i . '">';
				echo '<p><u>' . $itemName . '</u></p><p class="rarity" style="">' . getRarityString($itemsHeldArray[$i]) . '</p><p class="weight">Weight: ' . $itemWeight . '</p>';
				if($itemCategory == "Consume"){
					foreach($itemsConsumable as $check){
						if ($check[0] == $itemsHeldArray[$i]){
							echo "<p>" . $check[2] . "% AP</p>";
						}
					}
				}
				echo '<input class="act_button" onclick="newAction(`drop`, ' . $i .')" type="submit" value="Drop"><br>';
				if (checkUsability($itemsHeldArray[$i]) == 'Eat' || checkUsability($itemsHeldArray[$i]) == 'Drink' || checkUsability($itemsHeldArray[$i]) == 'Load')
				{
					echo '<input class="act_button" onclick="newAction(`' . checkUsability($itemsHeldArray[$i]) . '`, ' . $i . ')" type="submit" value="' . checkUsability($itemsHeldArray[$i]) . '">';					
				}
				elseif (checkUsability($itemsHeldArray[$i]) == 'Attack' && strpos($loc, 'outside') !== false && !($_SESSION['x'] == 0 && $_SESSION['y'] == 0)) //function is attack and location is outside AND out of town coords
				{
					echo '<input class="act_button" onclick="newAction(`' . checkUsability($itemsHeldArray[$i]) . '`, ' . $i . ')" type="submit" value="' . checkUsability($itemsHeldArray[$i]) . ' (1 AP)">';					
				}
				echo '</span></div>';
			}
			echo '</form>';
		}
		else
		{echo '<u><b>Inventory</b></u> <grp id="carryCapacity">(' . $currentMass . '/' . $weightCapacity . ')</grp><br><br>Empty';}
		?>
		</p></div>
		<!-- Display Status Effects -->
		<div class="infoset3"><p><?php 
		if ($status != NULL)
		{
			echo '<u><b>Status</b></u><form>';
			for ($i = 0; $i < sizeOf($statusArray); $i++)
			{
				$statusName = $statusMaster[$statusArray[$i]];
				echo '<img src="../images/status/' . $statusName . '.png" title="' . $statusName . '">';
			}
			echo '</form></p>';
		}
		?>
		</div>
	</div>
		
	</div>
<div class="taskBox">
    <a href="./?locat=construction"><img src="../images/construction.png" title="Construction"></a>
	<a href="./?locat=special"><img src="../images/special.png" title="Special Structures"></a>
    <a href="./?locat=warehouse"><img src="../images/storage.png" title="Warehouse"></a>
    <a href="./?locat=citizens"><img src="../images/citizens.png" title="Citizens"></a>
    <a href="./?locat=character"><img src="../images/stats.png" title="Character Info"></a>
    <a href="./?locat=outside"><img src="../images/outside.png" title="Outside Map"></a>
</div>

<script type="text/javascript">
	// function verify()
	// {
	// 	if (confirm('You are done using this character for the in-game day?'))
	// 	{
	// 		document.end.submit();
	// 	}
	// }

	function dayAlreadyEnded()
	{
		endButton.setAttribute('disabled', 'disabled');
	}
</script>

<?php
if (doesStatusContain(10)) {
	//Char has already ended the day
	echo '<script>dayAlreadyEnded();</script>';
}

//Only one character needs to set ready AND its the current character
echo "<script>";
if($maxRes - $deadRes - $readyRes == 1 && doesStatusContain(11)){
	echo "document.getElementById('endDayButtonText').innerHTML = '<red>End Day</red>';";
	echo "document.end.setAttribute('onsubmit', 'return confirm(`WARNING: Ending the day for this character will initiate the horde attack and begin a new day. Are you sure you wish to continue?`)')";
}
elseif(doesStatusContain(10)){ //Character Has already Set Ready
	echo "document.getElementById('endDayButtonText').innerHTML = '<yellow>Ended</yellow>';";
	echo "document.getElementById('endButton').className = 'endButtonDisabled';";
}

if($_SESSION['x'] != 0 || $_SESSION['y'] != 0){
	echo "document.end.setAttribute('onsubmit', 'return confirm(`WARNING: Are you sure you would like to camp outside for the night?`)')";
}
echo "</script>";
?>
