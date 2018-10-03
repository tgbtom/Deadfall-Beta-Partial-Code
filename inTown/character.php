<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");
Include("../data/buildings.php");
Include("../data/items.php");
require_once ("../functions/queryFunctions.php");

$errorMessage = FILTER_INPUT(INPUT_GET, 'e');
if (isset($errorMessage))
{
	echo "<script type='text/javascript'>alert('$errorMessage');</script>";
}


//All information here is retrieved from database simply using the login session and character session
$playerName = $_SESSION['login'];
$charName = $_SESSION['char'];

//Set Variables which correspond with the character that is in session (town name, level, class, etc.)
$charDetails = getCharDetails();
$townName = $charDetails['townName'];
$charLevel = $charDetails['level'];
$charClass = $charDetails['class'];

$townDetails = getTownDetails($townName);
$previousReady = $townDetails['readyResidents'];
$maxReady = $townDetails['maxResidents'];
$dayNumber = $townDetails['dayNumber'];
$deadRes = $townDetails['deadResidents'];

	
function endDay()
{
		global $dbCon;
		global $previousReady;
		global $maxReady;
		global $dayNumber;
		global $townName;
		global $deadRes;
		
		$newReady = $previousReady + 1;
		$newDead = $deadRes;
		
		if ($newReady >= ($maxReady - $deadRes))
		{
			//Remove '10' from all characters status' if they are in the town.
			$query = 'SELECT * FROM `characters` WHERE `townName` = :townName';
			$statement = $dbCon->prepare($query);
			$statement->bindValue(':townName', $townName);
			$statement->execute();
			$result = $statement->fetchAll();
			$statement->closeCursor();
			
			foreach ($result as $current)
			{
				$character = $current['character'];
				
				if(!doesStatusContainExt(12, $character)) //If character is not dead, reset ATE/DRANK/DAY ENDED
				{
					replaceStatusExt(10, 11, $character);
					//Remove status for ATE/DRANK
					replaceStatusExt(0, NULL, $character);
					replaceStatusExt(1, NULL, $character);
				}
				if ($dayNumber % 2 == 0) //If day is an even number, change hunger 
				{
					if (doesStatusContainExt(2, $character)) //character WAS FULL
					{
						replaceStatusExt(2, 3, $character);
					}
					elseif (doesStatusContainExt(3, $character)) //character WAS HUNGRY
					{
						replaceStatusExt(3, 4, $character);
					}
					elseif (doesStatusContainExt(4, $character)) //character WAS VERY HUNGRY
					{
						replaceStatusExt(4, 5, $character);
					}
					elseif (doesStatusContainExt(5, $character)) //character WAS STARVING
					{
						//***************************CHARACTER DIES HERE*****************************************
						if (!doesStatusContainExt(12, $character))
						{
							$newDead = $newDead + 1;
						
							$query = 'UPDATE `towns` SET `deadResidents` = :newDead WHERE `townName` = :townName';
							$statement = $dbCon->prepare($query);
							$statement->bindValue(':townName', $townName);
							$statement->bindValue(':newDead', $newDead);
							$statement->execute();
							$statement->closeCursor();
						
							addStatusExt(12, $character);
							replaceStatusExt(11, 10, $character);
							
							dropAllItemsExt($character);
							//NEED TO: Check if any chars are left alive in this town, otherwise needs to end town
							if (charsAlive($townName) == 0)
							{
								//All characters are dead
								closeTheTown($townName);
							}
						}
					}
				}
				
				if (doesStatusContainExt(6, $character)) //character WAS QUENCHED
				{
					replaceStatusExt(6, 7, $character);
				}
				elseif (doesStatusContainExt(7, $character)) //character WAS THIRSTY
				{
					replaceStatusExt(7, 8, $character);
				}
				elseif (doesStatusContainExt(8, $character)) //character WAS VERY THIRSTY
				{
					replaceStatusExt(8, 9, $character);
				}
				elseif (doesStatusContainExt(9, $character)) //character WAS DEHYDRATED
				{
					//***************************CHARACTER DIES HERE*****************************************
					if (!doesStatusContainExt(12, $character))
					{
						$newDead = $newDead + $deadRes + 1;
					
						$query = 'UPDATE `towns` SET `deadResidents` = :newDead WHERE `townName` = :townName';
						$statement = $dbCon->prepare($query);
						$statement->bindValue(':townName', $townName);
						$statement->bindValue(':newDead', $newDead);
						$statement->execute();
						$statement->closeCursor();
						
						$newDead++; //Increase count so if more than one character died this night, it updates the dead count accordingly.
					
						addStatusExt(12, $character);
						replaceStatusExt(11, 10, $character);						
						
						dropAllItemsExt($character);
						//Checks if anyone is still alive
						if (charsAlive($townName) == 0)
						{
							//All characters are dead
							closeTheTown($townName);
						}					
					}
				}
					
			}
			
			//finish day, increase day number
			$dayNumber++;
			if ($dayNumber >= 5)
			{
				zedSpread($townName);
			}
			
			//increase hordesize
			$newHorde = getHordeSize($townName);
			
			$query2 = 'UPDATE `towns` SET `readyResidents` = :newReady, `dayNumber` = :dayNumber, `hordeSize` = :newHorde WHERE `townName` = :townName';
			$statement2 = $dbCon->prepare($query2);
			$statement2->bindValue(':newReady', 0);
			$statement2->bindValue(':dayNumber', $dayNumber);
			$statement2->bindValue(':newHorde', $newHorde);
			$statement2->bindValue(':townName', $townName);
			$statement2->execute();
			$statement2->closeCursor();
			
			//Check for any structures that perform an action over night
			if (isStructureBuilt('Water Reserve', $townName))
			{
				for ($i = 0; $i < 2; $i++)
				{
					addToBank(0, $townName);
				}
			}			
			
			if (isStructureBuilt('Vegetable Garden', $townName))
			{
				for ($i = 0; $i < 2; $i++)
				{
					addToBank(1, $townName);
				}
			}
			
		}
		else
		{
			$query2 = 'UPDATE `towns` SET `readyResidents` = :newReady WHERE `townName` = :townName';
			$statement2 = $dbCon->prepare($query2);
			$statement2->bindValue(':newReady', $newReady);
			$statement2->bindValue(':townName', $townName);
			$statement2->execute();
			$statement2->closeCursor();
		}
	
}


