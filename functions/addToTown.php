<?php
require_once ('../connect.php');
require_once ('../functions/verifyLogin.php');
require_once ("../model/database.php");

$dbCon = Database::getDB();

//multiJoin is an array that holds each selected character for joining the town
$multiJoin = $_SESSION["multijoin"];


//Check again to ensure that every character selected is not in a town before adding them to the new town

//newTown will be set to the 'town_id'
$newTown = filter_input(INPUT_POST, 'newTown');
foreach($multiJoin as $characterObject){
	$characterObject = unserialize($characterObject);
	$charId = $characterObject["id"];
	$charName = $characterObject["character"];
	$playerName = $characterObject["username"];
	$defaultItems = NULL;
	$characterClassObject = new Character($charId);
	$charClass = $characterClassObject->class;

	//Check if this character is in a town
	$query = "SELECT * FROM `characters` WHERE `id` = :id";
	$statement = $dbCon->prepare($query);
	$statement->bindValue(":id", $charId);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();

	if($result["town_id"] == null){
		//Char still has not joined a town
		$query = "UPDATE `characters` SET `town_id` = :newTown, `items` = :items, `itemsMass` = '0', `currentAP` = :maxAp, `status` = '3.7.11' WHERE `id` = :id AND `username` = :user";
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':id', $charId);
		$statement->bindValue(':items', $defaultItems);
		$statement->bindValue(':user', $playerName);
		$statement->bindValue(':newTown', $newTown);
		$statement->bindValue(':maxAp', $result["maxAP"]);
		$statement->execute();
		$statement->closeCursor();

		//Add the Action of the User Joining, to the bulletin board for the town they are joining
		$content = "<img src='%2e%2e/images/icons/" . lcfirst($charClass) . "%2epng'><blue>" . $charName . "</blue> Has Joined the Town!";
		Towns::addTownBulletin($content, $newTown);

		$townName = Towns::getTownNameById($newTown);
		$townTableName = Towns::getTownTableName($newTown);


		//Modify citizen amounts in database
		$query = 'SELECT amountResidents, maxResidents FROM `towns` WHERE `town_id` = :townId';
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':townId', $newTown);
		$statement->execute();
		$result = $statement->fetch();
		$statement->closeCursor();

		$maxAmount = $result['maxResidents'];
		$newAmount = $result['amountResidents'] + 1;

		$query = 'UPDATE `towns` SET `amountResidents` = :newAmount WHERE `town_id` = :townId';
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':newAmount', $newAmount);
		$statement->bindValue(':townId', $newTown);
		$statement->execute();
		$statement->closeCursor();

		if ($newAmount >= $maxAmount){
			$query = 'UPDATE `towns` SET `townFull` = "1" WHERE `town_id` = :townId';
			$statement = $dbCon->prepare($query);
			$statement->bindValue(':townId', $newTown);
			$statement->execute();
			$statement->closeCursor();
		}
				
		//Add Character's name to the 0,0 co-ordinate for map
		//Also set X and Y sessions to 0
		$_SESSION['x'] = 0;
		$_SESSION['y'] = 0;
		
		$query = "SELECT * FROM " . $townTableName . " WHERE `x` = '0' AND `y` = '0'";
		$statement = $dbCon->prepare($query);
		$statement->execute();
		$result = $statement->fetch();
		$statement->closeCursor();

		$characters = $result['charactersHere'];
		if ($characters == '' || $characters == NULL){
			$charactersUpdated = $charId;
		}
		else{
			$charactersUpdated = $characters . '.' . $charId;
		}
		
		$query = "UPDATE " . $townTableName . " SET `charactersHere` = :newChars WHERE `x` = '0' AND `y` = '0'";
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':newChars', $charactersUpdated);
		$statement->execute();
		$statement->closeCursor();
		
		$query = "UPDATE `towns` SET `amountResidents` = :newAmount WHERE `town_id` = :townId";
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':newAmount', $newAmount);
		$statement->bindValue(':townId', $newTown);
		$statement->execute();
		$statement->closeCursor();

		if ($newAmount >= $maxAmount){
			break;
		}
	}
}
echo '<script>window.location = "' . $root . '/inTown/?locat=inTown";</script>';