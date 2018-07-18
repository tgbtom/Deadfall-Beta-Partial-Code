<?php
require_once ('../connect.php');
require_once ('./verifyLogin.php');
require_once ('../data/buildings.php');
require_once ('../functions/queryFunctions.php');
require_once ('../data/items.php');

//gets the user and current character, and stores them in local variables
$user = $_SESSION['login'];
$char = $_SESSION['char'];
$x = $_SESSION['x'];
$y = $_SESSION['y'];

//find which town the character is in
$charDetails = getCharDetails();
$townName = $charDetails['townName'];
$currentAp = $charDetails['currentAP'];

//query the DB to determine the current buildings array (string format)
$townDetails = getTownDetails($townName);
$buildingsString = $townDetails['buildings'];
$buildingsArray = explode(':', $buildingsString);

$apToAdd = filter_input(INPUT_POST, 'apToAdd');
$buildingName = filter_input(INPUT_POST, 'buildingName');
$firstBuild = filter_input(INPUT_POST, 'firstBuild');
$apRequired = filter_input(INPUT_POST, 'apRequired');

function isFirstBuild()
{
	global $townName;
	global $buildingName;
	global $dbCon;
	
	$query = 'SELECT `buildings` FROM `towns` WHERE `townName` = :townName';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':townName', $townName);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	print 'result:' . $result['buildings'];
	print 'buildName' . $buildingName;
	//$result is the buildings string from the current town
	//Defence.1:Outer Wall.180:Inner Wall.100:Wall Upgrade 1.0:Wooden Support.0:Supply.1:Water Reserve.29
	if (strpos($result['buildings'], $buildingName) == false)
	{
		//building name can't be found
		print 'its TRUE';
		return true;
	}
	else 
	{
		$stringIndex = strpos($result['buildings'], $buildingName);
		$lengthBeforeCount = strlen($buildingName) + 1 + $stringIndex;
		$firstDigit = substr ($result['buildings'], $lengthBeforeCount, 1); //first digit of the invested AP for current building (works because it's always 1-9 if number is > 0)
		if ($firstDigit == 0)
		{
			print 'its true';
			return true;
		}
		else
		{
			print 'its falsere';
			return false;
		}
	}
	
	
	
}

