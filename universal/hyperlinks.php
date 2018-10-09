<html>
<?php
//If you're logged in, Bottom Bar has the Log Out button as well


if (isset($_SESSION['login']) && $_SESSION['login'] != ''){
echo "<pre><p class='bottomBar' align='center'><a href='../functions/logout.php' class='bottomBar'>Log-Out</a>          <a href='../help.php' class='bottomBar'>HELP</a>          <a href='register.php' class='bottomBar'>Register</a>          <a href='index.php' class='bottomBar'>DeadFall Blog</a></p></pre>
</html>";	
}

else {
	echo "<pre><p class='bottomBar' align='center'><a href='./' class='bottomBar'>Homepage</a>          <a href='help.php' class='bottomBar'>HELP</a>          <a href='help.php' class='bottomBar'>Register</a>          <a href='index.php' class='bottomBar'>DeadFall Blog</a></p></pre>
</html>";
}

//Otherwise display the original hyperlink bar
?>
</html>
