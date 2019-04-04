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
    
    public static function isTownCreated($townName){
        $dbCon = Database::getDB();
        
        $query = "SELECT `townName` FROM `towns`";
        $statement = $dbCon->prepare($query);
        $statement->execute();
        $results = $statement->fetchAll();
        
        foreach ($results as $result){
            if ($result["townName"] == $townName){
                return true;
            }
        }
        return false;
    }

    public static function addTownBulletin($content, $townName){
        $dbCon = Database::getDB();

        //Get current bulletin, to concatenate new bulletin to
        $query = "SELECT `bulletin` FROM `towns` WHERE `townName` = :townName";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":townName", $townName);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        $oldBulletin = $result['bulletin'];
        $newBulletin = $oldBulletin . "." . $content;

        //Update the bulletin to include the new content
        $query2 = "UPDATE `towns` SET `bulletin` = :bulletin WHERE `townName` = :townName";
        $statement2 = $dbCon->prepare($query2);
        $statement2->bindValue(":bulletin", $newBulletin);
        $statement2->bindValue(":townName", $townName);
        $statement2->execute();
        $statement2->closeCursor();
    }
}

class Character {

    public $id, $username, $character, $class, $gender, $level, $exp, $townName, $items, $itemsMass, $bonusItems, $maxBonusItems, $currentAP, $maxAP, $status;
    
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
        $this->townName = $result["townName"];
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

}
