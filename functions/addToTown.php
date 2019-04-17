<?php
require_once ('../connect.php');
require_once ('../functions/verifyLogin.php');
require_once ("../model/database.php");

$dbCon = Database::getDB();

//multiJoin is an array that holds each selected character for joining the town
$multiJoin = $_SESSION["multijoin"];


//Check again to ensure that every character selected is not in a town before adding them to the new town


$newTown = filter_input(INPUT_POST, 'newTown');
foreach($multiJoin as $characterObject){
	$characterObject = unserialize($characterObject);
	$charId = $characterObject["id"];
	$charName = $characterObject["character"];
	$playerName = $characterObject["username"];
	$defaultItems = NULL;

	//Check if this character is in a town
	$query = "SELECT * FROM `characters` WHERE `id` = :id";
	$statement = $dbCon->prepare($query);
	$statement->bindValue(":id", $charId);
	$statement->execute();
	$result = $statement->fetch();
	$statement->closeCursor();

	if($result["townName"] == "none"){
		//Char still has not joined a town
		$query = "UPDATE `characters` SET `townName` = :newTown, `items` = :items, `itemsMass` = '0', `currentAP` = :maxAp, `status` = '3.7.11' WHERE `character` = :char AND `username` = :user";
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':char', $charName);
		$statement->bindValue(':items', $defaultItems);
		$statement->bindValue(':user', $playerName);
		$statement->bindValue(':newTown', $newTown);
		$statement->bindValue(':maxAp', $result["maxAP"]);
		$statement->execute();
		$statement->closeCursor();

		//Add the Action of the User Joining, to the bulletin board for the town they are joining
		$content = $charName . " Has Joined the Town!";
		Towns::addTownBulletin($content, $newTown);


		//Modify citizen amounts in database
		$query = 'SELECT amountResidents, maxResidents FROM `towns` WHERE `townName` = :townName';
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':townName', $newTown);
		$statement->execute();
		$result = $statement->fetch();
		$statement->closeCursor();

		$maxAmount = $result['maxResidents'];
		$newAmount = $result['amountResidents'] + 1;

		$query = 'UPDATE `towns` SET `amountResidents` = :newAmount WHERE `townName` = :townName';
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':newAmount', $newAmount);
		$statement->bindValue(':townName', $newTown);
		$statement->execute();
		$statement->closeCursor();

		if ($newAmount >= $maxAmount){
			$query = 'UPDATE `towns` SET `townFull` = "1" WHERE `townName` = :townName';
			$statement = $dbCon->prepare($query);
			$statement->bindValue(':townName', $newTown);
			$statement->execute();
			$statement->closeCursor();
		}
				
		//Add Character's name to the 0,0 co-ordinate for map
		//Also set X and Y sessions to 0
		$_SESSION['x'] = 0;
		$_SESSION['y'] = 0;
		
		$query = "SELECT * FROM " . $newTown . " WHERE `x` = '0' AND `y` = '0'";
		$statement = $dbCon->prepare($query);
		$statement->execute();
		$result = $statement->fetch();
		$statement->closeCursor();

		$characters = $result['charactersHere'];
		if ($characters == '' || $characters == NULL){
			$charactersUpdated = $charName;
		}
		else{
			$charactersUpdated = $characters . '.' . $charName;
		}
		
		$query = "UPDATE " . $newTown . " SET `charactersHere` = :newChars WHERE `x` = '0' AND `y` = '0'";
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':newChars', $charactersUpdated);
		$statement->execute();
		$statement->closeCursor();
		
		$query = "UPDATE `towns` SET `amountResidents` = :newAmount WHERE `townName` = :newTown";
		$statement = $dbCon->prepare($query);
		$statement->bindValue(':newAmount', $newAmount);
		$statement->bindValue(':newTown', $newTown);
		$statement->execute();
		$statement->closeCursor();

		if ($newAmount >= $maxAmount){
			break;
		}
	}
}
echo '<script>window.location = "' . $root . '/inTown/?locat=inTown";</script>';

// $_SESSION['char'] = filter_input(INPUT_POST, 'char');
// $charName = $_SESSION['char'];
// $playerName = $_SESSION['login'];
// $defaultItems = NULL;

