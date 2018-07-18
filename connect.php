<?php
$hostname="localhost";
$username="root";
$password="";
$database=""; 
$con=mysqli_connect($hostname,$username,$password, $database);
if(! $con)
{
//die('Connection Failed'.mysql_error());
print('Couldnt Connect');
}

//Set the root path for the website here
$root = "";


$dsn = 'mysql:host=127.0.0.1;dbname=';
$dsnUser = 'root';
$dsnPass = '';

try 
{
	$dbCon = new PDO($dsn, $dsnUser, $dsnPass);
}
catch(PDOException $e) 
{
$errorMessage = $e->getMessage();
echo "<p>An error has occurred while trying to connect to the database: " . $errorMessage . "</p>";
exit();
}

?>