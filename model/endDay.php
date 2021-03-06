<?php 
require_once ("../data/buildings.php");
require_once ("../data/items.php");
require_once ("../functions/queryFunctions.php");
require_once ("database.php");

//All information here is retrieved from database simply using the login session and character session
$playerName = $_SESSION['login'];
$charName = $_SESSION['char'];
$charId = $_SESSION['char_id'];

//Set Variables which correspond with the character that is in session (town name, level, class, etc.)
$charDetails = getCharDetails();
$townId = $charDetails['town_id'];
$townName = Towns::getTownNameById($townId);
$charLevel = $charDetails['level'];
$charClass = $charDetails['class'];

$townDetails = getTownDetails($townId);
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
    global $townId;
    global $deadRes;
    global $defence;

    $newReady = $previousReady + 1;
    $newDead = $deadRes;

    if ($newReady >= ($maxReady - $deadRes)) {
        //If there is overrun
        $oldHorde = getHordeSize($townId);
        if ($oldHorde > $defence) {
            $overrun = $oldHorde - $defence;
            for ($i = 0; $i < $overrun; $i++) {
                if (mt_rand(0, 100) >= 95) { //5-6% chance of random survivor being killed
                    //Kill random Character
                    characterLottery($townId, $newDead);
                }
            }
        }


        //Remove '10' from all characters status' if they are in the town.
        $query = 'SELECT * FROM `characters` WHERE `town_id` = :townId ';
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':townId', $townId);
        $statement->execute();
        $result = $statement->fetchAll();
        $statement->closeCursor();

        foreach ($result as $current) {
            $character = $current['character'];
            $characterId = $current['id'];
            $currentUsername = $current["username"];

            if (!doesStatusContainExt(12, $characterId, $currentUsername)) { //If character is not dead, reset ATE/DRANK/DAY ENDED
                replaceStatusExt(10, 11, $characterId);
                //Remove status for ATE/DRANK
                replaceStatusExt(0, NULL, $characterId);
                replaceStatusExt(1, NULL, $characterId);
            }
            if ($dayNumber % 2 == 0) { //If day is an even number, change hunger 
                if (doesStatusContainExt(2, $characterId)) { //character WAS FULL
                    replaceStatusExt(2, 3, $characterId);
                } elseif (doesStatusContainExt(3, $characterId)) { //character WAS HUNGRY
                    replaceStatusExt(3, 4, $characterId);
                } elseif (doesStatusContainExt(4, $characterId)) { //character WAS VERY HUNGRY
                    replaceStatusExt(4, 5, $characterId);
                } elseif (doesStatusContainExt(5, $characterId)) { //character WAS STARVING
                    //***************************CHARACTER DIES HERE*****************************************
                    if (!doesStatusContainExt(12, $characterId)) {
                        killCharacter($characterId, $currentUsername, $newDead);
                        $deathBulletin = "<red>" . $character . " starved to death</red>";
                        Towns::addTownBulletin($deathBulletin, $townId);
                    }
                }
            }

            if (doesStatusContainExt(6, $characterId)) { //character WAS QUENCHED
                replaceStatusExt(6, 7, $characterId);
            } elseif (doesStatusContainExt(7, $characterId)) { //character WAS THIRSTY
                replaceStatusExt(7, 8, $characterId);
            } elseif (doesStatusContainExt(8, $characterId)) { //character WAS VERY THIRSTY
                replaceStatusExt(8, 9, $characterId);
            } elseif (doesStatusContainExt(9, $characterId)) { //character WAS DEHYDRATED
                //***************************CHARACTER DIES HERE*****************************************
                if (!doesStatusContainExt(12, $characterId)) {
                    killCharacter($characterId, $currentUsername, $newDead);
                    $deathBulletin = "<red>" . $character . " died of dehydration</red>";
                    Towns::addTownBulletin($deathBulletin, $townId); 
                }
            }

            //Check for death of any characters that spent the night outside (60% chance to die)
            $charLocation = getCharCoordsExt($characterId);
            if($charLocation[0] != 0 || $charLocation[1] != 0){
                if(mt_rand(0, 100) < 60 && !doesStatusContainExt(12, $characterId)){
                    //Character dies from camping
                    killCharacter($characterId, $currentUsername, $newDead);
                    $deathBulletin = "<red>" . $character . " never returned from outside of town</red>";
                    Towns::addTownBulletin($deathBulletin, $townId);
                }
                else{
                    //track stats that you survived a night camping
                    $charStats = new CharStats($characterId);
                    $charStats->addCampSurvived();
                }
            }

            //replenish AP to full if they are still alive
            if(!doesStatusContainExt(12, $characterId)){
                $characterObject = new Character($characterId);
                $characterObject->refillAp();
            }
        }


        //add running values to town stats for defence, horde, and deaths
        $townStats = new TownStats($townId);
        $townStats->addDayStats($defence, $oldHorde, ($newDead - $deadRes));

        //finish day, increase day number
        $dayNumber++;
        if ($dayNumber >= 5) {
            zedSpread($townId);
        }

        //If there was  an over run, publish results to bulletin
        if (isset($overrun)){
            $notice = "<red><strong>Horde Attack -> Night " . ($dayNumber - 1) . "</strong>: " . $overrun . " Zeds got through the defences and terrorized the citizens</red>";
            Towns::addTownBulletin($notice, $townId);
            $notice = "<red>as a result " . ($newDead - $deadRes) . " Survivors have been killed</red>";
            Towns::addTownBulletin($notice, $townId);
        }
        //otherwise, post notice that you were safe
        else{
            $notice = "<green><strong>Horde Attack</strong>: The defences successfully fended off the horde for the night</green>";
            Towns::addTownBulletin($notice, $townId);
        }

        //increase hordesize
        $newHorde = getHordeSize($townId);

        $query2 = 'UPDATE `towns` SET `readyResidents` = :newReady, `dayNumber` = :dayNumber, `hordeSize` = :newHorde WHERE `town_id` = :townId';
        $statement2 = $dbCon->prepare($query2);
        $statement2->bindValue(':newReady', 0);
        $statement2->bindValue(':dayNumber', $dayNumber);
        $statement2->bindValue(':newHorde', $newHorde);
        $statement2->bindValue(':townId', $townId);
        $statement2->execute();
        $statement2->closeCursor();

        Towns::calculateDailyDangerValues($townId);

        //Check for any structures that perform an action over night
        
        //Add water to bank if the Reserve is Complete
        if (isStructureBuilt('Water Reserve', $townId)) {
            $amount = mt_rand(2,4);
            for ($i = 0; $i < $amount; $i++) {
                addToBank(0, $townId);
            }
            $notice = "<blue>" . $amount . " Water Rations were collected from the Water Reserve</blue>";
            Towns::addTownBulletin($notice, $townId);
        }

        //Add bits of food to the bank if Vegetable Garden is complete
        if (isStructureBuilt('Vegetable Garden', $townId)) {
            $amount = mt_rand(1,4);
            for ($i = 0; $i < $amount; $i++) {
                addToBank(23, $townId);
            }
            $notice = "<blue>" . $amount . " Carrots were collected from the Vegetable Garden</blue>";
            Towns::addTownBulletin($notice, $townId);
        }

    } else {
        $query2 = 'UPDATE `towns` SET `readyResidents` = :newReady WHERE `town_id` = :townId';
        $statement2 = $dbCon->prepare($query2);
        $statement2->bindValue(':newReady', $newReady);
        $statement2->bindValue(':townId', $townId);
        $statement2->execute();
        $statement2->closeCursor();
    }
}

