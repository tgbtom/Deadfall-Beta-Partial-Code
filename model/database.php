<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of database
 *
 * @author Dingo
 */
class Database {
    private static $user = 'root';
    private static $pass = '';
    private static $dsn = 'mysql:host=localhost;dbname=deadfall';
    private static $db;
    
    public static function getDB()
    {
        if (!isset(self::$db))
        {
            try 
            {
                self::$db = new PDO(self::$dsn, self::$user, self::$pass);
            }
            catch (PDOException $e)
            {
                $error_message = $e->getMessage();
                exit();
            }
        }
        return self::$db;    
    }
    
    public static function sendQuery($queryString)
    {
        $dbCon = self::getDB();
        
        $statement = $dbCon->prepare($queryString);
        $statement->execute();
        $statement->closeCursor();
    }
}

class Towns {

    public $town_id, $townName, $amountResidents, $maxResidents, $readyResidents, $deadResidents, $townFull, $buildings, $bulletin, $hordeSize, $defenceSize, $dayNumber;

    public function __construct($town_id){

        $dbCon = Database::getDB();

        $query = "SELECT * FROM `towns` WHERE `town_id` = :townId";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":townId", $townId);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        $this->town_id = $town_id;
        $this->townName = $result["townName"];
        $this->amountResidents = $result["amountResidents"];
        $this->maxResidents = $result["maxResidents"];
        $this->readyResidents = $result["readyResidents"];
        $this->deadResidents = $result["deadResidents"];
        $this->townFull = $result["townFull"];
        $this->buildings = $result["buildings"];
        $this->bulletin = $result["bulletin"];
        $this->hordeSize = $result["hordeSize"];
        $this->defenceSize = $result["defenceSize"];
        $this->dayNumber = $result["dayNumber"];
    }
    
    public static function isTownCreated($townId){
        $dbCon = Database::getDB();
        
        $query = "SELECT `town_id` FROM `towns`";
        $statement = $dbCon->prepare($query);
        $statement->execute();
        $results = $statement->fetchAll();
        
        foreach ($results as $result){
            if ($result["town_id"] == $townId){
                return true;
            }
        }
        return false;
    }

    public static function getTownNameById($townId){

        $dbCon = Database::getDB();

        $query = "SELECT `townName` FROM `towns` WHERE `town_id` = :townId";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':townId', $townId);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        return $result['townName'];

    }

    public static function getTownTableName($townId){
        $dbCon = Database::getDB();

        $query = "SELECT `townName` FROM `towns` WHERE `town_id` = :townId";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':townId', $townId);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        return $townId . "_" . $result['townName'];
    }

    public static function addTownBulletin($content, $townId){
        $dbCon = Database::getDB();

        //Get current bulletin, to concatenate new bulletin to
        $query = "SELECT `bulletin` FROM `towns` WHERE `town_id` = :townId";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":townId", $townId);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        $oldBulletin = $result['bulletin'];
        $newBulletin = $oldBulletin . "." . $content;

        //Update the bulletin to include the new content
        $query2 = "UPDATE `towns` SET `bulletin` = :bulletin WHERE `town_id` = :townId";
        $statement2 = $dbCon->prepare($query2);
        $statement2->bindValue(":bulletin", $newBulletin);
        $statement2->bindValue(":townId", $townId);
        $statement2->execute();
        $statement2->closeCursor();
    }

    public static function calculateDailyDangerValues($townId){
        $dbCon = Database::getDB();

        $townTableName = self::getTownTableName($townId);
        $query = "SELECT * FROM " . $townTableName;
        $statement = $dbCon->prepare($query);
        $statement->execute();
        $results = $statement->fetchAll();
        $statement->closeCursor();

        foreach ($results as $result){
            $dangerValue = getDangerLevel($result['x'], $result['y'], $townId);
            $dangerQuery = "UPDATE `" . $townTableName . "` SET `danger_value` = :danger WHERE `id` = :currentId";
            $dangerStatement = $dbCon->prepare($dangerQuery);
            $dangerStatement->bindValue(":danger", $dangerValue);
            $dangerStatement->bindValue(":currentId", $result['id']);
            $dangerStatement->execute();
            $dangerStatement->closeCursor();
        }
    }

    /*
    public static function getDangerValue($x, $y, $townId){
        $dbCon = Database::getDB();
        $townTableName = self::getTownTableName($townId);

        $query = "SELECT * FROM " . $townTableName . " WHERE `x` = :x AND `y` = :y";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':x', $x);
        $statement->bindValue(':y', $y);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        return $result['danger_value'];
    } 

    public static function lowerDangerValue($x, $y, $townId, $amountToReduce){
        $currentDanger = self::getDangerValue($x, $y, $townId);
        $newDanger = $currentDanger - $amountToReduce;

        $dbCon = Database::getDB();
        $townTableName = self::getTownTableName($townId);

        $query = "UPDATE " . $townTableName . " SET `danger_value` = :danger WHERE `x` = :x AND `y` = :y";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':danger', $newDanger);
        $statement->bindValue(':x', $x);
        $statement->bindValue(':y', $y);
        $statement->execute();
        $statement->closeCursor();
    }

    public static function increaseDangerValue($x, $y, $townId, $amountToAdd){
        $currentDanger = self::getDangerValue($x, $y, $townId);
        $newDanger = $currentDanger + $amountToAdd;

        $dbCon = Database::getDB();
        $townTableName = self::getTownTableName($townId);

        $query = "UPDATE " . $townTableName . " SET `danger_value` = :danger WHERE `x` = :x AND `y` = :y";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':danger', $newDanger);
        $statement->bindValue(':x', $x);
        $statement->bindValue(':y', $y);
        $statement->execute();
        $statement->closeCursor();
    }

    
    public static function getControlPoints($x, $y, $townId){
        $dbCon = Database::getDB();
        $townTableName = self::getTownTableName($townId);

        $query = "SELECT * FROM " . $townTableName . " WHERE `x` = :x AND `y` = :y";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':x', $x);
        $statement->bindValue(':y', $y);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        return $result['control_points'];
    }

    public static function increaseControlPoints($x, $y, $townId, $amountToAdd){
        $currentControl = self::getControlPoints($x, $y, $townId);
        $newControl = $currentControl + $amountToAdd;

        $dbCon = Database::getDB();
        $townTableName = self::getTownTableName($townId);

        $query = "UPDATE " . $townTableName . " SET `control_points` = :control WHERE `x` = :x AND `y` = :y";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':control', $newControl);
        $statement->bindValue(':x', $x);
        $statement->bindValue(':y', $y);
        $statement->execute();
        $statement->closeCursor();
    }*/
}

