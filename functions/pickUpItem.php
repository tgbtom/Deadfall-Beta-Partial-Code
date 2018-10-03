<?php 
require_once ("../connect.php");
require_once ("./verifyLogin.php");
Include ("../data/items.php");

//gets the user and current character, and stores them in local variables
$user = $_SESSION['login'];
$char = $_SESSION['char'];
$x = $_SESSION['x'];
$y = $_SESSION['y'];

//Gets the name of the item attempting to be picked up
$itemName = filter_input(INPUT_POST, 'itemName2');
$location = filter_input(INPUT_POST, 'location');

//Determine The weight of the item by finding it in the items data array
for ($i = 0; $i < sizeOf($itemsMaster); $i++)
{
	if ($itemsMaster[$i][0] == $itemName)
	{
		$itemWeight = $itemsMaster[$i][2];
		$itemId = $i;
	}
}
	
//Query the Database to find Weight Capacity and current Inventory weight
$query1 = 'SELECT * FROM `characters` WHERE `character` = :character AND `username` = :username';
$statement1 = $dbCon->prepare($query1);
$statement1->bindValue(':username', $user);
$statement1->bindValue(':character', $char);
$statement1->execute();
$charDetails = $statement1->fetch();
$statement1->closeCursor();

$townName = $charDetails['townName'];
$characterId = $charDetails['id'];
$currentItems = $charDetails['items'];
$weightCapacity = $charDetails['maxItems'];
$currentMass = $charDetails['itemsMass'];
$remainingCapacity = $weightCapacity - $currentMass;

//IF item is still on the ground, check if character can carry it. Then remove it from the ground, and add it to the Char's inventory
//Remove the Item from the Ground in the zone
if ($itemWeight <= $remainingCapacity)
{		
	$query2 = 'SELECT * from `' . $townName . '` WHERE `x` = :x AND `y` = :y';
	$statement2 = $dbCon->prepare($query2);
	$statement2->bindValue(':x', $_SESSION['x']);
	$statement2->bindValue(':y', $_SESSION['y']);
	$statement2->execute();
	$result2 = $statement2->fetch();
	$statement2->closeCursor();
	
	$groundArray = explode(',', $result2['groundItems']);
	$itemFound = false;
	
	if ($x == 0 && $y == 0)
	{
		for ($i = 0; $i < sizeOf($groundArray); $i++)
		{
			$currentItemSplit = explode('.', $groundArray[$i]);
			$currentItemId = $currentItemSplit[0];
			$currentItemAmount = $currentItemSplit[1];
			$potentialItemAmount = $currentItemAmount - 1;
			
			if ($currentItemId == $itemId && $itemFound == false)
			{
				$itemFound = true;
				if ($potentialItemAmount > 0)
				{
					if ($newGroundItems == NULL)
					{
						$newGroundItems = $currentItemId . '.' . $potentialItemAmount;
					}
					else
					{
						$newGroundItems = $newGroundItems . ',' . $currentItemId . '.' . $potentialItemAmount;
					}
				}
			}
			else if($newGroundItems == NULL)
			{	
			$newGroundItems = $currentItemId . '.' . $currentItemAmount;
			}
			else
			{
				$newGroundItems = $newGroundItems . ',' . $currentItemId . '.' . $currentItemAmount;
			}
		}
	}
	else
	{
		for ($i = 0; $i < sizeOf($groundArray); $i++)
		{
			if ($groundArray[$i] == $itemId && $itemFound == false)
			{
				$itemFound = true;
			}
		
			else if(!isset($newGroundItems))
			{	
			$newGroundItems = $groundArray[$i];
			}
		
			else
			{
				$newGroundItems = $newGroundItems . ',' . $groundArray[$i];
			}
		}
	}
	
	if($itemFound == true)
	{
	
	$query3 = 'UPDATE `' . $townName . '` SET `groundItems` = :newItems WHERE `x` = :x AND `y` = :y';
	$statement3 = $dbCon->prepare($query3);
	$statement3->bindValue(':newItems', $newGroundItems);
	$statement3->bindValue(':x', $_SESSION['x']);
	$statement3->bindValue(':y', $_SESSION['y']);
	$statement3->execute();
	$statement3->closeCursor();

		//IF the character has enough capacity to carry the item, it will be added to his inventory
			if ($currentItems == NULL)
			{
				$newItems = $itemId;
			}
			else
			{
				$newItems = $currentItems . ',' . $itemId;
			}
	
			$newItemsMass = $currentMass + $itemWeight;
	
			$query2 = 'UPDATE `characters` SET `items` = :newItems , `itemsMass` = :newItemsMass WHERE `id` = :id';
			$statement2 = $dbCon->prepare($query2);
			$statement2->bindValue(':id', $characterId);
			$statement2->bindValue(':newItems', $newItems);
			$statement2->bindValue(':newItemsMass', $newItemsMass);
			$statement2->execute();
			$statement2->closeCursor();

	
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
			echo '<script>window.location = "' . $root . '/inTown/?locat=outside&e=Item%20could%20not%20be%20found%20in%20this%20location";</script>';
		}
		else
		{
			echo '<script>window.location = "' . $root . '/inTown/?locat=warehouse&e=Item%20could%20not%20be%20found%20in%20this%20location";</script>';
		}
		
	}
}
else
{
	//Error: You are already carrying too much.
		if (strpos($location, 'outside') !== false)
		{
			echo '<script>window.location = "' . $root . '/inTown/?locat=outside&e=You%20are%20already%20carrying%20too%20much!";</script>';
		}
		else
		{
			echo '<script>window.location = "' . $root . '/inTown/?locat=warehouse&e=You%20are%20already%20carrying%20too%20much!";</script>';
		}
	
}


?>