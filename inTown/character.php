<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");
require_once ("../data/buildings.php");
require_once ("../data/items.php");
require_once ("../functions/queryFunctions.php");
require_once ("../model/structures.php");
require_once ("../model/database.php");
include ("../data/levelReq.php");

$errorMessage = FILTER_INPUT(INPUT_GET, 'e');
if (isset($errorMessage)) {
    echo "<script type='text/javascript'>alert('$errorMessage');</script>";
}

//All information here is retrieved from database simply using the login session and character session
$playerName = $_SESSION['login'];
$userId = $_SESSION['user_id'];
$charName = $_SESSION['char'];
$charId = $_SESSION['char_id'];

//Set Variables which correspond with the character that is in session (town name, level, class, etc.)
$townName = Towns::getTownNameById($townId);
$characterObject = new Character($charId);
$townId = $characterObject->townId;
$charClass = $characterObject->class;
$activeSkills = $characterObject->getActiveSkills();
$usedSkillPoints = $characterObject->getUsedSkillPoints();

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
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="../js/character.js" type="text/javascript"></script>
    </head>

    <body>

        <div class="Container">

<?php include("../universal/header.php"); ?>


            <!-- PHP draws level requirements, and checks database for current stats of character -->
<?php

$charStats = new CharStats($charId);

// $dbCon = Database::getDB();
// $query = "SELECT * FROM `characters` WHERE `username` = :username AND `id` = :id";
// $statement = $dbCon->prepare($query);
// $statement->bindValue(":username", $playerName);
// $statement->bindValue(":id", $charId);
// $statement->execute();
// $row = $statement->fetch();
// $statement->closeCursor();

    $currentLevel = $charStats->getLevel();
    $nextLevel = $currentLevel + 1;
    $currentXp = $charStats->getLegacyXp(); /** This value is the current permanent XP the character has (from previous towns) */
    $xpToBeEarned = $charStats->getBonusXp(); /* This value is the the Bonus XP that the character has earned in the new town that will be applied to the character when the town ends*/
    $neededXp = getRequiredXp($currentLevel);

    $percentage = ($currentXp / $neededXp) * 100;
    echo "<div class='centralBox'>" .
        "<div style='width: 100%; height: 30px;'><p style='float:left;'><b>Level " . $currentLevel . "</b></p> <p style='float:right;'>Level " . $nextLevel . "</p></div>" .
        "<div class='w3-light-grey w3-round-large'><div class='w3-container w3-blue w3-round-large w3-center' id='xpBar' style='width:" . $percentage . "%;'>". (round($percentage)) ."%</div></div>" .
        "<div style='width: 100%; height: 30px;'><p style='float:left;'>" . $currentXp . " exp</p> <p style='float:right;'>" . $neededXp . " exp</p></div>";
    
    $percentageSkills = ($usedSkillPoints / $currentLevel) * 100;
    echo "<div class='w3-light-grey w3-round'><div class='w3-container w3-amber w3-round w3-center' id='spBar' style='width:". $percentageSkills ."%'>". round($percentageSkills) ."%</div></div>";
    echo "<div style='text-align: center; width: 100%; height: 22px;'><h4><span id='usedSkillPoints'>" . $usedSkillPoints . "</span>/" . $currentLevel . " Points Assigned</h4></div>";

?>  <div class="skillsBox">
    <div class="skillsUnlocked">
        <table class="cinereousTable">
            <thead><tr><th>All Skills</th></tr></thead>
            <tbody id="allSkills">
                <?php 
                if($activeSkills != NULL){
                    if(count($activeSkills) >= 5){
                        $buttonText = "NA";
                    }
                    else{
                        $buttonText = "Assign";
                    }
                }
                else{
                    $buttonText = "Assign";
                }

                $userObject = new User($userId);
                $arrayOfSkills = $userObject->getListOfSkills();
                foreach($arrayOfSkills as $current){
                    $currentSkill = new Skill($current);
                    if(!$characterObject->isSkillActive($current)){
                        if($currentSkill->getCost() <= ($currentLevel - $usedSkillPoints)){
                            echo "<tr>" .
                            "<td class='pointer pointerWhite' id='skill_" . $current . "' onclick='skillAjax(" . $current . ", this, `" . $buttonText . "`)'>" . str_replace("_", " ", $currentSkill->getName()) . " <div class='numberCircle'>" . $currentSkill->getCost() . "</div></td>" .
                            "</tr>";
                        }
                        else{
                            echo "<tr>" .
                            "<td class='pointer pointerRed' onclick='skillAjax(" . $current . ", this, `NA`)'>" . str_replace("_", " ", $currentSkill->getName()) . " <div class='numberCircle'>" . $currentSkill->getCost() . "</div></td>" .
                            "</tr>";
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="middleSkills">
        <div class="skillDescription">
            <p id="skillName">Select A Skill</p>
            <p id="skillDescription"></p>
            <button class="skill_button" id="skill_button" style="display:none" onclick='modifySkillsAjax()'></button>
        </div>
    </div>
    <div class="skillsSelected">
        <table class="cinereousTable" id="skillsAssigned">
            <thead><tr><th>Active Skills</th></tr></thead>
            <tbody>
                <?php
                if($activeSkills != NULL){
                    foreach ($activeSkills as $key => $current){
                        echo "<tr id='skill" . $key . "'>" .
                        "<td class='pointer pointerGreen' id='skill_" . $current->id . "' onclick='skillAjax(" . $current->id . ", this, `Remove`)'>" . str_replace("_", " ", $current->getName()) . " <div class='numberCircle'>" . $current->getCost() . "</div></td>" .
                        "</tr>";
                    }
                    if (count($activeSkills) < 5){
                        //add empty slots to table
                        for ($i = count($activeSkills); $i < 5; $i ++){
                            echo "<tr id='skill" . $i . "'><td>Empty Skill Slot</td></tr>";
                        }
                    }
                }
                else{
                    echo "<tr id='skill0'><td>Empty Skill Slot</td></tr>" .
                    "<tr id='skill1'><td>Empty Skill Slot</td></tr>" .
                    "<tr id='skill2'><td>Empty Skill Slot</td></tr>" .
                    "<tr id='skill3'><td>Empty Skill Slot</td></tr>" .
                    "<tr id='skill4'><td>Empty Skill Slot</td></tr>";
                }
                ?>

            </tbody>
        </table>
    </div>
    </div>
        </div>
        </div>
            <?php
            Include ("../universal/hyperlinks.php");
            ?>
    </body>

</html>