<div style="width: 95%; min-width: 380px;">

<style>
	.bottomBar{
		width: 95%;
	}
	.bottomLink{
		width: 24%;
		text-align: center;
		display: inline-block;
	}
	hr{
		width: 100%;
	}
</style>

<?php
//If you're logged in, Bottom Bar has the Log Out button as well

if (isset($_SESSION['login']) && $_SESSION['login'] != ''){
echo "<p class='bottomBar' align='center'>
<div class='bottomLink'><a href='../functions/logout.php' class='bottomBar'>Log-Out</a></div>
<div class='bottomLink'><a href='../help.php' class='bottomBar'>HELP</a></div>
<div class='bottomLink'><a href='register.php' class='bottomBar'>Register</a></div>
<div class='bottomLink'><a href='index.php' class='bottomBar'>DF Blog</a></div>
</p>";
}

else {
	echo "<p class='bottomBar' align='center'>
	<div class='bottomLink'><a href='./' class='bottomBar'>Homepage</a></div>
	<div class='bottomLink'><a href='help.php' class='bottomBar'>HELP</a></div>
	<div class='bottomLink'><a href='help.php' class='bottomBar'>Register</a></div>
	<div class='bottomLink'><a href='index.php' class='bottomBar'>DF Blog</a></div>
	</p>";
}

?>

</div>