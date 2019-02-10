<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");
Include ("../data/buildings.php");
Include ("../data/items.php");
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
$townName = $charDetails['townName'];
$charLevel = $charDetails['level'];
$charClass = $charDetails['class'];

$townDetails = getTownDetails($townName);
$previousReady = $townDetails['readyResidents'];
$maxReady = $townDetails['maxResidents'];
$dayNumber = $townDetails['dayNumber'];
$deadRes = $townDetails['deadResidents'];
$defence = $townDetails['defenceSize'];

function endDay() {
    global $dbCon;
    global $previousReady;
    global $maxReady;
    global $dayNumber;
    global $townName;
    global $deadRes;
    global $defence;

    $newReady = $previousReady + 1;
    $newDead = $deadRes;

    if ($newReady >= ($maxReady - $deadRes)) {
        //If there is overrun
        $oldHorde = getHordeSize($townName);
        if ($oldHorde > $defence) {
            $overrun = $oldHorde - $defence;
            for ($i = 0; $i < $overrun; $i++) {
                if (mt_rand(0, 100) >= 95) { //5-6% chance of random survivor being killed
                    //Kill random Character
                    characterLottery($townName, $newDead);
                }
            }
        }


        //Remove '10' from all characters status' if they are in the town.
        $query = 'SELECT * FROM `characters` WHERE `townName` = :townName';
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':townName', $townName);
        $statement->execute();
        $result = $statement->fetchAll();
        $statement->closeCursor();

        foreach ($result as $current) {
            $character = $current['character'];
            $currentUsername = $current["username"];

            if (!doesStatusContainExt(12, $character, $currentUsername)) { //If character is not dead, reset ATE/DRANK/DAY ENDED
                replaceStatusExt(10, 11, $character);
                //Remove status for ATE/DRANK
                replaceStatusExt(0, NULL, $character);
                replaceStatusExt(1, NULL, $character);
            }
            if ($dayNumber % 2 == 0) { //If day is an even number, change hunger 
                if (doesStatusContainExt(2, $character)) { //character WAS FULL
                    replaceStatusExt(2, 3, $character);
                } elseif (doesStatusContainExt(3, $character)) { //character WAS HUNGRY
                    replaceStatusExt(3, 4, $character);
                } elseif (doesStatusContainExt(4, $character)) { //character WAS VERY HUNGRY
                    replaceStatusExt(4, 5, $character);
                } elseif (doesStatusContainExt(5, $character)) { //character WAS STARVING
                    //***************************CHARACTER DIES HERE*****************************************
                    if (!doesStatusContainExt(12, $character)) {
                        killCharacter($character, $currentUsername, $newDead);
                        $deathBulletin = $character . " starved to death.";
                        Towns::addTownBulletin($deathBulletin, $townName);
                    }
                }
            }

            if (doesStatusContainExt(6, $character)) { //character WAS QUENCHED
                replaceStatusExt(6, 7, $character);
            } elseif (doesStatusContainExt(7, $character)) { //character WAS THIRSTY
                replaceStatusExt(7, 8, $character);
            } elseif (doesStatusContainExt(8, $character)) { //character WAS VERY THIRSTY
                replaceStatusExt(8, 9, $character);
            } elseif (doesStatusContainExt(9, $character)) { //character WAS DEHYDRATED
                //***************************CHARACTER DIES HERE*****************************************
                if (!doesStatusContainExt(12, $character)) {
                    killCharacter($character, $currentUsername, $newDead);
                    $deathBulletin = $character . " died of dehydration.";
                    Towns::addTownBulletin($deathBulletin, $townName);
                }
            }
        }

        //finish day, increase day number
        $dayNumber++;
        if ($dayNumber >= 5) {
            zedSpread($townName);
        }

        //increase hordesize
        $newHorde = getHordeSize($townName);

        $query2 = 'UPDATE `towns` SET `readyResidents` = :newReady, `dayNumber` = :dayNumber, `hordeSize` = :newHorde WHERE `townName` = :townName';
        $statement2 = $dbCon->prepare($query2);
        $statement2->bindValue(':newReady', 0);
        $statement2->bindValue(':dayNumber', $dayNumber);
        $statement2->bindValue(':newHorde', $newHorde);
        $statement2->bindValue(':townName', $townName);
        $statement2->execute();
        $statement2->closeCursor();


        //Check for any structures that perform an action over night
        
        //Add 2 water to bank if the Reserve is Complete
        if (isStructureBuilt('Water Reserve', $townName)) {
            for ($i = 0; $i < 2; $i++) {
                addToBank(0, $townName);
            }
        }

        //Add 2 bits of food to the bank if Vegetable Garden is complete
        if (isStructureBuilt('Vegetable Garden', $townName)) {
            for ($i = 0; $i < 2; $i++) {
                addToBank(1, $townName);
            }
        }

    } else {
        $query2 = 'UPDATE `towns` SET `readyResidents` = :newReady WHERE `townName` = :townName';
        $statement2 = $dbCon->prepare($query2);
        $statement2->bindValue(':newReady', $newReady);
        $statement2->bindValue(':townName', $townName);
        $statement2->execute();
        $statement2->closeCursor();
    }
}

