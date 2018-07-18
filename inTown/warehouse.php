<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");
require_once ("../data/items.php");

$errorMessage = FILTER_INPUT(INPUT_GET, 'e');
if ($_SESSION['x'] != 0 || $_SESSION['y'] != 0)
{
	$errorMessage .= 'Character is out of town!';
	header('location:./?locat=outside');
}
if (isset($errorMessage))
{
	echo "<script type='text/javascript'>alert('$errorMessage');</script>";
}
?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="mainDesignTown.css">
	<title>Warehouse</title>
<?php 

/*////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
WAREHOUSE ITEMS ARE STORED ON MAP AT CO_ORDS 0,0.
/////////////////////////////////////////////
///////////////////////////////////////////////
//////////////////////////////////////////////
////////////////////////////////////////////////*/


//All information here is retrieved from database simply using the login session and character session

$playerName = $_SESSION['login'];
$charName = $_SESSION['char'];

$query1 = 'SELECT * FROM `characters` WHERE `username` = :username AND `character = :character';
$statement1 = $dbCon->prepare($query1);
$statement1->bindValue(':username', $playerName);
$statement1->bindValue(':character', $charName);
$statement1->execute();
$result1 = $statement1->fetch();
$statement1->closeCursor();

$townName = $result1['townName'];
$charLevel = $result1['level'];
$charClass = $result1['class'];


//Set Variables which correspond with the character that is in session (town name, level, class, etc.)
$query1 = "SELECT * FROM `characters` WHERE `character` = '$charName' AND `username` = '$playerName'";
$query2 = mysqli_query($con, $query1);

	while ($row = mysqli_fetch_assoc($query2))
	{
		$townName = $row['townName'];
		$charLevel = $row['level'];
		$charClass = $row['class'];
	}	
	
	$consumables = array();
	$resources = array ();
?>
</head>

<body>

<div class="Container">

	<?php include("../universal/header.php"); ?>
	
	<div class="centralBox">

	<!-- Checks the database for what items are in the town bank, then categorizes them into different arrays to be displayed in the correct location -->
	<?php
	$query2 = 'SELECT * FROM `' . $townName . '` WHERE `x` = 0 AND `y` = 0';
	$statement2 = $dbCon->prepare($query2);
	$statement2->execute();
	$result2 = $statement2->fetch();
	$statement2->closeCursor();
	
	$warehouseItems = explode(',', $result2['groundItems']);
	
	//loops through all items on the ground

	for ($i = 0; $i < sizeOf($warehouseItems); $i++)
	{
		$warehouseItem = explode('.', $warehouseItems[$i]);
		$currentId = $warehouseItem[0];
		$currentAmount= $warehouseItem[1];
		$current = $warehouseItems[$i];
		if ($currentId != NULL)
		{
			$category = $itemsMaster[$currentId][1];
			if ($category == 'Resource')
			{
				if (!(isset($resources)))
				{$resources = array($warehouseItems[$i]);}
				else
				{array_push($resources, $warehouseItems[$i]);}
			}
			else if ($category == 'Consume')
			{
				if (!(isset($consumables)))
				{$consumables = array($warehouseItems[$i]);}
				else
				{array_push($consumables, $warehouseItems[$i]);}
			}
			else if ($category == 'Weapon' || $category == 'Ammo')
			{
				if (!(isset($fight)))
				{$fight = array($warehouseItems[$i]);}
				else
				{array_push($fight, $warehouseItems[$i]);}
			}
		}
	}
	
	
	
	/*$Query3 = "SELECT * FROM `towns` WHERE `townName` = '$townName'";
	$Query4 = mysqli_query($con, $Query3);
	
	while ($row = mysqli_fetch_assoc($Query4))
	{
		$fd = $row['itemBank'];
		$fd2 = explode (':', $fd);
		foreach ($fd2 as $i)
		{
			if ($i != '')
			{
			$fd3 = explode ('.', $i);
			foreach ($itemsInfo as $i2)
			{
					if ($i2[0] == $fd3[0])
					{
						$category = $i2[1];
						
						if ($category == 'Resource')
						{
							array_push($resources, $i2[0] . "." . $fd3[1]);
						}
						
						else if ($category == 'Consume')
						{
							array_push($consumables, $i2[0] . "." . $fd3[1]);
						}
					}
			}
			}
		}
	}*/
	?>
	
	
	<h3 align='center' style='text-decoration: underline'>Warehouse</h3>
	<h4 style='text-decoration: underline'>Resources</h4>
	<?php
	if (isset($resources))
	{
		echo '<form action="../functions/pickUpItem.php" method="post">';
		foreach ($resources as $res)
		{
			$resArray = explode ('.', $res);
			$itemId = $resArray[0];
			$itemAmount = $resArray[1];
			echo '<input type="hidden" name="location "value="/deadfall/warehouse.php">';
			echo '<input type="image" name="itemName2" value="' . $itemsMaster[$itemId][0] . '" title="' . $itemsMaster[$itemId][0] . '" src="../images/items/' . $itemsMaster[$itemId][0] . '.png" alt="submit">x' . $itemAmount . ' ';
		}
		echo '</form>';
	}
	?>
	<hr style='border: 1px solid black;'>
	<h4 style='text-decoration: underline'>Consumables</h4>
	<?php
	if (isset($consumables))
	{
		echo '<form action="../functions/pickUpItem.php" method="post">';
		foreach ($consumables as $con)
		{			
			$conArray = explode ('.', $con);
			$itemId = $conArray[0];
			$itemAmount = $conArray[1];
			echo '<input type="hidden" name="location "value="/deadfall/warehouse.php">';
			echo '<input type="image" name="itemName2" value="' . $itemsMaster[$itemId][0] . '" title="' . $itemsMaster[$itemId][0] . '" src="../images/items/' . $itemsMaster[$itemId][0] . '.png" alt="submit">x' . $itemAmount . ' ';
		}
		echo '</form>';
	}
	?>
	<hr style='border: 1px solid black;'>
	<h4 style='text-decoration: underline'>Weapons/Ammo</h4>
	<?php
	if (isset($fight))
	{
		echo '<form action="../functions/pickUpItem.php" method="post">';
		foreach ($fight as $fig)
		{
			$figArray = explode ('.', $fig);
			$itemId = $figArray[0];
			$itemAmount = $figArray[1];
			echo '<input type="hidden" name="location "value="/deadfall/warehouse.php">';
			echo '<input type="image" name="itemName2" value="' . $itemsMaster[$itemId][0] . '" title="' . $itemsMaster[$itemId][0] . '" src="../images/items/' . $itemsMaster[$itemId][0] . '.png" alt="submit">x' . $itemAmount . ' ';
		}
		echo '</form>';
	}
	?>
	<hr style='border: 1px solid black;'>
	<h4 style='text-decoration: underline'>Misc.</h4>
	<hr style='border: 1px solid black;'>
	<h4 style='text-decoration: underline'>Special Items</h4>
	
	</div>
	

		
	
</div>
	<?php
	Include ("../universal/hyperlinks.php");
	?>
</body>

</html>