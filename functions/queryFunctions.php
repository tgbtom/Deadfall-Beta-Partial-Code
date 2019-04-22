<?php 
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");
include ("../data/items.php");

//gets the user and current character, and stores them in local variables
if (isset($_SESSION['login'])){
	$user = $_SESSION['login'];
}

if (!isset($_SESSION['char']))
{
	$_SESSION['char'] = '';
}
else
{
	$char = $_SESSION['char'];
}
if (!isset($_SESSION['x']))
{
	$_SESSION['x'] = NULL;
}
else
{
	$x = $_SESSION['x'];
}
if (!isset($_SESSION['y']))
{
	$_SESSION['y'] = NULL;
}
else
{
	$y = $_SESSION['y'];
}

function getCharDetails()
{
	global $char;
	global $user;
	global $dbCon;
	
	$query = 'SELECT * FROM `characters` WHERE `username` = :user AND `character` = :char';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':user', $user);
	$statement->bindValue(':char', $char);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	return $result;
}


//get char status to be used in status functions below
$charDetails = getCharDetails();
$statusString = $charDetails['status'];
$statusArray = explode('.', $statusString);
$townName = $charDetails['townName'];


function getTownDetails($townName)
{
	global $dbCon;
	
	$query = 'SELECT * FROM `towns` WHERE `townName` = :townName';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':townName', $townName);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	return $result;

}

function getWarehouseItems($townName)
{
	global $dbCon;
	
	$query = 'SELECT * FROM`' . $townName . '` WHERE `x` = 0 AND `y` = 0';
	$statement = $dbCon->prepare($query);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	return $result['groundItems'];
}

function doesStatusContain($statusId)
{

	global $statusString;

	$statusArray = explode('.', $statusString);

	for ($i = 0; $i < sizeof($statusArray); $i++)
	{
		if ($statusArray[$i] == $statusId)
		{return true;}
	}
}

function doesStatusContainExt($statusId, $charName, $user = null)
{
	global $dbCon;
	if ($user === NULL)
	{
		global $user;
	}
	
	$query = 'SELECT * FROM `characters` WHERE `username` = :user AND `character` = :char';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':user', $user);
	$statement->bindValue(':char', $charName);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	$statusStringExt = $result['status'];
	$statusArrayExt = explode('.', $statusStringExt);
	
	for ($i = 0; $i < sizeOf($statusArrayExt); $i++)
	{
		if ($statusArrayExt[$i] == $statusId)
		{return true;}
	}
	
}

//When Second Argument is NULL, function will only remove the status specified by the first argument
function replaceStatus($oldStat, $newStat) //Replace one status with another one, for the current character in session
{
	global $statusArray;
	global $dbCon;
	global $user;
	global $char;
	
	$statFound = false;
	
	for ($i = 0; $i < sizeOf($statusArray); $i++)
	{
		if ($statusArray[$i] == $oldStat)
		{
			$statFound = true;
			if (sizeOf($statusArray) == 1)
			{$newStatus = '';}
		}
		else if (!isset($newStatus))
		{
			$newStatus = $statusArray[$i];
		}
		else
		{
			$newStatus = $newStatus . '.' . $statusArray[$i];
		}
	}
	//If the stat was found, it has been removed from the string: Now add the new stat Update the DB
	if ($statFound)
	{
		if ($newStat != NULL)
		{
			if ($newStatus == '')
			{$newStatus = $newStat;}
			else
			{$newStatus = $newStatus . '.' . $newStat;}
		}
	}
	
	$query3 = 'UPDATE `characters` SET `status` = :newStatus WHERE `username` = :username AND `character` = :character';
	$statement3 = $dbCon->prepare($query3);
	$statement3->bindValue(':newStatus', $newStatus);
	$statement3->bindValue(':username', $user);
	$statement3->bindValue(':character', $char);
	$statement3->execute();
	$statement3->closeCursor();

}