class Character {

    public $id, $username, $character, $class, $gender, $level, $exp, $townId, $items, $itemsMass, $bonusItems, $maxBonusItems, $currentAP, $maxAP, $status;
    
    public function __construct($characterId){
        $this->id = $characterId;

        $dbCon = Database::getDB();
        $query = "SELECT * FROM `characters` WHERE `id` = :id";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":id", $characterId);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        $this->username = $result["username"];
        $this->character = $result["character"];
        $this->class = $result["class"];
        $this->gender = $result["gender"];
        $this->level = $result["level"];
        $this->exp = $result["experience"];
        $this->townId = $result["town_id"];
        $this->items = $result["items"];
        $this->itemsMass = $result["itemsMass"];
        $this->bonusItems = $result["bonusItems"];
        $this->maxBonusItems = $result["maxBonusItems"];
        $this->currentAP = $result["currentAP"];
        $this->maxAP = $result["maxAP"];
        $this->status = $result["status"];
    }

    public static function getCharacterById($id){

        $dbCon = Database::getDB();
        $query = "SELECT * FROM `characters` WHERE `id` = :id";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        return $result; 
    }

    public static function getSequentialCharacter($currentCharacter, $dir){
        //DIR should be 'next' or 'prev', sorted by ID
        
        $stopNextId = false;

        $dbCon = Database::getDB();

        $query = "SELECT * FROM `characters` WHERE `username` = :username AND `town_id` = :townId ORDER BY `id`";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':username', $currentCharacter->username);
        $statement->bindValue(':townId', $currentCharacter->townId);
        $statement->execute();
        $result = $statement->fetchAll();
        $statement->closeCursor();

        if($dir == "next"){
            foreach($result as $key => $current){
                
                if($current["id"] == $currentCharacter->id){
                    echo "<script>console.log('NEXT')</script>";
                    $stopNextId = true;
                    if($key == count($result) - 1){
                        //Last char, so go to first char
                        return new Character($result[0]["id"]);
                    }
                    continue;
                }
                elseif($stopNextId == true){
                    echo "<script>console.log('NEXT2')</script>";
                    return new Character($current["id"]);
                    exit;
                }
            }
        }
        elseif($dir == "prev"){
            $previousId = -1;
            if(count($result) == 1){
                return new Character($currentCharacter->id);
            }
            else{
                foreach($result as $key => $current){
                    if($previousId != -1)
                    {
                        if($current["id"] == $currentCharacter->id){
                            return new Character($previousId);
                        }
                    }
                    elseif($current["id"] == $currentCharacter->id){
                        return new Character($result[count($result) - 1]["id"]);
                    }
                    $previousId = $current["id"];
                }
            }
        }
    }

    private function updateDbStats($columnName, $newValue){
        $dbCon = Database::getDB();
        //query to update stats for this instance of class
        $query = "UPDATE `characters` SET `". $columnName ."` = :newValue WHERE `id` = :charId";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":charId", $this->id);
        $statement->bindValue(":newValue", $newValue);
        $statement->execute();
        $statement->closeCursor();
    }

    public function refillAp(){
        $this->currentAP = $this->maxAP;
        self::updateDbStats("currentAP", $this->currentAP);
    }

}

