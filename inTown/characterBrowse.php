<?php 
//require_once ("../connect.php");
//require_once ("../functions/verifyLogin.php");
$_SESSION['x'] = '';
$_SESSION['y'] = '';
?>

<html>
<head>
<title>Character Browse/Create</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
.data1
{
	border: 1px solid black;
	background-color: #A5907E;
	height: auto;
}
.data2
{
	border: 1px solid black; background-color:#c7966b;
	background-color: #A5907E;
	height: auto;
}
.data3
{
	border: 1px solid black; 
	border-radius: 25px; 
	padding: 1%; 
	margin-bottom: 4%; 
	background-color: #A5907E;
	height: auto;
}

.clickable
{
    cursor: pointer;
	border-left:1px solid black; 
	border-right:1px solid black; 
	background-color: #A08C7A;
}

 .charClass:checked + .spacer{
	box-shadow: 0 0 1px 1px #2ecc71;
 }

</style>

<script type="text/javascript">

	var name="charName";
	var town="No Town";

		function validateInfo()
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
		
		function displayInfo(name,arg1,arg2,arg3)
			{
				document.getElementById("play").disabled = false;
				document.getElementById("charId").value = arg1;
				document.getElementById("name").value = name;
				document.getElementById("name2").innerHTML = name;
				document.getElementById("name2").style.textAlign = "center";
				//replace underscored with spaces for display
				document.getElementById("town").innerHTML = arg2.replace(/_/g, " ");
				document.getElementById("class").innerHTML = arg3;
				document.getElementById("town2").innerHTML = "Town: ";
				document.getElementById("class2").innerHTML = "Class: ";
                                
                                characterNames = document.getElementsByClassName("clickable");
                                for (i=0; i < characterNames.length; i++)
                                {
                                    characterNames[i].style.backgroundColor = "#A08C7A";
                                    if (i == characterNames.length - 1)
                                    {
                                        characterNames[i].style.border = "none";
                                        characterNames[i].style.borderBottom = "1px solid black";
                                    }
                                    else
                                    {
                                        characterNames[i].style.border = "none"; 
                                    }
                                }
                                document.getElementById(arg1).style.backgroundColor = "#BAA28D";
                                document.getElementById(arg1).style.border = "1px solid black";
                                

				//Changes play button words depending on town status of selected character
				var x = document.getElementById("town");
				if (x.innerHTML == '')
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
					for (var i2 = 0; i2 <= vis.length - 1; i2++)
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
		<img src="../images/DeadfallBanner.png">
		</div>
		
		<div class="browseBlock">
		<h3 align="center">Created Characters</h3>
		<hr style="border-color: black;">
		
		<ul>
		<table style="" class="browseCharsTable" cellspacing="0">
		<tr><td style='border: 1px solid black;'>Level</td><td style='border: 1px solid black;'>Name</td><td style='border: 1px solid black;'>Class</td><td style='border: 1px solid black;' colspan="2">Town</td></tr>
		
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
			
			?>
			<form name="Multi-Select" method="post">
			<?php
			foreach ($results as $result)
			{
				$townId = $result["town_id"];
				$name = $result["character"];
				$tips = $result["id"];
				$tips2 = Towns::getTownNameById($townId);
				$convertedTownName = str_replace("_", " ", $tips2);
				$tips3 = lcfirst($result["class"]); //returns class name with first character as lowercase
				$classImg = $root . "/images/icons/" . lcfirst($tips3) . ".png"; 
				if($tips2 == NULL){
					echo "<tr id='" . $tips . "' class='clickable' onclick=displayInfo('$name','$tips','$tips2','$tips3')>" . "<td>Lvl: " . $result["level"] . "</td><td>" . $result["character"] . "</td><td><img src='$classImg'> " . $result["class"] . "</td><td>" . $convertedTownName . "</td><td><input type='checkbox' form='join' name='selectedChars[]' value=" . $result["id"] . "></td></tr>";
				}
				else{
					echo "<tr id='" . $tips . "' class='clickable' onclick=displayInfo('$name','$tips','$tips2','$tips3')>" . "<td>Lvl: " . $result["level"] . "</td><td>" . $result["character"] . "</td><td><img src='$classImg'> " . $result["class"] . "</td><td colspan='2'>" . $convertedTownName . "</td></tr>";
				}
			}
		}
	
	
		?>
		</form>
		</table>
		
		</ul>
		<ul>

		</ul>
		
		</div>
		
		<div class="infoBlock">
		
		<form action="<?php echo "../inTown/?locat=join";?>" method="post" name="joinOrPlay" id="join"><input type="submit" class="login-top" id="play" value="Welcome" onclick="checkTown()" name="tempChar2" disabled="true">
		<!-- Need to make the input type below so nobody can change it, make it just plain text if possible -->
		<input id="name" name="tempChar" value="Select a Character" hidden>
		<input id="charId" name="selectedChar" value=0 hidden>
		</form>
		
		<table width="90%">
		<tr><td style="border:1px solid black;" colspan="2"><b><p id="name2" style="text-align: left;">Please</p></b></td></tr>
		<tr><td class="data1" colspan="2"><b><p id="town2">Select</p></td><td class="data2" style="visibility: hidden;"><p id="town">none</p></b></td></tr>
		<tr><td class="data1" colspan="2"><b><p id="class2">Character</p></td><td class="data2" style="visibility: hidden;"><p id="class"></p></b></td></tr>
		</table>
		</div>
		
		
		<div class="createBlock">
			<details><summary class="hand" style="outline:none;">Create a Character</summary>
			
			<b><div class="login-top"></b>
			
				<form name="creation" action="<?php echo $root . "/functions/createChar.php";?>" method="post" onsubmit="return validateInfo()">
                                    <input type="text" name="charName" value="" placeholder="Character Name">
					<br>
					<label><input type="radio" name="gender" value="Male" checked> MALE </label>
					<label><input type="radio" name="gender" value="Female" disabled> FEMALE </label>
					<br><br>

					<div class="spacerCombo">
					<label><div class="spacer"><input type="radio" class="charClass" name="charClass" value="Survivor" checked><img src='../images/icons/survivor.png'> Survivor</div>
					<div class="spacer2 data3" id="survivorTag"><p class="caption">The Survivor class is an all around class. Survivors begin with 16 AP rather than 12 but have no special abilities.</p></div></label>
					</div>

					<div class="spacerCombo">
					<label><div class="spacer"><input type="radio" class="charClass" name="charClass" value="Builder"><img src='../images/icons/builder.png'> Builder </div>
					<div class="spacer2 data3" id="builderTag"><p class="caption">The Builder class is for characters who want to focus on construction and building, AP is twice effective when constructing.</p></div></label>
					</div>

					<div class="spacerCombo">
					<label><div class="spacer"><input type="radio" class="charClass" name="charClass" value="Runner" disabled><img src='../images/icons/runner.png'> Runner -- Coming Soon</div>
					<div class="spacer2 data3"><p class="caption">The Runner class is for characters who want to focus on exploration and travelling distances from town. Runners consume less AP when moving around.</p></div></label>
					</div>

					<div class="spacerCombo">
					<label><div class="spacer"><input type="radio" class="charClass" name="charClass" value="Looter" disabled><img src='../images/icons/looter.png'> Looter -- Coming Soon</div>
					<div class="spacer2 data3"><p class="caption">The Looter class is for characters who want to focus on looting and bringing helpful resources into town for constructions.</p></div></label>
					</div>
					
					<input type="submit" value="Create!">
				</form>
				
			</div>
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
