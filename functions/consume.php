<?php 
require_once ("../connect.php");
require_once ("./verifyLogin.php");
require_once ("../data/items.php");
require_once ("../functions/queryFunctions.php");
require_once ("../model/weapons.php");

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

//function to find the item ID By NAME


	
	for ($i = 0; $i < sizeOf($itemsMaster); $i++)
	{
		if ($itemName == $itemsMaster[$i][0])
		{
			$itemId = $i;
			$itemWeight = $itemsMaster[$i][2];
			$itemFunction = checkUsability($itemId);
		}
	}
	
//Check if Character is still holding the item being dropped
$query1 = 'SELECT * FROM `characters` WHERE `username` = :username AND `id` = :id';
$statement1 = $dbCon->prepare($query1);
$statement1->bindValue(':username', $user);
$statement1->bindValue(':id', $charId);
$statement1->execute();
$result1 = $statement1->fetch();
$statement1->closeCursor();

$currentAp = $result1['currentAP'];
$maxAp = $result1['maxAP'];
$statusString = $result1['status'];
$statusArray = explode('.', $statusString);
$townId = $result1['town_id'];
$townName = Towns::getTownNameById($townId);
$townTableName = Towns::getTownTableName($townId);
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

function getApForItemId($itemId)
{
	global $itemsConsumable;
	for ($i = 0; $i < sizeOf($itemsConsumable); $i++)
	{
		if ($itemsConsumable[$i][0] == $itemId)
		{return $itemsConsumable[$i][2];}
	}
}