function killCharacter($characterId, $username, &$newDead) {
    
    global $townName;
    global $townId;
    global $dbCon;
    global $dayNumber;

    $charObject = new Character($characterId);
    
    $newDead++;

    $query = 'UPDATE `towns` SET `deadResidents` = :newDead WHERE `town_id` = :townId';
    $statement = $dbCon->prepare($query);
    $statement->bindValue(':townId', $townId);
    $statement->bindValue(':newDead', $newDead);
    $statement->execute();
    $statement->closeCursor();

    addStatusExt(12, $characterId, $username);
    replaceStatusExt(11, 10, $characterId, $username);

    //This function kills the character on the database end
    dropAllItemsExt($characterId);

    //Add a notice of their death to the bulletin
    // $notice = "<red>" . $charObject->character . "[" . $username . "] Has died from zeds</red>";
    // Towns::addTownBulletin($notice, $townId);

    $charStats = new CharStats($characterId);
    $charStats->setDayOfDeath($dayNumber);

    //Check if any chars are left alive in this town, otherwise needs to end town
    if (charsAlive($townId) == 0) {
        //All characters are dead
        closeTheTown($townId);
    }
}

function characterLottery($townId, &$newDead){
    
    global $dbCon;
    global $dayNumber;
    
    //establish the empty array so arrays can be pushed into it (becomes multi-dimensional)
    $lotteryPool = array();
    
    $query = "SELECT * FROM `characters` WHERE `town_id` = :townId";
    $statement = $dbCon->prepare($query);
    $statement->bindValue(":townId", $townId);
    $statement->execute();
    $results = $statement->fetchAll();
    $statement->closeCursor();
    
    //compile an array of character-user combo that are still alive
    //Also only adds characters that are inside town to the lottery
    foreach ($results as $result){
        $currentChar = $result["character"];
        $currentCharId = $result["id"];
        $currentUser = $result["username"];
        $charLocation = getCharCoordsExt($currentCharId);
        if (!doesStatusContainExt(12, $currentCharId, $currentUser) && ($charLocation[0] == '0' && $charLocation[1] == '0')){
            //character is not dead, add him to the pool
            $currentCharCombo = array($currentUser, $currentCharId);
            array_push($lotteryPool, $currentCharCombo);
        }
    }
    
    $lotteryDraw = mt_rand(0, count($lotteryPool) - 1);
    
    $userToKill = $lotteryPool[$lotteryDraw][0];
    $charToKill = $lotteryPool[$lotteryDraw][1];

    killCharacter($charToKill, $userToKill, $newDead);
}

?>