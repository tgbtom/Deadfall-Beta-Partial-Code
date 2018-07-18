<?PHP
/*NEED TO DO: Return to Login page WITH a note saying if the password or other login details were incorrect*/

$errorMessage = ".";

$uname = filter_input(INPUT_POST,'user');
$pword = filter_input(INPUT_POST,'pass');
//$uname = $_POST['user'];
//$pword = $_POST['pass'];


require_once('../connect.php');

$quer = "SELECT * FROM `userstable` WHERE `username`='$uname' AND `password`='$pword'";
$result = mysqli_query($con, $quer);


if ($result)
{
	$num_results = mysqli_num_rows($result);
		if($num_results > 0)
		{
		session_start();
		$_SESSION['login'] = "$uname";
		$errorMessage = "Successful Login as $uname";
		header ("Location: ../inTown/?locat=browseChars");
		}
		else 
		{
		$errorMessage = "Incorrect Username or Password";
		session_start();
		$_SESSION['login'] = '';
		header ("Location: " . $root . "?error=Incorrect%20username%20or%20password");
		}
}
?>