//When Second Argument is NULL, function will only remove the status specified by the first argument
function replaceStatusExt($oldStat, $newStat, $charName, $user = null) //Function to replace status effects from characters that are not logged into the current session
{
	
	global $dbCon;
        if ($user == null){
          global $user;  
        }

	$query = 'SELECT * FROM `characters` WHERE `username` = :user AND `character` = :char';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':user', $user);
	$statement->bindValue(':char', $charName);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	$statusStringExt = $result['status'];
	$statusArrayExt = explode('.', $statusStringExt);
	
	$statFound = false;
	for ($i = 0; $i < sizeOf($statusArrayExt); $i++)
	{
		if ($statusArrayExt[$i] == $oldStat)
		{
			$statFound = true;
			if (sizeOf($statusArrayExt) == 1)
			{$newStatus = '';}
		}
		else if (!isset($newStatus))
		{
			$newStatus = $statusArrayExt[$i];
		}
		else
		{
			$newStatus = $newStatus . '.' . $statusArrayExt[$i];
		}
	}
	//If the stat was found, it has been removed from the string: Now add the new stat Update the DB
	if ($statFound)
	{
		if ($newStat != NULL)
		{
			if ($newStatus == '')
			{$newStatus = $newStat;}
			else
			{$newStatus = $newStatus . '.' . $newStat;}
		}
	}
	
	$query3 = 'UPDATE `characters` SET `status` = :newStatus WHERE `username` = :username AND `character` = :character';
	$statement3 = $dbCon->prepare($query3);
	$statement3->bindValue(':newStatus', $newStatus);
	$statement3->bindValue(':username', $user);
	$statement3->bindValue(':character', $charName);
	$statement3->execute();
	$statement3->closeCursor();	

}

function addStatus($newStat)
{
	global $dbCon;
	global $user;
	global $char;
	
	$query1TEMP = 'SELECT * FROM `characters` WHERE `username` = :username AND `character` = :character';
	$statement1TEMP = $dbCon->prepare($query1TEMP);
	$statement1TEMP->bindValue(':username', $user);
	$statement1TEMP->bindValue(':character', $char);
	$statement1TEMP->execute();
	$result1TEMP = $statement1TEMP->fetch();
	$statement1TEMP->closeCursor();

	$statusStringTEMP = $result1TEMP['status'];
	
		$newStatus = $statusStringTEMP . '.' . $newStat;
				
		$query4 = 'UPDATE `characters` SET `status` = :newStatus WHERE `username` = :username AND `character` = :character';
		$statement4 = $dbCon->prepare($query4);
		$statement4->bindValue(':newStatus', $newStatus);
		$statement4->bindValue(':username', $user);
		$statement4->bindValue(':character', $char);
		$statement4->execute();
		$statement4->closeCursor();
}

function addStatusExt($newStat, $charName, $user = null)
{
	global $dbCon;
        if ($user == null){
           global $user; 
        }
	
	$query1TEMP = 'SELECT * FROM `characters` WHERE `username` = :username AND `character` = :character';
	$statement1TEMP = $dbCon->prepare($query1TEMP);
	$statement1TEMP->bindValue(':username', $user);
	$statement1TEMP->bindValue(':character', $charName);
	$statement1TEMP->execute();
	$result1TEMP = $statement1TEMP->fetch();
	$statement1TEMP->closeCursor();

	$statusStringTEMP = $result1TEMP['status'];
	
		$newStatus = $statusStringTEMP . '.' . $newStat;
				
		$query4 = 'UPDATE `characters` SET `status` = :newStatus WHERE `username` = :username AND `character` = :character';
		$statement4 = $dbCon->prepare($query4);
		$statement4->bindValue(':newStatus', $newStatus);
		$statement4->bindValue(':username', $user);
		$statement4->bindValue(':character', $charName);
		$statement4->execute();
		$statement4->closeCursor();
}

function getHordeSize($town)
{
	global $dbCon;
	
	//determine the current amount of zombies spawned on the ENTIRE MAP	
	$query = 'SELECT zeds FROM ' . $town;
	$statement = $dbCon->prepare($query);
	$statement->execute();
	$result = $statement->fetchAll();
	$statement->closeCursor();

	$horde = 0;
	foreach ($result as $now)
	{
		$horde += $now[0]; 
	}
	
	return $horde;
}

