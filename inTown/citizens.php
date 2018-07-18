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
	.head
	{border: 1px solid black;}
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
		
		
		echo "<table><tr style='font-size:20;'><td class='head'>Username</td><td class='head'>Character</td><td class='head'>Class</td><td class='head'>Items</td></tr>";
		while ($row = mysqli_fetch_assoc($Query4))
		{
			$classImg = "../images/icons/" . $row['class'] . ".png";
			if ($row['username'] == $playerName)
			{
				echo "<tr>";
				$charRow = $row['character'];
				echo "<td><p>" . $row['username'] . "</p></td>" . "<td class='samePlayer'><p class='samePlayer2' onclick='changeChar(`" . $charRow . "`)'>" . $row['character'] . "</p>";
				if (doesStatusContainExt(12, $charRow)) //character is dead
				{
					echo "<img src='../images/status/Dead.png' title='DEAD' style='float: right;'>";
				}
				elseif (doesStatusContainExt(10, $charRow)) //character has ended the day
				{
					echo "<img src='../images/status/Day Ended.png' title='Finished Current Day' style='float: right;'>";
				}
				echo "</td>" . "<td><img src='$classImg'> " . $row['class'] . "</td>" . "<td style='border: 1px solid #120B06;'></td>";
				echo "</tr>";
			}
			
			else
			{
				echo "<tr>";
				echo "<td><p>" . $row['username'] . "</p></td>" . "<td style='border: 1px solid #120B06;'>" . $row['character'] . "</td>" . "<td><img src='$classImg'> " . $row['class'] . "</td>" . "<td style='border: 1px solid #120B06;'></td>";
				echo "</tr>";
			}
			
		}
		echo "</table>";
		
		?>
			
			<!--<table>
			<tr style="font-size:1.5em;">
				<td colspan="4">Defences</td>
			</tr>
			<tr>
				<td><p style="font-size:1.25em; font-family:'impact', charcoal, sans-serif;">Outer Wall</p></td>
				<td class="data"><img src="images/wood.png"><style1>7/15 </style1><img src="images/iron.png"><style1>9/15 </style1></td>
				<td><input type="number" class="number" value="0" min="0" max="12"></td>
				<td><img src="images/construct.png" style="float:right;"></td>
			</tr>
	 
			//if the town doesnt have the building reqs than it should hide the particular row 
			//code could be used to display the entire table which would make it easier to implement php in
	
			<tr>
				<td><p style="font-size:1.25em; font-family:'impact', charcoal, sans-serif;">Inner Wall</p></td>
				<td class="data"><img src="images/wood.png">x25 <img src="images/iron.png">x15 </td>
				<td><input type="number" class="number" value="0" min="0" max="12"></td>
				<td><img src="images/construct.png" style="float:right;"></td>
			</tr>
		
			</table>-->
	
		</div>
		
		<!--<div class="spoiler"><p>Well</p></div>
		<div class="spoiler"><p>Garden</p></div>
		<div class="spoiler"><p>Armoury</p></div>
		-->
	</div>
	

		
	
</div>
	<?php
	Include ("../universal/hyperlinks.php");
	?>
</body>

</html>