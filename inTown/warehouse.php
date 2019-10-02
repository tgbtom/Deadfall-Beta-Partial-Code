<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");
require_once ("../data/items.php");
require_once ("../functions/queryFunctions.php");

$errorMessage = FILTER_INPUT(INPUT_GET, 'e');
if ($_SESSION['x'] != 0 || $_SESSION['y'] != 0)
{
	$errorMessage .= 'Character is out of town!';
	echo "<script>window.location.href='.?locat=outside'</script>";
}
if (isset($errorMessage))
{
	echo "<script type='text/javascript'>alert('$errorMessage');</script>";
}
?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="mainDesignTown.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
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
$charId = $_SESSION['char_id'];

$charObject = new Character($charId);

$townId = $charObject->townId;
$townName = Towns::getTownNameById($townId);
$townTableName = Towns::getTownTableName($townId);
$charLevel = $charObject->level;
$charClass = $charObject->class;
	
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
	$query2 = 'SELECT * FROM `' . $townTableName . '` WHERE `x` = 0 AND `y` = 0';
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
	?>
	
	<h3 align='center' style='text-decoration: underline'>Warehouse</h3>
	<div class="storageCategory">
	<h4 style='text-decoration: underline'>Resources</h4>
	<?php
	if (isset($resources))
	{
		foreach ($resources as $res)
		{
                    echo '<form action="../functions/pickUpItem.php" method="post" class="warehouseItemsForm">';
                    $resArray = explode ('.', $res);
                    $itemId = $resArray[0];
                    $itemAmount = $resArray[1];
                    echo '<input type="hidden" name="location "value="/deadfall/warehouse.php">';
                    echo '<input type="hidden" name="itemName2" value="' . $itemsMaster[$itemId][0] . '">';
                    echo '<input type="image" title="' . $itemsMaster[$itemId][0] . '" src="../images/items/' . $itemsMaster[$itemId][0] . '.png" alt="submit">x' . $itemAmount;
                    echo '</form>'; 
		}
		if(count($resources) == 0){
			echo "<p>No <i>Resources</i> in the Warehouse</p>";
		}
	}
	?>
	</div>

	<div class="storageCategory">
	<h4 style='text-decoration: underline'>Consumables</h4>
	<?php
	if (isset($consumables))
	{
		
		foreach ($consumables as $con)
		{	
                    echo '<form action="../functions/pickUpItem.php" method="post" class="warehouseItemsForm">';
                    $conArray = explode ('.', $con);
                    $itemId = $conArray[0];
                    $itemAmount = $conArray[1];
                    echo '<input type="hidden" name="location "value="/deadfall/warehouse.php">';
                    echo '<input type="hidden" name="itemName2" value="' . $itemsMaster[$itemId][0] . '">';
                    echo '<input type="image" title="' . $itemsMaster[$itemId][0] . '" src="../images/items/' . $itemsMaster[$itemId][0] . '.png" alt="submit">x' . $itemAmount . ' ';
                    echo '</form>';
		}
		if(count($consumables) == 0){
			echo "<p>No <i>Consumables</i> in the Warehouse</p>";
		}
		
	}
	?>
	</div>

	<div class="storageCategory">
	<h4 style='text-decoration: underline'>Weapons/Ammo</h4>
	<?php
	if (isset($fight))
	{
		foreach ($fight as $fig)
		{
                    echo '<form action="../functions/pickUpItem.php" method="post" class="warehouseItemsForm">';
                    $figArray = explode ('.', $fig);
                    $itemId = $figArray[0];
                    $itemAmount = $figArray[1];
                    echo '<input type="hidden" name="location "value="/deadfall/warehouse.php">';
                    echo '<input type="hidden" name="itemName2" value="' . $itemsMaster[$itemId][0] . '">';
                    echo '<input type="image" title="' . $itemsMaster[$itemId][0] . '" src="../images/items/' . $itemsMaster[$itemId][0] . '.png" alt="submit">x' . $itemAmount . ' ';
                    echo '</form>';
		}
	}
	else{
			echo "<p>No <i>Weapons or Ammo</i> in the Warehouse</p>";
	}
	?>
	</div>

	<div class="storageCategory">
	<h4 style='text-decoration: underline'>Misc.</h4>
	<?php echo "<p>No <i>Miscellaneous Items</i> in the Warehouse</p>"; ?>
	</div>

	<div class="storageCategory">
	<h4 style='text-decoration: underline'>Special Items</h4>
	<?php echo "<p>No <i>Special Items</i> in the Warehouse</p>"; ?>
	</div>
	
	</div>
	

		
	
</div>
	<?php
	Include ("../universal/hyperlinks.php");
	?>
</body>

</html>