function doesNewStringContain($arg1)
{
	global $newItemsString;
	
	if (!isset($newItemsString) || $newItemsString == NULL)
	{return false;}
	
	$searchingFor = $arg1 . '.';
	$searchingFor2 = ',' . $arg1 . '.';
	$search1 = strpos($newItemsString, $searchingFor);
	$search2 = strpos($newItemsString, $searchingFor2);
	
	//if the item is the first item in the string ... USES === operator so this statement doesn't continue if the item isn't found
	if ($search1 === 0)
	{
		return true;
	}
	//else; set the searching string to make sure it only finds full index, so 10 doesn't return true for item #0
	else
	{
		if ($search2 == false || $search2 == NULL)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}

function warehouseContains($arg1)
{
	global $dbCon;
	global $townName;
	
	$query = 'SELECT * FROM `' . $townName . '` WHERE `x` = 0 AND `y` = 0';
	$statement = $dbCon->prepare($query);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();
	
	$functionItems = $result['groundItems'];
	$searchingFor = $arg1 . '.';
	$searchingFor2 = ',' . $arg1 . '.';
	$search1 = strpos($functionItems, $searchingFor);
	$search2 = strpos($functionItems, $searchingFor2);
	
	//if the item is the first item in the string ... USES === operator so this statement doesn't continue if the item isn't found
	if ($search1 === 0)
	{
		return true;
	}
	//else; set the searching string to make sure it only finds full index, so 10 doesn't return true for item #0
	else
	{
		if ($search2 == false || $search2 == NULL)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	//0.5,5.31,6.13,3.24
	//0.5  5.31   6.13   3.24
	/*$functionItemsArray = explode(',', $result['groundItems']);
	for ($i = 0; $i < sizeOf($functionItemsArray); $i++)
	{
		$functionItemSplit = explode('.', $functionItemsArray[$i]);
		$functionItemId = $functionItemSplit[0];
		$functionItemAmount = $functionItemSplit[1];
		
		if ($functionItemId == $arg1)
		{
			return true;
		}		
	}*/
}

function buildingRequires($itemId)
{
	global $itemRequirementsArray;
	for ($i = 0; $i < sizeOf($itemRequirementsArray); $i++)
	{
		$currentSplit = explode('.', $itemRequirementsArray[$i]);
		$currentId = $currentSplit[0];
		$currentAmount = $currentSplit[1];
		if ($currentId == $itemId)
		{
			return true;
		}
	}
	return false;
}


//following for loop increases the AP built on the corresponding buildingName
//if apToBuild > AP the Char has, dont follow through, instead set header with error_get_last
if ($apToAdd > $currentAp || $apToAdd == 0)
{
	header ('location: ../inTown/?locat=construction&e=You%20do%20not%20have%20enough%20AP!');
}
else if ($apToAdd > $apRequired)
{
	header ('location: ../inTown/?locat=construction&e=This%20Structure%20does%20not%20require%20so%20much%20AP!!');
}
else 
{
	if (isFirstBuild())
	{
		$newItemsString = NULL;
		$warehouseItems = getWarehouseItems($townName);
		$warehouseItemsArray = explode(',', $warehouseItems);
		//Determine the required Items by ID and amount, store them in an array called itemRequirementsArray
		for ($i = 0; $i < sizeOf($buildingsInfo); $i++)
		{
			if ($buildingsInfo[$i][0] == $buildingName)
			{
				$itemRequirements = $buildingsInfo[$i][4];
			}
		}
		$itemRequirementsArray = explode(':', $itemRequirements);
		$requiredItems = sizeOf($itemRequirementsArray);
		
		for ($i = 0; $i < sizeOf($itemRequirementsArray); $i++)
		{
			$currentRequirementSplit = explode('.', $itemRequirementsArray[$i]);
			$currentRequiredItemId = $currentRequirementSplit[0];
			$currentRequiredItemAmount = $currentRequirementSplit[1];
			
			//check if item has a stack in the warehouse
			if (warehouseContains($currentRequiredItemId) == false)
			{
				$itemsFound = false;
				header ('location: ../inTown/?locat=construction&e=Sufficient%20items%20no%20longer%20exist! NOSTACK');
				exit();
			}
			/*else
			{
				if (!isset($itemsFound))
				{
					$itemsFound = true;
				}
			}*/
		}
		
		$itemRequirementsArray = explode(':', $itemRequirements);
		$requiredItems = sizeOf($itemRequirementsArray);
		for ($i1 = 0; $i1 < sizeOf($itemRequirementsArray); $i1++)
		{
			$currentRequirementSplit = explode('.', $itemRequirementsArray[$i1]);
			$currentRequiredItemId = $currentRequirementSplit[0];
			$currentRequiredItemAmount = $currentRequirementSplit[1];
			
			//remove items from warehouse. If quantities no longer suffice, return with an error message
			for ($i2 = 0; $i2 < sizeOf($warehouseItemsArray); $i2++)
			{
				$currentWarehouseSplit = explode('.', $warehouseItemsArray[$i2]);
				$currentItemId = $currentWarehouseSplit[0];
				$currentItemAmount = $currentWarehouseSplit[1];
				$potentialItemAmount = $currentItemAmount - $currentRequiredItemAmount;

				if ($currentItemId == $currentRequiredItemId)
				{
					if ($potentialItemAmount < 0)
					{
						$itemsFound = false;
						header ('location: ../inTown/?locat=construction&e=Sufficient%20items%20no%20longer%20exist! ABCD');
						exit();
					}
					else if ($potentialItemAmount == 0)
					{
						
					}
					else
					{
						echo $currentItemId . '.' . $potentialItemAmount;
						if (!isset($newItemsString) || $newItemsString == NULL)
						{
							$newItemsString = $currentItemId . '.' . $potentialItemAmount;
							
						}
						else
						{
							$newItemsString = $newItemsString . ',' . $currentItemId . '.' . $potentialItemAmount;
							
						}
					}
				}
				else if (buildingRequires($currentItemId))
				{
					//do nothing, item will be added to the string when it is time to search for it
				}
				else if (doesNewStringContain($currentItemId) == false)//PREVENT STRING-TYPE ARRAY FROM DUPLICATING ALL ITEMS
				{
					if (!isset($newItemsString) || $newItemsString == NULL)
					{
						$newItemsString = $currentItemId . '.' . $currentItemAmount;
						
					}
					else
					{
						$newItemsString = $newItemsString . ',' . $currentItemId . '.' . $currentItemAmount;
						
					}
				}
				
				/*if ($i2 == (sizeOf($warehouseItemsArray) - 1) && ($itemsFound == false)) //if item isn't found by the last warehouse item, item is not in bank.
				{
					$itemsFound = false;
					header ('location: ../inTown/?locat=construction&e=Sufficient%20items%20no%20longer%20exist! EFGH');
					exit();
				}
				else if ($i2 == (sizeOf($warehouseItemsArray) - 1) && !($i == (sizeOf($itemRequirementsArray) - 1)))
				{
					$itemsFound = false;
					header ('location: ../inTown/?locat=construction&e=Sufficient%20items%20no%20longer%20exist! EFGHIJKL');
					exit();
				}*/
			}			
		}
			
			if (isset($itemsFound) && $itemsFound != false || !isset($itemsFound))
			{
			//Update the grounditems in the DB
			$query4 = 'UPDATE `' . $townName . '` SET `groundItems` = :newItemsString WHERE `x` = 0 AND `y` = 0';
			$statement4 = $dbCon->prepare($query4);
			$statement4->bindValue(':newItemsString', $newItemsString);
			$statement4->execute();
			$statement4->closeCursor();
			}
	}
	
	if (isset($itemsFound) && $itemsFound != false || !isset($itemsFound))
	{
		//Increase the invested AP
		for ($i = 0; $i < sizeOf($buildingsArray); $i++)
		{
			$currentBuildingSplit = explode('.', $buildingsArray[$i]);
			$currentBuildingName = $currentBuildingSplit[0];
			$currentBuildingAp = $currentBuildingSplit[1];
			$potentialBuildingAp = $currentBuildingAp + $apToAdd;
	
			if ($currentBuildingName == $buildingName)
			{
				if (!isset($newBuildingsString))
				{
					$newBuildingsString = $currentBuildingName . '.' . $potentialBuildingAp;
				}
				else
				{
					$newBuildingsString = $newBuildingsString . ':' . $currentBuildingName . '.' . $potentialBuildingAp;	
				}
			}
			else
			{
				if (!isset($newBuildingsString))
				{
					$newBuildingsString = $currentBuildingName . '.' . $currentBuildingAp;
				}
				else
				{
					$newBuildingsString = $newBuildingsString . ':' . $currentBuildingName . '.' . $currentBuildingAp;	
				}
			}
		}

		$query = 'UPDATE `towns` SET `buildings` = :newBuildingsString WHERE `townName` = :townName';
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':newBuildingsString', $newBuildingsString);
		$statement->bindValue(':townName', $townName);
		$statement->execute();
		$statement->closeCursor();

		//decrease the AP the character has
		$remainingCharAp = $currentAp - $apToAdd;
		
		$query2 = 'UPDATE `characters` SET `currentAP` = :newCharAp WHERE `character` = :char AND `username` = :user';
		$statement2 = $dbCon->prepare($query2);
		$statement2->bindValue(':newCharAp', $remainingCharAp);
		$statement2->bindValue(':char', $char);
		$statement2->bindValue(':user', $user);
		$statement2->execute();
		$statement2->closeCursor();
		
		
		
		//Return to the construction page
		header('location: ../inTown/?locat=construction');
	}
}

?>