//if the item was found on the character, remove it (update character items)
if ($foundItem)	
{
	if ($itemFunction == 'Eat')
	{
		//Removes the item and decreases used weight capacity in the DB
		$query2 = 'UPDATE `characters` SET `items` = :newItems , `itemsMass` = :newMass WHERE `username` = :username AND `id` = :id';
		$statement2 = $dbCon->prepare($query2);
		$statement2->bindValue(':newItems', $newItems);
		$statement2->bindValue(':newMass', $newMass);
		$statement2->bindValue(':username', $user);
		$statement2->bindValue(':id', $charId);
		$statement2->execute();
		$statement2->closeCursor();
		
		//2,3,4,5 -> Full to Hungry
		if (doesStatusContain(3))
		{
			replaceStatus(3, 2);
		}
		else if (doesStatusContain(4))
		{
			replaceStatus(4, 3);
		}
		else if (doesStatusContain(5))
		{
			replaceStatus(5, 4);
		}

		if (!(doesStatusContain(0))) 
		{
			addStatus(0);
			$apToGain = getApForItemId($itemId)/100;
			if (($currentAp + ($apToGain * $maxAp)) > $maxAp)
			{$newAp = $maxAp;}
			else
			{$newAp = $currentAp + ($apToGain * $maxAp);}
	
			$query5 = 'UPDATE `characters` SET `currentAP` = :newAp WHERE `username` = :username AND `id` = :id';
			$statement5 = $dbCon->prepare($query5);
			$statement5->bindValue(':newAp', $newAp);
			$statement5->bindValue(':username', $user);
			$statement5->bindValue(':id', $charId);
			$statement5->execute();
			$statement5->closeCursor();
		
		}
		echo "<script>window.location.href='../inTown/?locat=inTown&e=You have Eaten Food.'</script>";
	}
	elseif ($itemFunction == 'Drink')
	{
		//Removes the item and decreases used weight capacity in the DB
		$query2 = 'UPDATE `characters` SET `items` = :newItems , `itemsMass` = :newMass WHERE `username` = :username AND `id` = :id';
		$statement2 = $dbCon->prepare($query2);
		$statement2->bindValue(':newItems', $newItems);
		$statement2->bindValue(':newMass', $newMass);
		$statement2->bindValue(':username', $user);
		$statement2->bindValue(':id', $charId);
		$statement2->execute();
		$statement2->closeCursor();
		
		//6,7,8,9 -> Quenched to Dehydrated
		if (doesStatusContain(6))
		{/*Character is already Quenched*/}
		else if (doesStatusContain(7))
		{
			replaceStatus(7, 6);
		}
		else if (doesStatusContain(8))
		{
			replaceStatus(8, 7);
		}
		else if (doesStatusContain(9))
		{
			replaceStatus(9, 8);
		}

		if (doesStatusContain(1))  //if character has drank today: only decrease thirst level
		{
			//Character has already DRANK TODAY
		}
		else  //else, if the character has NOT Drank today: Add 1 to his status array AND UPDATE AP accordingly
		{
			addStatus(1);
			$apToGain = getApForItemId($itemId)/100;
			if (($currentAp + ($apToGain * $maxAp)) > $maxAp)
			{$newAp = $maxAp;}
			else
			{$newAp = $currentAp + ($apToGain * $maxAp);}
	
			$query5 = 'UPDATE `characters` SET `currentAP` = :newAp WHERE `username` = :username AND `id` = :id';
			$statement5 = $dbCon->prepare($query5);
			$statement5->bindValue(':newAp', $newAp);
			$statement5->bindValue(':username', $user);
			$statement5->bindValue(':id', $charId);
			$statement5->execute();
			$statement5->closeCursor();
		
		}
		echo "<script>window.location.href='../inTown/?locat=inTown&e=You have consumed a drink.'</script>";
	}
	elseif ($itemFunction == 'Attack')
	{
		//If weapon	requires ammo, check if user has any
		for ($i = 0; $i < sizeOf($itemsWeapon); $i++)
		{
			if ($itemsWeapon[$i][0] == $itemId)
			{
				$ammoId = $itemsWeapon[$i][1];
				$apUse = $itemsWeapon[$i][2];
				$minKills = $itemsWeapon[$i][3];
				$maxKills = $itemsWeapon[$i][4];

				$weapon = new Weapon($itemsMaster[$itemId][0], $itemId, $itemsWeapon[$i][1], $itemsWeapon[$i][2], $itemsWeapon[$i][3], $itemsWeapon[$i][4], $itemsWeapon[$i][5], $itemsWeapon[$i][6], $itemsWeapon[$i][7], $itemsWeapon[$i][8]);
			}
		}
		
		if ($currentAp >= $apUse)
		{
			$query = 'SELECT zeds, bulletin FROM ' . $townTableName . ' WHERE `x` = :x AND `y` = :y';
			$statement = $dbCon->prepare($query);
			$statement->bindValue(':x', $x);
			$statement->bindValue(':y', $y);
			$statement->execute();
			$result = $statement->fetch();
			$statement->closeCursor();
				
			$zedCount = $result['zeds'];
			$oldBulletin = $result['bulletin'];
				
			if ($zedCount > 0)
			{
				$haveRequiredAmmo = false;
				//ammo type of -1 means no ammo is required
				if ($ammoId != -1)
				{
					if (characterIsHolding($ammoId, 1))
					{
						//Character has atleast 1 of the item
						removeItem($ammoId);
						$haveRequiredAmmo = true;

						//Roll for Ammo Output
						if($output = $weapon->rollForOutput()){
							pickUpItem($output, $itemsMaster[$output][2]);
						}
					}
				}
				else
				{
					$haveRequiredAmmo = true;
				}
		
				//if character had the ammo and it was removed...
				if ($haveRequiredAmmo)
				{
					//Consumes the required AP
					$newAp = $currentAp - $apUse;
					$query5 = 'UPDATE `characters` SET `currentAP` = :newAp WHERE `username` = :username AND `id` = :id';
					$statement5 = $dbCon->prepare($query5);
					$statement5->bindValue(':newAp', $newAp);
					$statement5->bindValue(':username', $user);
					$statement5->bindValue(':id', $charId);
					$statement5->execute();
					$statement5->closeCursor();
		
					//... Kill zombies based on weapon stats and perform other calculations regarding weapon statistics. Weapon Break not for first round beta
					$kills = ($zedCount > $minKills) ? mt_rand($minKills, min($maxKills, $zedCount)) : $zedCount;
					$newZeds = (($zedCount - $kills) < 0) ? 0 : ($zedCount - $kills);
					$addThisBulletin = '<red>' . $char . ' killed ' . $kills . ' zed(s) with ' . $itemName . '</red>';
					$newBulletin = ($oldBulletin == NULL) ? $addThisBulletin : $oldBulletin . '.' . $addThisBulletin;
					
					$query = 'UPDATE ' . $townTableName . ' SET `zeds` = :newZeds, `bulletin` = :newBulletin WHERE `x` = :x AND `y` = :y';
					$statement = $dbCon->prepare($query);
					$statement->bindValue(':x', $x);
					$statement->bindValue(':y', $y);
					$statement->bindValue(':newZeds', $newZeds);
					$statement->bindValue(':newBulletin', $newBulletin);
					$statement->execute();
					$statement->closeCursor();
					
					if($kills > 0){
						updateHordeSize($townId);
					}

					echo "<script>window.location.href='../inTown/?locat=outside'</script>";
				}	
			}
			else
			{
				echo "<script>window.location.href='../inTown/?locat=outside&e=There are no zeds here to kill!'</script>";
			}
			
		
			//UPDATE BULLETIN WHEN FIGHTING OCCURS
		}
		else
		{
			echo "<script>window.location.href='../inTown/?locat=outside&e=You do not have enough AP to Attack with this weapon.'</script>";
		}
	}
	elseif($itemFunction == "Load"){
		/** 
		 * 
		 * 
		 * 
		 * 
		 * 
		 * 
		 * Maintain inventory mass throughout
		 * 
		 * 
		 * 
		 * 
		 * 
		 * 
		 *  */

		$amountRemoved = 0;
		
		//Check inventory for ammo
		for($i = 0; $i < 3; $i++){
			if(characterIsHolding(22)){
				removeItem(22);
				$amountRemoved++;
			}
		}

		//If not enough ammo was found and removed, regain the ammo that WAS found and removed 
		if($amountRemoved < 3){
			//Not enough ammo was found/removed, re-add the ammo
			for($i = 0; $i < $amountRemoved; $i++){
				pickUpItem(22, 1);
				$amountRemoved = 0;
			}
			echo "<script>window.location.href='../inTown/?locat=outside&e=You do not have enough ammo to load the item.'</script>";
		}
		else{
			//Remove empty mag and Add Full mag to inventory
			removeItem(20);
			pickUpItem(21, 3);
			echo "<script>window.location.href='../inTown/?locat=inTown&e=You successfully loaded the item.'</script>";
		}

	}
	
}
?>