class User {
    public $userId, $username;

    public static function getNameById($id){
        $dbCon = Database::getDB();
        
        $query = "SELECT * FROM `userstable` WHERE `id` = :id";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":id", $id);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        return $result['username'];
    }
}

Class CharStats {
    public $charId;
    private $zeds_killed, $distance_travelled, $times_looted, $camps_survived, $structure_contributions, $bonus_xp, $day_of_death, $current_xp;
    
    public function __construct($characterId){
        $dbCon = Database::getDB();

        $query = "SELECT * FROM `stats_character` WHERE `char_id` = :charId";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":charId", $characterId);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        $this->charId = $characterId;
        $this->zeds_killed = $result["zeds_killed"];
        $this->distance_travelled = $result["distance_travelled"];
        $this->times_looted = $result["times_looted"];
        $this->camps_survived = $result["camps_survived"];
        $this->structure_contributions = $result["structure_contributions"];
        $this->bonus_xp = $result["bonus_xp"];
        $this->day_of_death = $result["day_of_death"];
        $this->current_xp = $result["current_xp"];
    }

    public static function makeNewCharStats($charId){
        $dbCon = Database::getDB();

        $queryUp = "INSERT INTO `stats_character` VALUES (:charId, '0', '0', '0', '0', '0', '0', '0', '0')";
        $statementUp = $dbCon->prepare($queryUp);
        $statementUp->bindValue(":charId", $charId);
        $statementUp->execute();
        $statementUp->closeCursor();

        $query = "INSERT INTO `stats_character_legacy` VALUES (:charId, '0', '0', '0', '0', '0', '0', '0', '0')";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":charId", $charId);
        $statement->execute();
        $statement->closeCursor();
    } 

    public function getZedsKilled(){
        return $this->zeds_killed;
    }

    public function getTimesLooted(){
        return $this->times_looted;
    }

    private function updateDbStats($columnName, $newValue){
        $dbCon = Database::getDB();
        //query to update stats for this instance of class
        $query = "UPDATE `stats_character` SET `". $columnName ."` = :newValue WHERE `char_id` = :charId";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":charId", $this->charId);
        $statement->bindValue(":newValue", $newValue);
        $statement->execute();
        $statement->closeCursor();
    }

    /**Transfer all character temporary stats to the legacy table and clear the temporary stats to '0'.
     * Allow recounting of stats when the character joins a new town
     */
    public function transferAllToLegacy(){
        $dbCon = Database::getDB();

        $query = "SELECT * FROM `stats_character` WHERE `char_id` = :charId";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":charId", $this->charId);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        $queryLegacy = "SELECT * FROM `stats_character_legacy` WHERE `char_id` = :charId";
        $statement = $dbCon->prepare($queryLegacy);
        $statement->bindValue(":charId", $this->charId);
        $statement->execute();
        $resultLegacy = $statement->fetch();
        $statement->closeCursor();

        $query = "UPDATE `stats_character_legacy` SET ('zeds_killed', 'distance_travelled', 'times_looted', 'structure_contributions', 'camps_survived') VALUES (?, ?, ?, ?, ?)";
        $statement->bindValue(1, $resultLegacy["zeds_killed"] + $result["zeds_killed"]);
        $statement->bindValue(2, $resultLegacy["distance_travelled"] + $result["distance_travelled"]);
        $statement->bindValue(3, $resultLegacy["times_looted"] + $result["times_looted"]);
        $statement->bindValue(4, $resultLegacy["structure_contributions"] + $result["structure_contributions"]);
        $statement->bindValue(5, $resultLegacy["camps_survived"] + $result["camps_survived"]);
        $statement->execute();
        $statement->closeCursor();
    }

    public function modifyKilledZeds($kills){
        $this->zeds_killed += $kills;
        self::updateDbStats("zeds_killed", $this->zeds_killed);
    }

    public function  modifyTimesLooted($loots){
        $this->times_looted += $loots;
        self::updateDbStats("times_looted", $this->times_looted);
    }

    public function modifyStructureContributions($amountContributed){
        $this->structure_contributions += $amountContributed;
        self::updateDbStats("structure_contributions", $this->structure_contributions);
    }

    public function setDayOfDeath($dayNum){
        $this->day_of_death = $dayNum;
        self::updateDbStats("day_of_death", $this->day_of_death);
    }

    public function addCampSurvived(){
        $this->camps_survived += 1;
        self::updateDbStats("camps_survived", $this->camps_survived);
    }

    public function addDistanceTravelled(){
        $this->distance_travelled += 1;
        self::updateDbStats("distance_travelled", $this->distance_travelled);
    }
}