$endDay = filter_input(INPUT_POST, 'endDay');
if (isset($endDay))
{
	if ($endDay == 'end')
	{
		//end the day
		if (doesStatusContain(10))
		{
			//Char has already ended the day --> does nothing
		}
		else
		{
			replaceStatus(11, 10);
			endDay();
		}
	}
}

?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="mainDesignTown.css">	
</head>

<body>

<div class="Container">

	<?php include("../universal/header.php"); ?>
	
	
	<!-- PHP draws level requirements, and checks database for current stats of character -->
	<?php 
	include ("../data/levelReq.php"); 
	$Query3 = "SELECT * FROM `characters` WHERE `username` = '$playerName' AND `Character` = '$charName'";
	$Query4 = mysqli_query($con, $Query3);
	while ($row = mysqli_fetch_assoc($Query4))
	{
			$currentLevel = $row['level'];
			$nextLevel = $currentLevel + 1;
			$currentXp = $row['experience'];
			$neededXp = $xpReq [0];
			
			for ($level = 1; $level < $nextLevel; $level++)
			{
			$neededXp = $neededXp * $xpReq[1];
			}

		
			echo "
			<div class='centralBox'>
			<p style='float:left;'>Level " . $currentLevel . "</p> <p style='float:right;'>Level " . $nextLevel . "</p>
			<progress value='" . $currentXp . "' max='" . $neededXp . "' style='width:100%;color:light-blue;'></progress>
			<p style='float:left;'>" . $currentXp . " exp</p> <p style='float:right;'>" . $neededXp . " exp</p>
			</div>";
	}
	?>
	
	<div class='centralBox'>
	<form action='.?locat=character' method='post' name='end'>
	<input hidden value='end' name='endDay'>
	<input type='button' id='endButton' onclick='verify()' value='End Day'>
	</form>
	</div>


	<script type="text/javascript">
	function verify()
	{
		if (confirm('You are done using this character for the in-game day?'))
		{
			document.end.submit();
		}
	}
	
	function dayAlreadyEnded()
	{
		endButton.setAttribute('disabled', 'disabled');
	}

	</script>
	<?php 
	if (doesStatusContain(10))
		{
			//Char has already ended the day
			echo '<script>dayAlreadyEnded();</script>';
		}
	
	?>

		
	
</div>
	<?php
	Include ("../universal/hyperlinks.php");
	?>
</body>

</html>