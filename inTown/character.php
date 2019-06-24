<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");
require_once ("../data/buildings.php");
require_once ("../data/items.php");
require_once ("../functions/queryFunctions.php");
require_once ("../model/structures.php");
require_once ("../model/database.php");

$errorMessage = FILTER_INPUT(INPUT_GET, 'e');
if (isset($errorMessage)) {
    echo "<script type='text/javascript'>alert('$errorMessage');</script>";
}

//All information here is retrieved from database simply using the login session and character session
$playerName = $_SESSION['login'];
$charName = $_SESSION['char'];

//Set Variables which correspond with the character that is in session (town name, level, class, etc.)
$charDetails = getCharDetails();
$townId = $charDetails['town_id'];
$townName = Towns::getTownNameById($townId);
$charLevel = $charDetails['level'];
$charClass = $charDetails['class'];

$townDetails = getTownDetails($townName);
$previousReady = $townDetails['readyResidents'];
$maxReady = $townDetails['maxResidents'];
$dayNumber = $townDetails['dayNumber'];
$deadRes = $townDetails['deadResidents'];
$defence = $townDetails['defenceSize'];

?>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="mainDesignTown.css">	
    </head>

    <body>

        <div class="Container">

<?php include("../universal/header.php"); ?>


            <!-- PHP draws level requirements, and checks database for current stats of character -->
<?php
include ("../data/levelReq.php");
$Query3 = "SELECT * FROM `characters` WHERE `username` = '$playerName' AND `Character` = '$charName'";
$Query4 = mysqli_query($con, $Query3);
while ($row = mysqli_fetch_assoc($Query4)) {
    $currentLevel = $row['level'];
    $nextLevel = $currentLevel + 1;
    $currentXp = $row['experience'];
    $neededXp = $xpReq [0];

    for ($level = 1; $level < $nextLevel; $level++) {
        $neededXp = $neededXp * $xpReq[1];
    }


    echo "
			<div class='centralBox'>
			<p style='float:left;'>Level " . $currentLevel . "</p> <p style='float:right;'>Level " . $nextLevel . "</p>
			<progress value='" . $currentXp . "' max='" . $neededXp . "' style='width:100%;color:light-blue;'></progress>
			<p style='float:left;'>" . $currentXp . " exp</p> <p style='float:right;'>" . $neededXp . " exp</p>
			</div>";
}
?>

            <!-- <div class='centralBox'>
                <form action='.?locat=character' method='post' name='end'>
                    <input hidden value='end' name='endDay'>
                    <input type='button' id='endButton' onclick='verify()' value='End Day'>
                </form>
            </div> -->



        </div>
            <?php
            Include ("../universal/hyperlinks.php");
            ?>
    </body>

</html>