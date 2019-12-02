<?PHP
if (session_status() == PHP_SESSION_NONE) 
{
    session_start();
	$locat = filter_input(INPUT_GET, 'locat');
}
//session_start();
require_once ('../functions/queryFunctions.php');

if (!(isset($_SESSION['login']) && $_SESSION['login'] != '')) 
{	
	echo '<script>window.location = "' . $root . '";</script>';
}
elseif (!(isset($_SESSION['char'])) || $_SESSION['char'] == '' || $_SESSION['x'] === NULL || $_SESSION['y'] === NULL)
{
	if (isset($locat) && $locat != "browseChars" && $locat != "join" && $locat != "character")
	{		
		echo '<script>window.location = "' . $root . '/inTown/?locat=browseChars";</script>';
	}
}
elseif (doesStatusContain(12) && $locat != 'citizens' && $locat != 'browseChars' && $locat != 'inTown') //User is logged in and Character is selected... IF CHAR IS DEAD, KEEP THEM INtown On Citizens.
{	
	echo '<script>window.location = "' . $root . '/inTown/?locat=citizens";</script>';
} 

	
?>