function getDangerLevel($zoneX, $zoneY, $townName)
{
	global $dbCon;
	$dangerLevel = 0;
	
	//zone above: if y is >= 5 set y to 5, otherwise increase it by 1
	$newY = ($zoneY >= 5) ? 5 : $zoneY + 1;
	$query1 = 'SELECT zeds FROM ' . $townName . ' WHERE `x` = :x AND `y` = :y';
	$statement1 = $dbCon->prepare($query1);
	$statement1->bindValue(':x', $zoneX);
	$statement1->bindValue(':y', $newY);
	$statement1->execute();
	$result1 = $statement1->fetch();
	$statement1->closeCursor();
	
	$dangerLevel = $result1['zeds'];
	
	//zone to the right
	$newX = ($zoneX >= 5) ? 5 : $zoneX + 1;
	$query2 = 'SELECT zeds FROM ' . $townName . ' WHERE `x` = :x AND `y` = :y';
	$statement2 = $dbCon->prepare($query2);
	$statement2->bindValue(':x', $newX);
	$statement2->bindValue(':y', $zoneY);
	$statement2->execute();
	$result2 = $statement2->fetch();
	$statement2->closeCursor();
	
	$dangerLevel += $result2['zeds'];
	
	//zone below
	$newY = ($zoneY <= -5) ? -5 : $zoneY - 1;
	$query3 = 'SELECT zeds FROM ' . $townName . ' WHERE `x` = :x AND `y` = :y';
	$statement3 = $dbCon->prepare($query3);
	$statement3->bindValue(':x', $zoneX);
	$statement3->bindValue(':y', $newY);
	$statement3->execute();
	$result3 = $statement3->fetch();
	$statement3->closeCursor();
	
	$dangerLevel += $result3['zeds'];
	
	//zone to the left
	$newX = ($zoneX <= -5) ? -5 : $zoneX - 1;
	$query4 = 'SELECT zeds FROM ' . $townName . ' WHERE `x` = :x AND `y` = :y';
	$statement4 = $dbCon->prepare($query4);
	$statement4->bindValue(':x', $newX);
	$statement4->bindValue(':y', $zoneY);
	$statement4->execute();
	$result4 = $statement4->fetch();
	$statement4->closeCursor();
	
	$dangerLevel += $result4['zeds'];
	
	//current zone
	$query5 = 'SELECT zeds FROM ' . $townName . ' WHERE `x` = :x AND `y` = :y';
	$statement5 = $dbCon->prepare($query5);
	$statement5->bindValue(':x', $zoneX);
	$statement5->bindValue(':y', $zoneY);
	$statement5->execute();
	$result5 = $statement5->fetch();
	$statement5->closeCursor();
	
	$dangerLevel += $result5['zeds'];
	
	return $dangerLevel;
}

function zedSpread($townName)
{
	global $dbCon;
	
	$query = 'SELECT * FROM ' . $townName;
	$statement = $dbCon->prepare($query);
	$statement->execute();
	$result = $statement->fetchAll();
	$statement->closeCursor();
	
	//create an array with all of the details of the map prior to updating any zones
	$mapArray = array();
	foreach ($result as $now)
	{
		//$now[0] is the current zed count min the zone
		array_push($mapArray, getDangerLevel($now['x'], $now['y'], $townName));
	}
	
	for ($i = 0; $i < sizeOf($mapArray); $i++)
	{
		//$mapArray[$i] is the danger of the current zone in the loop...
		$maxZeds = ceil($mapArray[$i] * 0.09);
		$minZeds = $maxZeds * 0.2;
		$addZeds = mt_rand($minZeds, $maxZeds);
		
		$query1 = 'SELECT * FROM ' . $townName . ' WHERE `id` = :id';
		$statement1 = $dbCon->prepare($query1);
		$statement1->bindValue(':id', $i);
		$statement1->execute();
		$result1 = $statement1->fetch();
		$statement1->closeCursor();
		
		$currentZeds = $result1['zeds'];
		$newZedCount = $currentZeds + $addZeds;
		
		//ensure to spawn no zombies at the town, if a zone has 0 zeds, 40% chance to spawn 1 or 2 zeds
		if($result1['x'] == 0 && $result1['y'] == 0)
		{
			$newZedCount = 0;
			continue;
		}
		elseif ($newZedCount == 0 && mt_rand(0,100) < 40)
		{
			$newZedCount = mt_rand(1,2);
		}

		
		$query2 = 'UPDATE ' . $townName . ' SET `zeds` = :newZeds WHERE `id` = :id';
		$statement2 = $dbCon->prepare($query2);
		$statement2->bindValue(':newZeds', $newZedCount);
		$statement2->bindValue(':id', $i);
		$statement2->execute();
		$statement2->closeCursor();
	}
}

