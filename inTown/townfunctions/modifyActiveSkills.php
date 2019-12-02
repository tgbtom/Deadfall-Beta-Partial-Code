<?php 
require_once ("../../model/database.php");

session_start();
//gets the user and current character, and stores them in local variables
if (isset($_SESSION['login'])){
    $user = $_SESSION['login'];
    $userId = $_SESSION['user_id'];
}

if (!isset($_SESSION['char_id']))
{
	$_SESSION['char'] = '';
	$_SESSION['char_id'] = '';
}
else
{
	$char = $_SESSION['char'];
	$charId = $_SESSION['char_id'];
}

$returnArray = [];

$skillId = filter_input(INPUT_POST, "skillid");
$skillObject = new Skill($skillId);
$skillCost = $skillObject->getCost();

$characterObject = new Character($charId);
$townId = $characterObject->townId;
$charStats = new CharStats($charId);
$userObject = new User($userId);
$characterSkills = $characterObject->getActiveSkills();

$usedSkillPoints = $characterObject->getUsedSkillPoints();
$maximumSkillPoints = $charStats->getLevel();

if($characterObject->getActiveSkills() != NULL){
    $amountOfSkills = count($characterObject->getActiveSkills());
}
else{
    $amountOfSkills = 0;
}


    if(!($characterObject->isSkillActive($skillId))){
        if($userObject->isSkillUnlocked($skillId)){
            if($maximumSkillPoints - $usedSkillPoints >= $skillCost){
                //affordable
                if($amountOfSkills < 5){
                    //free space
                    //add skill to nearest empty slot
                    if($characterObject->addSkill($skillId)){
                    $newUsedSkillPoints = $characterObject->getUsedSkillPoints();
                    $returnArray["UsedPoints"] = $newUsedSkillPoints;
                    $returnArray["MaxPoints"] = $maximumSkillPoints;
                    $returnArray["SkillId"] = $skillId;
                    $returnArray["NewSlot"] =  $amountOfSkills;
                    $returnArray["SkillName"] = str_replace("_", " ", $skillObject->getName());
                    $returnArray["SkillCost"] = $skillCost;
                    $returnArray["Direction"] = "Assign";
                    }
                }
            }
        }
    }
    else if($townId == NULL){
        //Skill is already active on the character, attempt to remove instead
            $returnArray["EmptiedSlot"] = $characterObject->removeSkill($skillId);
            $newUsedSkillPoints = $characterObject->getUsedSkillPoints();
            $returnArray["UsedPoints"] = $newUsedSkillPoints;
            $returnArray["MaxPoints"] = $maximumSkillPoints;
            $returnArray["SkillId"] = $skillId;
            $returnArray["SkillName"] = str_replace("_", " ", $skillObject->getName());
            $returnArray["SkillCost"] = $skillCost;
            $returnArray["Direction"] = "Remove";
    }
    else{
        $returnArray = null;   
    }
echo json_encode($returnArray);


//is skill already active on the current character?
//is skill unlocked by current user?
//is Skill Affordable for this character?
//is there a free skill slot?

?>