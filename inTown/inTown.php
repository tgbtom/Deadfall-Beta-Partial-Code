<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");

$errorMessage = FILTER_INPUT(INPUT_GET, 'e');
?>
<html>
<head>

<link rel="stylesheet" type="text/css" href="mainDesignTown.css">

<?php 



//All information here should be retrieved from database simply using the login session and character session

$playerName = $_SESSION['login'];
$charName = $_SESSION['char'];
$charId = $_SESSION['char_id'];


//Set Variables which correspond with th character that is in session (town name, level, class, etc.)
$query1 = "SELECT * FROM `characters` WHERE `id` = '$charId' AND `username` = '$playerName'";
$query2 = mysqli_query($con, $query1);

	while ($row = mysqli_fetch_assoc($query2))
	{
		$townId = $row['town_id'];
		$townName = Towns::getTownNameById($townId);
		$charLevel = $row['level'];
		$charClass = $row['class'];
	}	
?>
</head>
<body>

<div class="Container">

	<?php include("../universal/header.php"); ?>
	
	<div class="centralBox">
		<div class="centralBoxTop">
		<a href="../inTown/?locat=browseChars"> Return to Character Selection</a>
			<h3 style="text-align:center;">Bulletin Board</h3>
		</div>
		<div>
		<!-- Bulletin Information Is Drawn from the Database below -->
		<?php 
		 $Query3 = "SELECT `bulletin` FROM `towns` WHERE `town_id` = '$townId'";
		 $Query4 = mysqli_query($con, $Query3);
		 
		 while ($row = mysqli_fetch_assoc($Query4))
		 {
			 $bulletin = explode (".", $row['bulletin']);
			 foreach ($bulletin as $cur)
			 {
				 /* the if statement skips index 0 of the bulletin which is always empty */
				 if ($cur != '')
				 {
				 echo "<li>" . $cur . "</li>";
				 }
			 }
		 }
		?>
		</div>
	
	</div>
</div>
	<?php
	Include ("../universal/hyperlinks.php");
	?>
</body>
</html>
<?php
if (isset($errorMessage))
{
	?>
	<script type='text/javascript'> 
		document.onreadystatechange = function(){
		if(document.readyState == "complete"){
			alert("<?php echo $errorMessage; ?>");
		}
		};
	</script>
	<?php
} 
?>