function lootItem()
{
	global $x;
	global $y;
	global $itemsMaster;
	global $dbCon;
	global $townName;
	global $char;
	
	$rarityArray = array();
	
	//check if zone is depleted? ****************************************
	$query = 'SELECT * FROM ' . $townName . ' WHERE `x` = :x AND `y` = :y';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':x', $x);
	$statement->bindValue(':y', $y);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	$depletion = $result['lootability'];
	$oldBulletin = $result['bulletin'];
	
	//determine rarity of item to be picked updating, then create an array with all of the results
	$random = mt_rand(1,200);
	if ($depletion <= 0)
	{
		//this means depleted zone will always give item #0 (MUST BE CHANGED TO SPECIAL RARITY)
		foreach ($itemsMaster as $itemsMasterCur)
		{
			//if rarity = 5 the item is scrap
			if ($itemsMasterCur[3] == 5)
			{
				//add the item name to the end of the rarity array (scrap)
				array_push($rarityArray, $itemsMasterCur[0]);
			}
		}
	}
	elseif ($random <= 130) //65%
	{
		//common
		$rarity = 0;
		foreach ($itemsMaster as $itemsMasterCur)
		{
			//if rarity = 0 the item is common
			if ($itemsMasterCur[3] == 0)
			{
				//add the item name to the end of the rarity array (common)
				array_push($rarityArray, $itemsMasterCur[0]);
			}
		}
	}
	elseif ($random <= 172) //21%
	{
		//uncommon
		$rarity = 1;
		foreach ($itemsMaster as $itemsMasterCur)
		{
			//if rarity = 1 the item is uncommon
			if ($itemsMasterCur[3] == 1)
			{
				//add the item name to the end of the rarity array (uncommon)
				array_push($rarityArray, $itemsMasterCur[0]);
			}
		}
	}
	elseif ($random <= 194) //11%
	{
		//rare
		$rarity = 2;
		foreach ($itemsMaster as $itemsMasterCur)
		{
			//if rarity = 2 the item is rare
			if ($itemsMasterCur[3] == 2)
			{
				//add the item name to the end of the rarity array (rare)
				array_push($rarityArray, $itemsMasterCur[0]);
			}
		}
	}
	elseif ($random <= 199) //2.5%
	{
		//ultra-rare
		$rarity = 3;
		foreach ($itemsMaster as $itemsMasterCur)
		{
			//if rarity = 3 the item is ultra-rare
			if ($itemsMasterCur[3] == 3)
			{
				//add the item name to the end of the rarity array (ultra-rare)
				array_push($rarityArray, $itemsMasterCur[0]);
			}
		}
	}
	elseif ($random == 200) //0.5%
	{
		//legendary
		$rarity = 4;
		foreach ($itemsMaster as $itemsMasterCur)
		{
			//if rarity = 4 the item is legendary
			if ($itemsMasterCur[3] == 4)
			{
				//add the item name to the end of the rarity array (legendary)
				array_push($rarityArray, $itemsMasterCur[0]);
			}
		}
	}
	
	//random # between 0 and size of rarity array - 1
	$randomItem = $rarityArray[array_rand($rarityArray)];	
	//Update the depletion value
	if ($depletion > 0)
	{
		$depletion--;
	}
	elseif ($depletion < 0)
	{
		$depletion = 0;
	}
	
	if($oldBulletin == NULL)
	{
		$newBulletin = $randomItem . ' was looted by ' . $char;
	}
	else
	{
		$newBulletin = $oldBulletin . '.' . $randomItem . ' was looted by ' . $char;	
	}
	
	
	$query = 'UPDATE ' . $townName . ' SET `lootability` = :lootability, `bulletin` = :newBulletin WHERE `x` = :x AND `y` = :y';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':lootability', $depletion);
	$statement->bindValue(':newBulletin', $newBulletin);
	$statement->bindValue(':x', $x);
	$statement->bindValue(':y', $y);
	$statement->execute();
	$statement->closeCursor();
	
	return $randomItem;
	
}

