<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");

//Check amount of Residents to display later

	$temp = filter_input(INPUT_GET, 'tempChar');
	if (!isset($temp))
	{
		$temp = filter_input(INPUT_POST, 'tempChar');
	}
	//$temp = $_POST["tempChar"]
	$_SESSION['char'] = $temp;
	$cName = $temp;
	$pName = $_SESSION['login'];
	//if the character is already in a town, forward the player to inTown.php
	$query1 = "SELECT * FROM `characters` WHERE `username` = '$pName' AND `character` = '$cName'";
	$query2 = mysqli_query($con, $query1);
	while ($row = mysqli_fetch_assoc($query2))
	{
		if ($row['townName'] != 'none')
		{
			//***SET SESSION for the character's current location ***ALSO add this code to 'addToTown.php' for players that join a new town
			//First we search every zone to figure out where the current character is ($con = settlements DB)
			$querySelect = "SELECT * FROM `" . $row['townName'] . "`";
			$querySelect2 = mysqli_query($con,$querySelect);
			while ($row = mysqli_fetch_assoc($querySelect2))
			{
				$currentRow = explode('.', $row['charactersHere']);
				for ($i = 0; $i < count($currentRow); $i++)
				{
					if ($currentRow[$i] == $cName)
					{
						$_SESSION['x'] = $row['x'];
						$_SESSION['y'] = $row['y'];
					}
				}
			}
			$_SESSION['char'] = $cName;
			echo '<script>window.location = "' . $root . '/inTown/?locat=inTown";</script>';
			exit();
		}
	}
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="../mainDesign.css">
</script>
<!-- source below grants access to JQuery,through Microsoft network (Can download Jquery file and host it through the website too) -->
<script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.3.min.js"></script>
</head>

<body bgcolor="#1A0000">
	<div class="Container">
		<div class="header">
		<img src="../images/DeadFallLogo2.png">
		</div>

		<div class="browseBlock3">
		
		<?php
		$check = "SELECT * FROM `towns` WHERE `townFull`=0";
		$query = mysqli_query($con, $check);
		$x = 0;

		while ($row = mysqli_fetch_assoc($query))
		{
			$x += 1;
			$t = $row["townName"];
			$c = $cName;
			$emptyTown = "<li>" . $x . ": " . $row["townName"] . "[" . $row["amountResidents"] . "/" . $row["maxResidents"] . "]  <form method='post' action='../functions/addToTown.php'><input type='hidden' name='newTown' value=$t><input type='hidden' name='char' value=$c><input type='submit' value='Join'></form></li>";
			echo $emptyTown;
		}
		?>
		
		<br><hr><br>
		<h3>***Note: Do NOT use any spaces in town-name***</h3>
		<form method='post' action='../functions/createTown.php'>
		<input type='text' name='newTown' style="margin: 5px;">
		<input type='hidden' name='cName' value='<?php echo $cName;?>'>
		<input type='hidden' name='char' value=$c>
		<input type='submit' value='Create Town'>
		</form>
		</div>

		
</body>
</html>