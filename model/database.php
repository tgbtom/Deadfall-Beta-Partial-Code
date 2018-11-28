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