function pickUpItem($itemId, $itemWeight)
{
	global $dbCon;
	global $user;
	global $char;
	
	//get the current string of items held
	$query = 'SELECT * FROM `characters` WHERE `username` = :user AND `character` = :char';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':user', $user);
	$statement->bindValue(':char', $char);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	$itemsHeld = $result['items'];
	$currentMass = $result['itemsMass'];
	$newMass = $currentMass + $itemWeight;
	if ($itemsHeld == NULL || $itemsHeld == '')
	{
		$newItems = $itemId;
	}
	else
	{
		$newItems = $itemsHeld . ',' . $itemId;
	}
	
	//update the items string with the new item
	$query2 = 'UPDATE `characters` SET `items` = :newItems, `itemsMass` = :newMass WHERE `username` = :user AND `character` = :char';
	$statement2 = $dbCon->prepare($query2);
	$statement2->bindValue(':newItems', $newItems);
	$statement2->bindValue(':newMass', $newMass);
	$statement2->bindValue(':user', $user);
	$statement2->bindValue(':char', $char);
	$statement2->execute();
	$statement2->closeCursor();

}

function dropItem($itemId)
{
	global $dbCon;
	global $townName;
	global $x;
	global $y;	

	
	$query = 'SELECT * FROM ' . $townName . ' WHERE `x` = :x AND `y` = :y';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':x', $x);
	$statement->bindValue(':y', $y);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	$items = $result['groundItems'];
	if ($items == NULL || $items == '')
	{
		$newItems = $itemId;
	}
	else
	{
		$newItems = $items . ',' . $itemId;
	}
	
	$query = 'UPDATE ' . $townName . ' SET `groundItems` = :newItems WHERE `x` = :x AND `y` = :y';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':newItems', $newItems);
	$statement->bindValue(':x', $x);
	$statement->bindValue(':y', $y);
	$statement->execute();
	$statement->closeCursor();
}

function dropItemExt($itemId, $charX, $charY){

	global $dbCon;
	global $townName;
	
	$query = 'SELECT * FROM ' . $townName . ' WHERE `x` = :x AND `y` = :y';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':x', $charX);
	$statement->bindValue(':y', $charY);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	$items = $result['groundItems'];
	if ($items == NULL || $items == '')
	{
		$newItems = $itemId;
	}
	else
	{
		$newItems = $items . ',' . $itemId;
	}
	
	$query = 'UPDATE ' . $townName . ' SET `groundItems` = :newItems WHERE `x` = :x AND `y` = :y';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':newItems', $newItems);
	$statement->bindValue(':x', $charX);
	$statement->bindValue(':y', $charY);
	$statement->execute();
	$statement->closeCursor();
}

//PERHAPS RETURN THE FUNCTION ID of the item, where ID 0 is EAT, 156 could be the availability of Both Eat or Attack for example
function checkUsability($arg1) //arg1 should be a number representing the item ID
{
	global $itemsMaster;
	global $itemsConsumable;
	$category = $itemsMaster[$arg1][1];
	if ($category != "Weapon")
	{
		for ($i = 0; $i < sizeOf($itemsConsumable); $i++)
		{
			if ($itemsConsumable[$i][0] == $arg1)
			{
				$functionId = $itemsConsumable[$i][1];
				//$itemsConsumable[$i][1] = id of the function the item uses.
				if ($functionId == 0){
					//function is 'Eat'
					return 'Eat';
				}
				elseif ($functionId == 1){
					return 'Drink';
				}
				elseif ($functionId == 2){
					return 'Use';
				}
				elseif($functionId ==3){
					return 'Load';
				}
			}
		}
	}
	else if ($category == "Resource")
	{
		return "Resource";
	}
	else if ($category == "Weapon")
	{
		return "Attack";
	}
	else
	{
		return "other";
	}
}