Class TownStats {
    public $town_id;
    private $town_name, $defence_by_day, $horde_by_day, $deaths_by_day, $zeds_killed, $times_looted, $structure_levels, $created_by_user; 

    public function __construct($townId){
        $dbCon = Database::getDB();

        $query = "SELECT * FROM `stats_town_legacy` WHERE `town_id` = :townId";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":townId", $townId);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        $this->town_id = $townId;
        $this->town_name = $result["town_name"];
        $this->defence_by_day = $result["defence_by_day"];
        $this->horde_by_day = $result["horde_by_day"];
        $this->deaths_by_day = $result["deaths_by_day"];
        $this->zeds_killed = $result["zeds_killed"];
        $this->times_looted = $result["times_looted"];
        $this->structure_levels = $result["structure_levels"];
        $this->created_by_user = $result["created_by_user"];
    }

    public static function createNewTownStats($townId, $townName, $created_by){
        $dbCon = Database::getDB();

        $queryUp = "INSERT INTO `stats_town_legacy` VALUES (:townId, :townName, '', '', '', '0', '0', '0', :createdBy)";
        $statementUp = $dbCon->prepare($queryUp);
        $statementUp->bindValue(":townId", $townId);
        $statementUp->bindValue(":townName", $townName);
        $statementUp->bindValue(":createdBy", $created_by);
        $statementUp->execute();
        $statementUp->closeCursor();
    }

    private function updateDbStats($columnName, $newValue){
        $dbCon = Database::getDB();
        //query to update stats for this instance of class
        $query = "UPDATE `stats_town_legacy` SET `". $columnName ."` = :newValue WHERE `town_id` = :townId";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":townId", $this->town_id);
        $statement->bindValue(":newValue", $newValue);
        $statement->execute();
        $statement->closeCursor();
    }

    //Add defence, deaths, and horde size to daily amounts. Compiles all to a list to allow creation of charts with data
    public function addDayStats($defenceToday, $hordeToday, $deathsToday){
        if($this->defence_by_day == ""){
            $concat = "";
        } else{
            $concat = ".";
        }
        $this->defence_by_day .= $concat . $defenceToday;
        $this->horde_by_day .= $concat . $hordeToday;
        $this->deaths_by_day .= $concat . $deathsToday;

        self::updateDbStats("defence_by_day", $this->defence_by_day);
        self::updateDbStats("horde_by_day", $this->horde_by_day);
        self::updateDbStats("deaths_by_day", $this->deaths_by_day);
    }

    /**Add 1 to the town stat for 'built structure levels */
    public function addStructureLevel(){
        $this->structure_levels += 1;
        self::updateDbStats("structure_levels", $this->structure_levels);
    }

    public function addZedsKilled($amount){
        $this->zeds_killed += $amount;
        self::updateDbStats("zeds_killed", $this->zeds_killed);
    }

    public function addTimesLooted($amount){
        $this->times_looted += $amount;
        self::updateDbStats("times_looted", $this->times_looted);
    }
}