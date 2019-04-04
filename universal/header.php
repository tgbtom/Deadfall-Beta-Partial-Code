<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<script type="text/javascript" src="../js/header.js"></script>

<style>
/* Popup container */
.popup {
    position: relative;
    display: inline-block;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* The actual popup */
.popup .popuptext {
    visibility: hidden;
    width: 160px;
    background-color: #555;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 8px 0;
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    margin-left: -80px;
}

/* Popup arrow */
.popup .popuptext::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #555 transparent transparent transparent;
}

/* Toggle this class - hide and show the popup */
.popup .show {
    visibility: visible;
    -webkit-animation: fadeIn 1s;
    animation: fadeIn 1s;
}

/* Add animation (fade in the popup) */
@-webkit-keyframes fadeIn {
    from {opacity: 0;} 
    to {opacity: 1;}
}

@keyframes fadeIn {
    from {opacity: 0;}
    to {opacity:1 ;}
}

.rarityBanner  
{
	position: absolute;
	top: -2px;
	left: -2px;
}

.item
{
	margin-right: 4px;
}
</style>
</head>
<body style="text-align:center">


<script>

/*$(document).click(function(){
  	var popuptext = document.getElementsByClassName('popuptext');
	for (var i = 0; i < popuptext.length; i++)
	{
		popuptext[i].style.visibility = 'hidden';
	}
  });*/

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
</script>

</body>
</html>

<?php 
include ("../data/items.php");
include ("../data/status.php");

require_once ("../functions/queryFunctions.php");

//gets the user and current character, and stores them in local variables
$user = $_SESSION['login'];
$char = $_SESSION['char'];

//Query loads the row in the characters DB that corresponds to the currently logged character
$query1 = 'SELECT * FROM `characters` WHERE `character` = :character AND `username` = :username';
$statement1 = $dbCon->prepare($query1);
$statement1->bindValue(':username', $user);
$statement1->bindValue(':character', $char);
$statement1->execute();
$charDetails = $statement1->fetch();
$statement1->closeCursor();

$weightCapacity = $charDetails['maxItems'];
$currentMass = $charDetails['itemsMass'];
$itemsHeld = $charDetails['items'];
$itemsHeldArray = explode(',', $itemsHeld);
$status = $charDetails['status'];
$statusArray = explode('.', $status);

$currentAp = $charDetails['currentAP'];
$maxAp = $charDetails['maxAP'];
$townName = $charDetails['townName'];

$loc = $_SERVER['REQUEST_URI'];

//query loads the row in the towns DB that corresponds to the current town 
$query2 = 'SELECT * FROM `towns` WHERE `townName` = :townName';
$statement2 = $dbCon->prepare($query2);
$statement2->bindValue(':townName', $townName);
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

?>

<div class="header">
	<a href="<?php echo "../inTown/?locat=inTown"; ?>"><img src="../images/DeadFallLogo2.png"></a>
	</div>	
	
	<div class="infoSet">
		<div class="infoSet1"><p><?php echo htmlspecialchars($townName)?></p></div>
		<div class="infoSet1"><p><?php echo 'Day ' . $dayNumber?></p></div>
		<div class="infoSet1"><p><?php echo $readyRes . '/' . $aliveRes . ' Ready'?>
		</p></div>
		<div class="infoSet1"><p><?php echo '<img src="../images/icons/zombie.png" title="Horde Size"> ' . $hordeSize?></p></div>
		<div class="infoSet1"><p><?php echo '<img src="../images/icons/shield.png" title="Defence Amount"> ' . $defenceSize?></p></div>
		<div class="infoset2"><p><?php echo "logged in as: " . htmlspecialchars($playerName)?></p></div>
		<div class="infoset2"><p><?php echo '<img src="' . $root . '/images/icons/' . lcfirst(htmlspecialchars($charClass)) .  '.png" title="' . htmlspecialchars($charClass) . '"> ' . htmlspecialchars($charClass); ?></p></div>
		<div class="infoset2"><p><?php echo htmlspecialchars($charName)?></p></div>
		<div class="infoset2"><p><?php echo 'Lvl ' . htmlspecialchars($charLevel)?></p></div>
		<div class="infoSet2"><p><?php echo $_SESSION['x'] . ", " . $_SESSION['y']?></p></div>
		<div class="infoset2"><p><?php echo $currentAp . '/' . $maxAp . '<img src="../images/icons/ap.png" title="Action Points">'?></p></div>
		<div class="infoset2"><p id="carryCapacity"><?php echo 'Mass: ' . $currentMass . '/' . $weightCapacity; ?></p></div>
		<!-- Display Inventory -->
		<div class="infoset2"><p><?php 
		if ($itemsHeld != NULL)
		{
			echo '<u><b>Inventory</b></u>';
			echo '<form id="sendItemData" name="sendItemData" method="post">';
			for ($i = 0; $i < sizeOf($itemsHeldArray); $i++)
			{
				$itemName = $itemsMaster[$itemsHeldArray[$i]][0];
				$itemWeight = $itemsMaster[$itemsHeldArray[$i]][2]; 
				
				echo '<input type="hidden" name="location" value="'. $loc . '">';
				echo '<input type="hidden" name="itemName' . $i . '" value="'. $itemName . '">';				
				echo '<div class="popup" onclick="popUpMenu(`popUp' . $i . '`)"><img src="../images/items/' . $itemName . '.png" class="item"><img src="../images/rarity/' . getRarityString($itemsHeldArray[$i]) . '.png" title="' . $itemName . '" class="rarityBanner">';
				echo '<span class="popuptext" id="popUp' . $i . '">';
				echo '<p><u>' . $itemName . '</u></p><p class="rarity" style="">' . getRarityString($itemsHeldArray[$i]) . '</p><p class="weight">Weight: ' . $itemWeight . '</p>';
				echo '<input onclick="newAction(`drop`, ' . $i .')" type="submit" value="Drop">';
				if (checkUsability($itemsHeldArray[$i]) == 'Eat' || checkUsability($itemsHeldArray[$i]) == 'Drink' || checkUsability($itemsHeldArray[$i]) == 'Load')
				{
					echo '<input onclick="newAction(`' . checkUsability($itemsHeldArray[$i]) . '`, ' . $i . ')" type="submit" value="' . checkUsability($itemsHeldArray[$i]) . '">';					
				}
				elseif (checkUsability($itemsHeldArray[$i]) == 'Attack' && strpos($loc, 'outside') !== false && !($_SESSION['x'] == 0 && $_SESSION['y'] == 0)) //function is attack and location is outside AND out of town coords
				{
					echo '<input onclick="newAction(`' . checkUsability($itemsHeldArray[$i]) . '`, ' . $i . ')" type="submit" value="' . checkUsability($itemsHeldArray[$i]) . '">';					
				}
				echo '</span></div>';
			}
			echo '</form>';
		}
		else
		{echo 'empty';}
		?>
		</p></div>
		<!-- Display Status Effects -->
		<div class="infoset2"><p><?php 
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
<div class="taskBox">
    <a href="./?locat=construction"><img src="../images/leaveTown.png" title="Construction"></a>
	<a href="./?locat=special"><img src="../images/leaveTown.png" title="Special Structures"></a>
    <a href="./?locat=warehouse"><img src="../images/storage.png" title="Warehouse"></a>
    <a href="./?locat=citizens"><img src="../images/citizens.png" title="Citizens"></a>
    <a href="./?locat=character"><img src="../images/stats.png" title="Character Info"></a>
    <a href="./?locat=outside"><img src="../images/outside.png" title="Outside Map"></a>
</div>
