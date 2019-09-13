<?php require_once ("connect.php");?>
<html>
<title>DeadFall Login</title>
<head>

	<link rel="stylesheet" type="text/css" href="mainDesign.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">

<script type="text/javascript">
		function validateform2()
			{
				var a=document.forms["log"]["user"].value;
				var b=document.forms["log"]["pass"].value;
				if ((a==null || a=="" || a=="Username") && (b==null || b=="" || b=="password"))
				{
					alert("All Field must be filled out");
					return false;
				}
				if (a==null || a=="" || a=="Username")
				{
					alert("Username must be filled out");
					return false;
				}
				if (b==null || b=="" || b=="password")
				{
					alert("Password must be filled out");
					return false;
				}
			}
</script>

</head>
<body>
	<div class="Container">
		<div class="header">
		<img src="images/DeadfallBanner.png">
		</div>
		
		<div class="news">
		<h3 align="center">News and Updates</h3>
		<hr style="width:103%; border-color: black;">
		<ul>
			<li><p><img src="images/icons/sword.png" align="center"> <b>May 19th, 2019:</b> <i>The <q>horde size</q> (attack) image has been added!</i></p></li>
			<li><p><img src="images/icons/zombie.png" align="center"> <b>May 19th, 2019:</b> The <q>zombie</q> image has been updated!</p></li>	
			<li><p><img src="images/Added/Bits of Food.png" align="center"> <b>May 19th, 2019:</b> The artwork for <q>Bits of food</q> has been updated!</p></li>
			<li><p><img src="images/items/Bow.png" align="center"><img src="images/items/Arrow.png" align="center"><img src="images/items/Pistol.png" align="center"> <b>July 22nd, 2018:</b> Art has been updated for the following items: Pistol, Bow, and Arrow. </i></p></li>
			<li><p><img src="images/items/fillerItem.png" align="center"> <b>April 26th, 2018:</b> The following items have been added: Bow, Arrow, Slingshot, Rock, Cloth, Pistol, Small Bullet</i></p></li>
			<li><p><img src="images/items/Grenade.png" align="center"> <b>November 14th, 2017:</b> <i>The <q>Grenade</q> has been added!</i></p></li>
			<li><p><img src="images/icons/zombie.png" align="center"> <b>November 7th, 2017:</b> <i>The <q>zombie</q> prototype image has been added!</i></p></li>
			<li><p><img src="images/Added/new.png" align="center"><b>November 6th, 2017:</b> <i>Prototype map generation has been configured!</i></p></li>			
			<li><p><img src="images/Added/Rope.png" align="center"> <b>December 22nd, 2016:</b> <i>The <q>Rope</q> has been added!</i></p></li>			
			<li><p><img src="images/Added/Battery.png" align="center"> <b>December 22nd, 2016:</b> <i>The <q>Battery</q> has been added!</i></p></li>			
			<li><p><img src="images/Added/Brick.png" align="center"> <b>December 22nd, 2016:</b> <i>The <q>Brick</q> has been added! A new resource for construction sites.</i></p></li>
			<li><p><img src="images/Added/Sheet Metal.png" align="center">&rarr;<img src="images/Added/Sheet Metal New.png" align="center"> <b>December 18th, 2016:</b> <i>The icon for the <q>Sheet Metal</q> has been updated.</i></p></li>
			<li><p><img src="images/Added/looter.png" align="center"> <b>December 18th, 2016:</b> <i>The icon for the <q>Looter</q> class is now implemented.</i></p></li>
			<li><p><img src="images/Added/survivor.png" align="center"> <b>December 17th, 2016:</b> <i>The icon for the <q>Survivor</q> class is now implemented.</i></p></li>
			<li><p><img src="images/Added/builder.png" align="center"> <b>December 17th, 2016:</b> <i>The icon for the <q>Builder</q> class is now implemented.</i></p></li>
			<li><p><img src="images/Added/runner.png" align="center"> <b>December 14th, 2016:</b> <i>The icon for the <q>Runner</q> class is now implemented.</i></p></li>
			<li><p><img src="images/Added/sharpStick.png" align="center"> <b>October 8th, 2015:</b> <i>The <q>Sharp stick</q> has been added! It is the first weapon used for killing zombies.</i></p></li>
			<li><p><img src="images/Added/stone.png" align="center"> <b>October 8th, 2015:</b> <i><q>Stone</q> has been added! They can be used in a variety of different constructions or crafting.</i></p></li>
			<li><p><img src="images/Added/bitsOfFood.png" align="center"> <b>October 8th, 2015:</b> <i><q>Bits of food</q> has been added!  Satisfies hunger, restores AP and prevents death from starvation.</i></p></li>
			<li><p><img src="images/Added/makeShiftSpear.png" align="center"> <b>October 8th, 2015:</b> <i><q>Make shift spear</q> has been added!  Improved version of the <q>Sharp stick</q>, deals more damage and has a smaller chance of breaking.</i></p></li>
			<li><p><img src="images/Sheet Metal.png"> <b>August 10th, 2015:</b> <i>Sheet Metal has been added! Used as a basic resource for construction.</p></li>
			<li><p><img src="images/Water Ration.png" align="center"> <b>August 9th, 2015:</b> <i>Water has been added! Quenches thirst, restores AP and prevents death from dehydration.</i></p></li>
			<li><p><img src="images/Wood Board.png" align="center"> <b>August 9th, 2015:</b> <i>Wood has been added! Used as a basic resource for construction.</i></p></li>
		</ul>
		
		</div>
		
		<div class="login">
			<b>Login Details</b>
			
			<div class="login-top">
			
				<form action="<?php echo "./functions/validate.php"; ?>" name="log" method="post" onsubmit="return validateform2()">
				<?php $errorMessage = filter_input(INPUT_GET, "error"); if (!empty($errorMessage)) { ?> <p> <?php echo htmlspecialchars($errorMessage); ?> </p> <?php } ?>
					<input type="text" placeholder="Username" name="user" value="" required></br>
					<input type="password" placeholder="Password" name="pass" value="" required></br>
					<a href="<?php echo htmlspecialchars($root); ?>" class="forgot">Forgot your password?</a></br>
					<input type="submit" value="Login">
				</form>
				
			
			</div>
			
			<div class="login-bottom">
			
				<form action=".">	
					<input type="submit" value="Register" disabled>
				</form>
			
			</div>
			
		</div>
		
	</div>
	
	<br><br>
	<hr>
	<?php
	Include ("universal/hyperlinks.php");
	?>
	
	
</body>
</html>
