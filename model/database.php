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

}
