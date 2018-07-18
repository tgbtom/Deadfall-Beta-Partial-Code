<?PHP
if (session_status() == PHP_SESSION_NONE) {
    session_start();
	
	$locat = filter_input(INPUT_GET, 'locat');
}
//session_start();
require_once ('../functions/queryFunctions.php');

if (!(isset($_SESSION['login']) && $_SESSION['login'] != '')) 
{
	header ("Location: " . $root . "");
}
elseif (!(isset($_SESSION['char'])) || $_SESSION['char'] == '' || $_SESSION['x'] == '' || $_SESSION['y'] == '')
{
	if (isset($locat) && $locat != "browseChars" && $locat != "join")
	{
		header ("Location: ../inTown/?locat=browseChars");
	}
}
elseif (doesStatusContain(12) && $locat != 'citizens' && $locat != 'browseChars' && $locat != 'inTown') //User is logged in and Character is selected... IF CHAR IS DEAD, KEEP THEM INtown On Citizens.
{
	header ("location: ../inTown/?locat=citizens");
} 

	
?>