function characterIsHolding($itemId) //Checks if the current session's character has the item specified
{	
	global $dbCon;
	global $char;
	global $user;
	
	$query = 'SELECT items FROM `characters` WHERE `username` = :user AND `character` = :char';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':user', $user);
	$statement->bindValue(':char', $char);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	$items = $result['items'];
	$itemsArray = explode(',', $items);
	foreach ($itemsArray as $curItem)
	{
		if ($curItem == $itemId)
		{
			return true;
		}
	}
	
}

function removeItem($itemId)
{
	global $dbCon;
	global $user;
	global $char;
	global $itemsMaster;
	
	$itemWeight = $itemsMaster[$itemId][2];
	
	//Check if Character is still holding the item being dropped
	$query1 = 'SELECT * FROM `characters` WHERE `username` = :username AND `character` = :character';
	$statement1 = $dbCon->prepare($query1);
	$statement1->bindValue(':username', $user);
	$statement1->bindValue(':character', $char);
	$statement1->execute();
	$result1 = $statement1->fetch();
	$statement1->closeCursor();

	$currentAp = $result1['currentAP'];
	$statusString = $result1['status'];
	$statusArray = explode('.', $statusString);
	$oldMass = $result1['itemsMass'];
	$newMass = $oldMass - $itemWeight;
	$itemsHeldArray = explode(',', $result1['items']);
	$foundItem = false;

	for ($i = 0; $i < sizeOf($itemsHeldArray); $i++)
	{
		//$itemsHeldArray[$i];
		if ($itemId == $itemsHeldArray[$i] && !($foundItem))
		{
			$foundItem = true;
			if (sizeOf($itemsHeldArray) == 1)
			{
				$newItems = NULL;	
			}
		}
		else if (!isset($newItems))
		{
			$newItems = $itemsHeldArray[$i];
		}
		else
		{
			$newItems = $newItems . ',' . $itemsHeldArray[$i];
		}
	
	}
	
	if ($foundItem)
	{
		//Removes the item and decreases used weight capacity in the DB
		$query2 = 'UPDATE `characters` SET `items` = :newItems , `itemsMass` = :newMass WHERE `username` = :username AND `character` = :character';
		$statement2 = $dbCon->prepare($query2);
		$statement2->bindValue(':newItems', $newItems);
		$statement2->bindValue(':newMass', $newMass);
		$statement2->bindValue(':username', $user);
		$statement2->bindValue(':character', $char);
		$statement2->execute();
		$statement2->closeCursor();
	}		
	
}

function getCharCoordsExt($character) //Return example => 0 => 5, 1=> -5  (Bottom right corner)
{
	global $dbCon;
	global $townName;
	
	$query = 'SELECT * FROM ' . $townName;
	$statement = $dbCon->prepare($query);
	$statement->execute();
	$result = $statement->fetchAll();
	$statement->closeCursor();
	
	foreach ($result as $current)
	{
		$CharsArray = explode('.', $current['charactersHere']);
		if (!empty($charsArray))
		{
			if (in_array($character, $charsArray))
			{
				return array($current['x'], $current['y']);
			}	
		}
	}
}