// $query = 'UPDATE `characters` SET `townName` = :newTown, `items` = :items, `itemsMass` = "0", `currentAP` = "16", `status` = "3.7.11" WHERE `character` = :char AND `username` = :user';
// $statement = $dbCon->prepare($query);
// $statement->bindValue(':char', $charName);
// $statement->bindValue(':items', $defaultItems);
// $statement->bindValue(':user', $playerName);
// $statement->bindValue(':newTown', $newTown);
// $statement->execute();
// $statement->closeCursor();


// //Add the Action of the User Joining, to the bulletin board for the town they are joining
// $query = 'SELECT bulletin FROM `towns` WHERE `townName` = :townName';
// $statement = $dbCon->prepare($query);
// $statement->bindValue(':townName', $newTown);
// $statement->execute();
// $result = $statement->fetch();
// $statement->closeCursor();

// $oldBulletin = $result['bulletin'];
// $newBulletin = $oldBulletin . '.' . $charName . ' Has Joined The Town!';

// $query = 'UPDATE `towns` SET `bulletin` = :newBulletin WHERE `townName` = :townName';
// $statement = $dbCon->prepare($query);
// $statement->bindValue(':newBulletin', $newBulletin);
// $statement->bindValue(':townName', $newTown);
// $statement->execute();
// $statement->closeCursor();

// 	//Add To amount of town slots which are occupied
// 	//if town becomes full, tell database to change townFull Boolean to '1'
	
// $query = 'SELECT amountResidents, maxResidents FROM `towns` WHERE `townName` = :townName';
// $statement = $dbCon->prepare($query);
// $statement->bindValue(':townName', $newTown);
// $statement->execute();
// $result = $statement->fetch();
// $statement->closeCursor();

// $maxAmount = $result['maxResidents'];
// $newAmount = $result['amountResidents'] + 1;

// $query = 'UPDATE `towns` SET `amountResidents` = :newAmount WHERE `townName` = :townName';
// $statement = $dbCon->prepare($query);
// $statement->bindValue(':newAmount', $newAmount);
// $statement->bindValue(':townName', $newTown);
// $statement->execute();
// $statement->closeCursor();

// if ($newAmount >= $maxAmount)
// {
// 	$query = 'UPDATE `towns` SET `townFull` = "1" WHERE `townName` = :townName';
// 	$statement = $dbCon->prepare($query);
// 	$statement->bindValue(':townName', $newTown);
// 	$statement->execute();
// 	$statement->closeCursor();
// }
		
// 	//Add Character's name to the 0,0 co-ordinate for map
// 	//Also set X and Y sessions to 0
// 	$_SESSION['x'] = 0;
// 	$_SESSION['y'] = 0;
	
// 	$query = "SELECT * FROM " . $newTown . " WHERE `x` = '0' AND `y` = '0'";
// 	$statement = $dbCon->prepare($query);
// 	$statement->execute();
// 	$result = $statement->fetch();
// 	$statement->closeCursor();

/////







// 	$characters = $result['charactersHere'];
// 	if ($characters == '' || $characters == NULL)
// 	{
// 		$charactersUpdated = $charName;
// 	}
// 	else
// 	{
// 		$charactersUpdated = $characters . '.' . $charName;
// 	}
	
// 	$query = "UPDATE " . $newTown . " SET `charactersHere` = :newChars WHERE `x` = '0' AND `y` = '0'";
// 	$statement = $dbCon->prepare($query);
// 	$statement->bindValue(':newChars', $charactersUpdated);
// 	$statement->execute();
// 	$statement->closeCursor();
	
// 	$query = "UPDATE `towns` SET `amountResidents` = :newAmount WHERE `townName` = :newTown";
// 	$statement = $dbCon->prepare($query);
// 	$statement->bindValue(':newAmount', $newAmount);
// 	$statement->bindValue(':newTown', $newTown);
// 	$statement->execute();
// 	$statement->closeCursor();
// 	echo '<script>window.location = "' . $root . '/inTown/?locat=inTown";</script>';
