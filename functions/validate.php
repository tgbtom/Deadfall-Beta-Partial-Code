<?PHP
/*NEED TO DO: Return to Login page WITH a note saying if the password or other login details were incorrect*/

$errorMessage = ".";

$uname = filter_input(INPUT_POST,'user');
$pword = filter_input(INPUT_POST,'pass');
//$uname = $_POST['user'];
//$pword = $_POST['pass'];


require_once('../connect.php');

$query = "SELECT * FROM `userstable` WHERE `username` = :username AND `password` = :password";
$statement = $dbCon->prepare($query);
$statement->bindValue(':username', $uname);
$statement->bindValue(':password', $pword);
$statement->execute();
$result = $statement->fetch();
$statement->closeCursor();

if($result){
	session_start();
	$_SESSION['login'] = $uname;
	$_SESSION['user_id'] = $result['id'];
	header ("Location: ../inTown/?locat=browseChars");
}
else{
	$errorMessage = "Incorrect Username or Password";
	session_start();
	$_SESSION['login'] = '';
	header ("Location: " . $root . "?error=Incorrect%20username%20or%20password");	
}
?>