//Upon death the character drops all items, and status is all reset to default
function dropAllItemsExt($character, $user = NULL)
{
	if($user == NULL){
		global $user;
	}
	global $dbCon;
	global $townName;
	
	$query = 'SELECT items FROM `characters` WHERE `character` = :char AND `username` = :user';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':char', $character);
	$statement->bindValue(':user', $user);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	$coordsArray = getCharCoordsExt($character);
	
	$charX = $coordsArray[0];
	$charY = $coordsArray[1];
	$oldItems = explode(',', $result['items']);
	
	if ($charX == 0 && $charY == 0)
	{
		//Character died in Town
		for ($i2 = 0; $i2 < sizeOf($oldItems); $i2++)
		{
			$itemId = $oldItems[$i2];
			
			//Find what items are in the town
			$query3 = 'SELECT * FROM `' . $townName . '` WHERE `x` = "0" AND `y` = "0"';
			$statement3 = $dbCon->prepare($query3);
			$statement3->execute();
			$result3 = $statement3->fetch();
			$statement3->closeCursor();
			$groundItems = $result3['groundItems'];
			
			//Determine if there is already a stack for this ID
			$groundItemsArray = explode(',', $groundItems);
			$itemFound = false;
			$newGroundItems = NULL;
			
			for ($i = 0; $i < sizeOf($groundItemsArray); $i++)
			{
				$currentItemSplit = explode('.', $groundItemsArray[$i]);
				$currentItemId = $currentItemSplit[0];
				$currentItemAmount = $currentItemSplit[1];
				$potentialNewAmount = $currentItemAmount + 1;
				if($currentItemId == $itemId && $itemFound == false)
				{
					$itemFound = true;
					if ($newGroundItems == NULL)
					{
						$newGroundItems = $currentItemId . '.' . $potentialNewAmount;
					}
					else
					{
						$newGroundItems = $newGroundItems . ',' . $currentItemId . '.' . $potentialNewAmount;
					}
				}
				else
				{
					if ($newGroundItems == NULL)
					{
						$newGroundItems = $currentItemId . '.' . $currentItemAmount;
					}
					else
					{
						$newGroundItems = $newGroundItems . ',' . $currentItemId . '.' . $currentItemAmount;
					}	
				}
			}
			//create a 
			if ($itemFound == false)
			{
				if ($newGroundItems == NULL)
				{
					$newGroundItems = $itemId . '.1';
				}
				else
				{
					$newGroundItems = $newGroundItems . ',' . $itemId . '.1';
				}
			}
				//Update the bank items
				$query4 = 'UPDATE `' . $townName . '` SET `groundItems` = :newGroundItems WHERE `x` = "0" AND `y` = "0"';
				$statement4 = $dbCon->prepare($query4);
				$statement4->bindValue(':newGroundItems', $newGroundItems);
				$statement4->execute();
				$statement4->closeCursor();
		}
	}
	else
	{
		//Character died outside
		for ($i = 0; $i < sizeOf($oldItems); $i++)
		{
			dropItemExt($oldItems[$i], $charX, $charY);
		}
	}
	$query = 'UPDATE `characters` SET `items` = NULL, `itemsMass` = "0", `currentAP` = "0" WHERE `character` = :char AND `username` = :user';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':char', $character);
	$statement->bindValue(':user', $user);
	$statement->execute();
	$statement->closeCursor();
}

function getRarityString($itemId)
{
	global $itemsMaster;
	
	switch ($itemsMaster[$itemId][3])
	{
		case '0':
		return 'Common';
		break;
		
		case '1':
		return 'Uncommon';
		break;
		
		case '2':
		return 'Rare';
		break;
		
		case '3':
		return 'Ultra-Rare';
		break;
		
		case '4':
		return 'Legendary';
		break;

		case '5':
		return 'Scrap';
		break;
		
		default: return 'Common';
	}
	
}

function charsAlive($townName)
{
	global $dbCon;
	
	$query = 'SELECT * FROM `characters` WHERE `townName` = :townName';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':townName', $townName);
	$statement->execute();
	$result = $statement->fetchAll();
	$statement->closeCursor();
	$aliveCount = 0;
	
	foreach ($result as $current)
	{
		$character = $current['character'];
		if (doesStatusContainExt(12, $character))
		{
			//Character is Dead
		}
		else
		{
			$aliveCount++;
		}
	}
	
	return $aliveCount;
}

