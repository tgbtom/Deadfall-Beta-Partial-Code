<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");
require_once ("../functions/queryFunctions.php");
Include("../data/buildings.php");
Include("../data/items.php");
$errorMessage = FILTER_INPUT(INPUT_GET, 'e');
if (isset($errorMessage))
{
	echo "<script type='text/javascript'>alert('$errorMessage');</script>";
}
?>
<html>
<head>

<link rel="stylesheet" type="text/css" href="mainDesignTown.css">

<?php 

//All information here is retrieved from database simply using the login session and character session

$playerName = $_SESSION['login'];
$charName = $_SESSION['char'];


//Set Variables which correspond with the character that is in session (town name, level, class, etc.)
$query1 = "SELECT * FROM `characters` WHERE `character` = '$charName' AND `username` = '$playerName'";
$query2 = mysqli_query($con, $query1);

	while ($row = mysqli_fetch_assoc($query2))
	{
		$townName = $row['townName'];
		$charLevel = $row['level'];
		$charClass = $row['class'];
	}	
?>





	<link rel="stylesheet" type="text/css" href="mainDesignTown.css">
	<style>
	table {width:100%;}
	table, tr, td {color:#120B06; padding:2px;}
	input.number {width:40px;}
	.data {width:150px;}
	tr {}
	style1 {font-size:0.8em;}
	.samePlayer
	{cursor: hand; background-color: #c7966b; height: 1px; border: 1px solid #120B06;}
	.samePlayer2
	{float: left; vertical-align: text-top; height: 1%;}
	.sameChar
	{cursor: hand; background-color: #F2C59D; height: 1px; border: 1px solid #120B06;}
	.sameChar2
	{float: left; vertical-align: text-top; height: 1%;}
	.head
	{border: 1px solid black;}
	.itemDiv {
    position: relative;
    display: inline-block;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
	}
	</style>
	
			<script>
		function changeChar(newChar) {

		if (newChar.length == 0) 
		{
			return;
		} 
		else
		{
        var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
					//document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
				}
			};
        xmlhttp.open("GET", "../functions/changeChar.php?change="+newChar, true);
        xmlhttp.send();
		window.location.reload();
    }	
}

		</script>
</head>

<body>

<div class="Container">

	<?php include("../universal/header.php"); ?>
	
	<div class="centralBox">

		<h3 align="center" style="text-decoration: underline;">Citizens</h3>
		
		
		<?php
		
		$Query3 = "SELECT * FROM `characters` WHERE `townName` = '$townName'";
		$Query4 = mysqli_query($con, $Query3);
		
		
		echo "<table><tr style='font-size:20;'><td class='head'>Username</td><td class='head'>Character</td><td class='head'>Class</td><td class='head'>Items</td><td class='head'>Status</td></tr>";
		while ($row = mysqli_fetch_assoc($Query4))
		{
			$charRow = $row['character'];
			$userRow = $row['username'];
			
			//Query loads the row in the characters DB that corresponds to the character being checked
			$query1 = 'SELECT * FROM `characters` WHERE `character` = :character AND `username` = :username';
			$statement1 = $dbCon->prepare($query1);
			$statement1->bindValue(':username', $userRow);
			$statement1->bindValue(':character', $charRow);
			$statement1->execute();
			$charDetails = $statement1->fetch();
			$statement1->closeCursor();

			$itemsHeld = $charDetails['items'];
			$itemsHeldArray = explode(',', $itemsHeld);
			$status = $charDetails['status'];
			$statusArray = explode('.', $status);
			
			$classImg = "../images/icons/" . lcfirst($row['class']) . ".png";
			if ($row['username'] == $playerName)
			{
                                if ($charName == $charRow)
                                {
                                    echo "<tr class='sameChar'>";
                                    echo "<td><p>" . $row['username'] . "</p></td>" . "<td class='sameChar'><p class='sameChar2' onclick='changeChar(`" . $charRow . "`)'>" . $row['character'] . "</p>";
                                }
                                else 
                                {
                                    echo "<tr>";
                                    echo "<td><p>" . $row['username'] . "</p></td>" . "<td class='samePlayer'><p class='samePlayer2' onclick='changeChar(`" . $charRow . "`)'>" . $row['character'] . "</p>";
                                }
		
				if (doesStatusContainExt(12, $charRow)) //character is dead
				{
					echo "<img src='../images/status/Dead.png' title='DEAD' style='float: right;'>";
				}
				elseif (doesStatusContainExt(10, $charRow)) //character has ended the day
				{
					echo "<img src='../images/status/Day Ended.png' title='Finished Current Day' style='float: right;'>";
				}
				echo "</td>" . "<td><img src='$classImg'> " . $row['class'] . "</td>" . "<td style='border: 1px solid #120B06;'>";
				
				if ($itemsHeld != NULL)
				{
					for ($i = 0; $i < sizeOf($itemsHeldArray); $i++)
					{
						$itemName = $itemsMaster[$itemsHeldArray[$i]][0];
						echo '<div class="itemDiv"><img src="../images/items/' . $itemName . '.png" class="item"><img src="../images/rarity/' . getRarityString($itemsHeldArray[$i]) . '.png" title="' . $itemName . '" class="rarityBanner"></div>';
					}
				}
				
				echo "</td><td style='border: 1px solid #120B06;'></td>";
				echo "</tr>";
			}
			
			else
			{
				echo "<tr>";
				echo "<td><p>" . $row['username'] . "</p></td>" . "<td style='border: 1px solid #120B06;'>" . $row['character'];
				if (doesStatusContainExt(12, $charRow, $row['username'])) //character is dead
				{
					echo "<img src='../images/status/Dead.png' title='DEAD' style='float: right;'>";
				}
				elseif (doesStatusContainExt(10, $charRow, $row['username'])) //character has ended the day
				{
					echo "<img src='../images/status/Day Ended.png' title='Finished Current Day' style='float: right;'>";
				}
				
				echo "</td><td><img src='$classImg'> " . $row['class'] . "</td>" . "<td style='border: 1px solid #120B06;'>";
				
				if ($itemsHeld != NULL)
				{
					for ($i = 0; $i < sizeOf($itemsHeldArray); $i++)
					{
						$itemName = $itemsMaster[$itemsHeldArray[$i]][0];
						echo '<div class="itemDiv"><img src="../images/items/' . $itemName . '.png" class="item"><img src="../images/rarity/' . getRarityString($itemsHeldArray[$i]) . '.png" title="' . $itemName . '" class="rarityBanner"></div>';
					}
				}
				
				echo "</td><td style='border: 1px solid #120B06;'></td>";
				
				echo "</tr>";
			}
			
		}
		echo "</table>";
		?>
		</div>
	</div>	
</div>
	<?php
	Include ("../universal/hyperlinks.php");
	?>
</body>

</html>