function killCharacter($character, $username, &$newDead) {
    
    global $townName;
    global $dbCon;
    
    $newDead++;

    $query = 'UPDATE `towns` SET `deadResidents` = :newDead WHERE `townName` = :townName';
    $statement = $dbCon->prepare($query);
    $statement->bindValue(':townName', $townName);
    $statement->bindValue(':newDead', $newDead);
    $statement->execute();
    $statement->closeCursor();

    addStatusExt(12, $character, $username);
    replaceStatusExt(11, 10, $character, $username);

    //This function kills the character on the database end
    dropAllItemsExt($character);
    //Check if any chars are left alive in this town, otherwise needs to end town
    if (charsAlive($townName) == 0) {
        //All characters are dead
        closeTheTown($townName);
    }
}

function characterLottery($townName, &$newDead){
    
    global $dbCon;
    
    //establish the empty array so arrays can be pushed into it (becomes multi-dimensional)
    $lotteryPool = array();
    
    $query = "SELECT * FROM `characters` WHERE `townName` = :townName";
    $statement = $dbCon->prepare($query);
    $statement->bindValue(":townName", $townName);
    $statement->execute();
    $results = $statement->fetchAll();
    $statement->closeCursor();
    
    //compile an array of character-user combo that are still alive
    foreach ($results as $result){
        $currentChar = $result["character"];
        $currentUser = $result["username"];
        if (!doesStatusContainExt(12, $currentChar, $currentUser)){
            //character is not dead, add him to the pool
            $currentCharCombo = array($currentUser, $currentChar);
            array_push($lotteryPool, $currentCharCombo);
        }
    }
    
    $lotteryDraw = mt_rand(0, count($lotteryPool) - 1);
    
    $userToKill = $lotteryPool[$lotteryDraw][0];
    $charToKill = $lotteryPool[$lotteryDraw][1];

    killCharacter($charToKill, $userToKill, $newDead);

}

$endDay = filter_input(INPUT_POST, 'endDay');
if (isset($endDay)) {
    if ($endDay == 'end') {
        //end the day
        if (doesStatusContain(10)) {
            //Char has already ended the day --> does nothing
        } else {
            replaceStatus(11, 10);
            endDay();
        }
    }
}
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

            <div class='centralBox'>
                <form action='.?locat=character' method='post' name='end'>
                    <input hidden value='end' name='endDay'>
                    <input type='button' id='endButton' onclick='verify()' value='End Day'>
                </form>
            </div>


            <script type="text/javascript">
                function verify()
                {
                    if (confirm('You are done using this character for the in-game day?'))
                    {
                        document.end.submit();
                    }
                }

                function dayAlreadyEnded()
                {
                    endButton.setAttribute('disabled', 'disabled');
                }

            </script>
            <?php
            if (doesStatusContain(10)) {
                //Char has already ended the day
                echo '<script>dayAlreadyEnded();</script>';
            }
            ?>



        </div>
            <?php
            Include ("../universal/hyperlinks.php");
            ?>
    </body>

</html>