function closeTheTown($townName) //Officially ends the town, database table is left for legacy records
{
	global $dbCon;
	global $root;
	// All chars from the town must be reset to 'none'
	$query = 'SELECT * FROM `characters` WHERE `townName` = :townName';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':townName', $townName);
	$statement->execute();
	$result = $statement->fetchAll();
	$statement->closeCursor();
	
	foreach ($result as $current)
	{
		$characterId = $current['id'];
		
		$query = 'UPDATE `characters` SET `townName` = "none", `status` = "3.7.11" WHERE `id` = :id';
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':id', $characterId);
		$statement->execute();
		$statement->closeCursor();
	}
	
	//Set 'TownFull' to 2 -> meaning the town has completed
	$query = 'UPDATE `towns` SET `townFull` = "2" WHERE `townName` = :townName';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':townName', $townName);
	$statement->execute();
	$statement->closeCursor();
	
	//clear char session, x, AND y
	$_SESSION['x'] = NULL;
	$_SESSION['y'] = NULL;
	$_SESSION['char'] = '';
	
	echo '<script>window.location = "' . $root . '/summary.php?town=' . $townName . '";</script>';
}

function isStructureBuilt($structure, $townName)
{
	global $dbCon;
	global $buildingsInfo;
	
	$query = 'SELECT buildings FROM `towns` WHERE `townName` = :townName';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':townName', $townName);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	$buildingsString = $result['buildings'];
	
	//Get information on the required ap for structure
	foreach ($buildingsInfo as $build)
	{
		if ($build[0] == $structure)
		{
			$apRequired = $build[3];
		}
	}
	
	//Determine how much AP has already been assigned to the structure
	$buildingsArray = explode(':', $buildingsString);
	foreach ($buildingsArray as $build)
	{
		$buildingsSplit = explode('.', $build);
		$buildingName = $buildingsSplit[0];
		$buildingAp = $buildingsSplit[1];
                $buildingLevel =$buildingsSplit[2];
		
		if ($buildingName == $structure)
		{
			if ($buildingAp >= $apRequired || $buildingLevel >= 1)
			{
				return true;
			}
		}
	
	}
}

function addToBank($itemId, $townName)
{
	global $dbCon;
	
	$query = 'SELECT * FROM `' . $townName . '` WHERE `x` = :x AND `y` = :y';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':x', 0);
	$statement->bindValue(':y', 0);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	$groundItems = $result['groundItems'];
	
	$newGroundItems = NULL;
	
	//Determine the new string of items to go on the ground ***STRING IS DIFFERENT IF COORDS ARE 0,0

	
	//if coords are 0,0, apply the item to an existing stack or create a new one
	//IF outside, just drop the item as usual
		
	//Determine if there is already a stack for this ID
	$groundItemsArray = explode(',', $groundItems);
	$itemFound = false;
	for ($i = 0; $i < sizeOf($groundItemsArray); $i++)
	{
		$currentItemSplit = explode('.', $groundItemsArray[$i]);
		$currentItemId = $currentItemSplit[0];
		$currentItemAmount = $currentItemSplit[1];
		$potentialNewAmount = $currentItemAmount + 1;
		if($currentItemId == $itemId && $itemFound == false)
		{
			$itemFound = true;
			if ($newGroundItems == NULL)
			{
				$newGroundItems = $currentItemId . '.' . $potentialNewAmount;
			}
			else
			{
				$newGroundItems = $newGroundItems . ',' . $currentItemId . '.' . $potentialNewAmount;
			}
		}
		else
		{
			if ($newGroundItems == NULL)
			{
				$newGroundItems = $currentItemId . '.' . $currentItemAmount;
			}
			else
			{
				$newGroundItems = $newGroundItems . ',' . $currentItemId . '.' . $currentItemAmount;
			}
		}
	}
	//create a 
	if ($itemFound == false)
	{
		if ($newGroundItems == NULL)
		{
			$newGroundItems = $itemId . '.1';
		}
		else
		{
			$newGroundItems = $newGroundItems . ',' . $itemId . '.1';
		}
	}
	
	//Update the ground in the current zone with the item being dropped
	$query4 = 'UPDATE `' . $townName . '` SET `groundItems` = :newGroundItems WHERE `x` = :x AND `y` = :y';
	$statement4 = $dbCon->prepare($query4);
	$statement4->bindValue(':newGroundItems', $newGroundItems);
	$statement4->bindValue(':x', 0);
	$statement4->bindValue(':y', 0);
	$statement4->execute();
	$statement4->closeCursor();
}
?>