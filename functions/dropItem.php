<?php
require_once ("../connect.php");
require_once ("./verifyLogin.php");
include ("../data/items.php");
require_once ("../model/database.php");

$dbCon = Database::getDB();

//gets the user and current character, and stores them in local variables
$user = $_SESSION['login'];
$char = $_SESSION['char'];
$charId = $_SESSION['char_id'];
$x = $_SESSION['x'];
$y = $_SESSION['y'];

//Gets the name of the item being dropped
$nameTagId = filter_input(INPUT_GET, 'nameTagId');
$nameTag = 'itemName' . $nameTagId;
$itemName = filter_input(INPUT_POST, $nameTag);
$location = filter_input(INPUT_POST, 'location');

//if trying to drop an item at 0,0 while on outside.php, dont allow
//if trying to drop an item in warehouse while out of town, dont allow
//otherwise, proceed with item dropping protocol
if (strpos($location, 'outside') !== false && ($x == 0 && $y == 0))
{echo '<script>window.location = "' . $root . '/inTown/?locat=outside&e=You%20may%20not%20drop%20items%20here!";</script>';}
else if (strpos($location, 'warehouse') !== false && ($x != 0 || $y != 0))
{echo '<script>window.location = "' . $root . '/inTown/?locat=warehouse&e=You%20may%20not%20drop%20items%20here!";</script>';}
else if (strpos($location, 'outside') == false && ($x != 0 || $y != 0))
{
$location2 = str_replace('/deadfall/', '', $location);
$location3 = str_replace('.php', '', $location2);
echo '<script>window.location = "' . $root . '/inTown/?locat=inTown&e=You%20may%20not%20drop%20items%20here!";</script>';
}
else
{
	//Determine The weight of the item by finding it in the items data array
	for ($i = 0; $i < sizeOf($itemsMaster); $i++)
	{
		if ($itemsMaster[$i][0] == $itemName)
		{
			$itemWeight = $itemsMaster[$i][2];
			$itemId = $i;
		}
	}

	//Check if Character is still holding the item being dropped
	$query1 = 'SELECT * FROM `characters` WHERE `username` = :username AND `id` = :charId';
	$statement1 = $dbCon->prepare($query1);
	$statement1->bindValue(':username', $user);
	$statement1->bindValue(':charId', $charId);
	$statement1->execute();
	$result1 = $statement1->fetch();
	$statement1->closeCursor();

	$townId = $result1['town_id'];
	$townName = Towns::getTownNameById($townId);
	$townTableName = Towns::getTownTableName($townId);
	$oldMass = $result1['itemsMass'];
	$newMass = $oldMass - $itemWeight;
	$currentItemsArray = explode(',', $result1['items']);
	$foundItem = false;

	for ($i = 0; $i < sizeOf($currentItemsArray); $i++)
	{
			if (($currentItemsArray[$i] == $itemId) && ($foundItem == false))
			{
				$foundItem = true;
				if (sizeOf($currentItemsArray) == 1)
				{$newItems = NULL;}
			}
			else if (!(isset($newItems)))
			{
				$newItems= $currentItemsArray[$i];
			}
			else
			{
				$newItems = $newItems . ',' . $currentItemsArray[$i];
			}
	}


	if ($foundItem)
	{	
		//Removes the item and decreases used weight capacity in the DB
		$query2 = 'UPDATE `characters` SET `items` = :newItems , `itemsMass` = :newMass WHERE `username` = :username AND `id` = :charId';
		$statement2 = $dbCon->prepare($query2);
		$statement2->bindValue(':newItems', $newItems);
		$statement2->bindValue(':newMass', $newMass);
		$statement2->bindValue(':username', $user);
		$statement2->bindValue(':charId', $charId);
		$statement2->execute();
		$statement2->closeCursor();

		//Find what items are on the ground in the characters current zone
		$query3 = 'SELECT * FROM `' . $townTableName . '` WHERE `x` = :x AND `y` = :y';
		$statement3 = $dbCon->prepare($query3);
		$statement3->bindValue(':x', $x);
		$statement3->bindValue(':y', $y);
		$statement3->execute();
		$result3 = $statement3->fetch();
		$statement3->closeCursor();
		$groundItems = $result3['groundItems'];
	
		//Determine the new string of items to go on the ground ***STRING IS DIFFERENT IF COORDS ARE 0,0

	
		//if coords are 0,0, apply the item to an existing stack or create a new one
		//IF outside, just drop the item as usual
		
		if ($x == 0 && $y == 0)
		{
			//Determine if there is already a stack for this ID
                        $newGroundItems = NULL;
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
		}
		else
		{
			if ($groundItems == NULL)
			{
				$newGroundItems = $itemId;
			}	
			else 
			{
				$newGroundItems = $groundItems . ',' . $itemId;
			}
		}
			//Update the ground in the current zone with the item being dropped
			$query4 = 'UPDATE `' . $townTableName . '` SET `groundItems` = :newGroundItems WHERE `x` = :x AND `y` = :y';
			$statement4 = $dbCon->prepare($query4);
			$statement4->bindValue(':newGroundItems', $newGroundItems);
			$statement4->bindValue(':x', $x);
			$statement4->bindValue(':y', $y);
			$statement4->execute();
			$statement4->closeCursor();
	
		if (strpos($location, 'outside') !== false)
		{
			echo '<script>window.location = "' . $root . '/inTown/?locat=outside";</script>';
		}
		else
		{
			echo '<script>window.location = "' . $root . '/inTown/?locat=warehouse";</script>';
		}
	
	}
	else
	{
		if (strpos($location, 'outside') !== false)
		{
			echo '<script>window.location = "' . $root . '/inTown/?locat=outside&e=Attempting%20to%20drop%20an%20item%20that%20no%20longer%20exists!";</script>';
		}
		else
		{
			echo '<script>window.location = "' . $root . '/inTown/?locat=warehouse&e=Attempting%20to%20drop%20an%20item%20that%20no%20longer%20exists!";</script>';
		}
	}
}


?>