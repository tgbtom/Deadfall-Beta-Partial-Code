<?php 
//require_once ("../connect.php");
//require_once ("../functions/verifyLogin.php");
$_SESSION['x'] = '';
$_SESSION['y'] = '';
?>

<html>
<title>Character Browse/Create</title>
<head>

<style>
.data1
{border: 1px solid black;}
.data2
{border: 1px solid black; background-color:#c7966b;}
.data3
{border: 1px solid black; border-radius: 25px; padding: 1%; margin-bottom: 4%; background-color:#c7966b;}

</style>

<script type="text/javascript">

	var name="charName";
	var town="No Town";

		function validateinfo()
			{
				var a=document.forms["creation"]["charName"].value;
				var b=document.forms["creation"]["gender"].value;
				var c=document.forms["creation"]["charClass"].value;
				//Still need to find out how to check if name is already being used by this user
				if (a==null || a=="" || a=="Character Name")
				{
					alert("Name must be picked");
					return false;
				}
			}
		
		function displayInfo(arg1,arg2,arg3)
			{
				document.getElementById("play").disabled = false;
				document.getElementById("name").value = arg1;
				document.getElementById("name2").innerHTML = arg1;
				document.getElementById("name2").style.textAlign = "center";
				document.getElementById("town").innerHTML = arg2;
				document.getElementById("class").innerHTML = arg3;
				document.getElementById("town2").innerHTML = "Town: ";
				document.getElementById("class2").innerHTML = "Class: ";

				//Changes play button words depending on town status of selected character
				var x = document.getElementById("town");
				if (x.innerHTML == "none")
				{
					document.getElementById("play").value = "Join Town";
				}
				
				else
				{
					document.getElementById("play").value = "Play Town";
				}
				
				//modifies colspan of info display, to fix layout of table when character is selected
				var colsp = document.getElementsByClassName("data1");
				var i;
					for (i = 0; i < colsp.length; i++)
					{
						colsp[i].colSpan = "1";
					}
					
				//makes second column for table become visible
				var vis = document.getElementsByClassName("data2");
				var i2;
					for (i2 = 0; i2 <= vis.length; i2++)
					{
						vis[i2].style.visibility = "visible";
					}
			}
			
		function checkTown ()
			{
				var x = document.getElementById("town");
				if (x.innerHTML != ".")
				{
					var y = document.getElementById("play").value;
					
					if (y == "Play Town")
					{
						//if the player has already joined a town, it will send them to the town instead of finding one to join
						//document.joinOrPlay.action = "inTown.php";			
					}

				}
			}
</script>

<link rel="stylesheet" type="text/css" href="../mainDesign.css">
	
</head>

<body bgcolor="#1A0000">
	<div class="Container">
	
		<div class="header">
		<img src="../images/DeadFallLogo2.png">
		</div>
		
		<div class="browseBlock">
		<h3 align="center">Created Characters</h3>
		<hr style="border-color: black;">
		
		<ul>
		<table style="width:90%;" cellspacing="1">
		<tr><td style='border: 1px solid black;'>Level</td><td style='border: 1px solid black;'>Name</td><td style='border: 1px solid black;'>Class</td></tr>
		
		<?php 
		
		if (isset($_SESSION['login']) && $_SESSION['login'] != '')
		{
			
			$user = $_SESSION['login'];
			$check = "SELECT * FROM characters WHERE username = :username";
			$query = $dbCon->prepare($check);
			$query->bindValue(':username', $user);
			$query->execute();
			$results = $query->fetchAll();
			$query->closeCursor();
			
			foreach ($results as $result)
			{
				$tips = $result["character"];
				$tips2 = $result["townName"];
				$tips3 = lcfirst($result["class"]); //returns class name with first character as lowercase
				$classImg = $root . "/images/icons/" . lcfirst($tips3) . ".png"; 
				echo "<tr>" . "<td>Lvl: " . $result["level"] . "</td><td onclick=displayInfo('$tips','$tips2','$tips3') class='hand' style='border: 1px solid black; background-color: #c7966b;'>" . $result["character"] . "</td><td><img src='$classImg'> " . $result["class"] . "</td></tr>";
			}
		}
	
	
		?>
		</table>
		
		</ul>
		<ul>

		</ul>
		
		</div>
		
		<div class="infoBlock">
		
		<form action="<?php echo "../inTown/?locat=join";?>" method="post" name="joinOrPlay"><input type="submit" class="login-top" id="play" value="Welcome" onclick="checkTown()" name="tempChar2" disabled="true">
		<!-- Need to make the input type below so nobody can change it, make it just plain text if possible -->
		<input id="name" name="tempChar" value="Select a Character" hidden></form>
		<table width="90%">
		<tr><td style="border:1px solid black;" colspan="2"><b><p id="name2" style="text-align: left;">Please</p></b></td></tr>
		<tr><td class="data1" colspan="2"><b><p id="town2">Select</p></td><td class="data2" style="visibility: hidden;"><p id="town">none</p></b></td></tr>
		<tr><td class="data1" colspan="2"><b><p id="class2">Character</p></td><td class="data2" style="visibility: hidden;"><p id="class"></p></b></td></tr>
		</table>
		</div>
		
		
		<div class="createBlock">
			<details><summary class="hand" style="outline:none;">Create a Character</summary>
			
			<b><div class="login-top"></b>
			
				<form name="creation" action="<?php echo $root . "/functions/createChar.php";?>" method="post" onsubmit="return validateinfo()">
					<input type="text" name="charName" value="Character Name" onfocus="if (this.value == 'Character Name') {this.value='';}" onblur="if (this.value == '') {this.value = 'Character Name';}">
					<br>
					<input type="radio" name="gender" value="Male" checked> MALE
					<input type="radio" name="gender" value="Female" disabled> FEMALE
					<br><br>
					<div class="spacer"><input type="radio" name="charClass" value="Survivor" checked><img src='../images/icons/survivor.png'> Survivor</div>
					<div class="spacer2"><input type="radio" name="charClass" value="Builder"><img src='../images/icons/builder.png'> Builder</div>
					<div class="spacer data3"><p class="caption">The Survivor class is an all around class. Survivors begin with 16 AP rather than 12 but have no special abilities.</p></div>
					<div class="spacer2 data3"><p class="caption">The Builder class is for characters who want to focus on construction and building, AP is twice effective when constructing.</p></div>
					<div class="spacer"><input type="radio" name="charClass" value="Runner"><img src='../images/icons/runner.png'> Runner</div>
					<div class="spacer2"><input type="radio" name="charClass" value="Looter"><img src='../images/icons/looter.png'> Looter</div>
					<div class="spacer data3"><p class="caption">The Runner class is for characters who want to focus on exploration and travelling distances from town. Runners consume less AP when moving around.</p></div>
					<div class="spacer2 data3"><p class="caption">The Looter class is for characters who want to focus on looting and bringing helpful resources into town for constructions and whatnot.</p></div>
					<input type="submit" value="Create!">
				</form>
				
			
			</div>
			</details>
			
		</div>
		</details>
		
	</div>
	
	<br><br>
	<hr>
	
	<?php
	Include ("../universal/hyperlinks.php");
	?>
	
</body>
</html>
