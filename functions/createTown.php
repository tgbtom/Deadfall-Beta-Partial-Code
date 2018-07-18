<html>
<?php

require_once('../connect.php');
require_once('../functions/verifyLogin.php');

$newTown = filter_input(INPUT_POST, 'newTown');
if (isset($newTown) && $newTown != NULL && $newTown != '')
{
	createSettlement($newTown, 11);
}

//CREATE A TABLE FOR THE TOWN


//This will loop through to create an 11x11 map within the database (121 blocks including the town)
function createSettlement($settlementName, $mapSize)
{
	global $newTown;
	global $dbCon;
	
	$hostname="127.0.0.1"; 
	$username="root";
	$password=""; 
	$database2="deadfalldb";  //load database for towns
	$con2=mysqli_connect($hostname,$username,$password, $database2);

	if(! $con2)
	{
		die('Connection Failed'.mysql_error());
		print('Couldnt Connect');
	}
	
	$sql = "CREATE TABLE `" . $newTown . "` (
	`id` int(11) NOT NULL,
	`x` int(11) NOT NULL,
	`y` int(11) NOT NULL,
	`specialStructure` text NOT NULL,
	`lootability` int(11) NOT NULL DEFAULT '10',
	`groundItems` text,
	`zeds` int(11) NOT NULL,
	`lastKnownZed` int(11) NOT NULL,
	`charactersHere` text NOT NULL,
	`bulletin` varchar(4000) DEFAULT 'Zone was created!.This is a test for the bulletin',
	PRIMARY KEY (`id`)
	)";

	if (mysqli_query($con2, $sql)) 
	{
		echo "Table: " . $newTown . " created successfully";
	} 
	else 
	{
		echo "Error creating table: " . mysqli_error($con2);
	}

	$defaultBuildingsString = "Defence.1:Outer Wall.0:Inner Wall.0:Wall Upgrade 1.0:Wooden Support.0:Supply.1:Water Reserve.0";
	
	//add details of town to `towns` table
	$query = 'INSERT INTO `towns` 
	(`townName`, `amountResidents`, `maxResidents`, `readyResidents`, `deadResidents`, `townFull`, `buildings`, `bulletin`, `hordeSize`, `defenceSize`, `dayNumber`)
	VALUES
	(:newTown, "0", "10", "0", "0", "0", :buildings, :bulletin, "300", "350", "1")';
	$statement = $dbCon->prepare($query);
	$statement->bindValue(':newTown', $newTown);
	$statement->bindValue(':bulletin', $newTown . ' has been created!');
	$statement->bindValue(':buildings', $defaultBuildingsString);
	$statement->execute();
	$statement->closeCursor();
	
	$x = -4;
	$y = 5;
	$zedSpawn = rand(5,10);
	//groundItems default is -1, representing no particular item
	//OLD  $compilation = "INSERT INTO `" . $settlementName . "` (`id`, `coords`, `specialStructure`, `lootability`, `groundItems`, `zeds`, `lastKnownZed`, `charactersHere`) VALUES ('0', '-5,5', '', '10', '-1', '$zedSpawn', '0', '')";
	$compilation = "INSERT INTO `" . $settlementName . "` (`id`, `x`, `y`, `specialStructure`, `lootability`, `groundItems`, `zeds`, `lastKnownZed`, `charactersHere`, `bulletin`) VALUES ('0', '-5', '5', '', '10', NULL, '$zedSpawn', '0', '', NULL)";

	$loopAmt = $mapSize * $mapSize;
	for ($index = 1; $index < $loopAmt; $index++) 		
	{
		$distance = abs($x) + abs($y);
		if ($distance == 1)
		{
			$zedSpawn = 0;
		}
		else if ($distance == 2)
		{
			$zedSpawn = rand(0,1);
		}
		else if ($distance >= 3 && $distance <= 5)
		{
			$zedSpawn = rand(0,3);
		}
		else if ($distance >= 6 && $distance <= 7)
		{
			$zedSpawn = rand(1,5);
		}
		else if ($distance >= 8 && $distance <= 9)
		{
			$zedSpawn = rand(3,6);
		}
		else if ($distance >= 10)
		{
			$zedSpawn = rand(5,10);
		}
		
		if ($x == 0 && $y == 0)
		{
			//If statement check skips ahead (-1) because otherwise it writes 0,0 then skips 1,0
			$compilation .= ", ('$index', '$x', '$y', '', '10', '0.35,1.15', '0', '0', '', NULL)";
			$x++;
			$zedSpawn = 0;
		}
		else if ($x == 5)
		{
			$compilation .= ", ('$index', '$x', '$y', '', '10', NULL, '$zedSpawn', '0', '', NULL)";
			$y--;
			$x = -5;
			$zedSpawn = 0;
		}
		else if ($index == $loopAmt - 1)
		{
			$compilation .= ", ('$index', '$x', '$y', '', '10', NULL, '$zedSpawn', '0', '', NULL);";
			$x++;
			$zedSpawn = 0;
		}
		else
		{
			$compilation .= ", ('$index', '$x', '$y', '', '10', NULL, '$zedSpawn', '0', '', NULL)";
			$x++;
			$zedSpawn = 0;
		}
	
	}
	if ($con2->query($compilation) === TRUE) 
	{
    echo "New record created successfully";
	header ("location: ../inTown/?locat=join&tempChar=" . $_SESSION['char']);
	}
	//mysql_query($con2, $compilation);
	
}
//INSERT INTO `guelph` (`coords`, `specialStructure`, `lootability`, `groundItems`, `zeds`) VALUES ('-3,1', '', '10', '', '0'), ('-2,1', '', '10', '', '0');
?>
</html>