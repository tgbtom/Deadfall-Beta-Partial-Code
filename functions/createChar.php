<?php 
require_once ("../connect.php");
require_once ("./verifyLogin.php");
require_once ("../model/database.php");

$selected_class = filter_input(INPUT_POST, 'charClass');
$selected_name = filter_input(INPUT_POST, 'charName');
$selected_gender = filter_input(INPUT_POST, 'gender');


$validPattern = "/[a-zA-Z0-9]{" . strlen($selected_name) . "}/";
$validName = preg_match($validPattern, $selected_name);

//Need to check if the user already has a character by the given name, if they do then don't allow them to create one with that name!
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

$user = $_SESSION['login'];


//Ensure that current user has no more than 20 characters
$dbCon = Database::getDB();
$query = "SELECT * FROM `characters` WHERE `username` = :username";
$statement = $dbCon->prepare($query);
$statement->bindValue(":username", $user);
$statement->execute();
$results = $statement->fetchAll();
$statement->closeCursor();

if(sizeof($results) >= 20){
    //The current user already has 20 or more characters, revert them back to character browsewith this error
    echo '<script>window.location = "' . $root . '/inTown/?locat=browseChars&e=You have exceeded the maximum amount of characters";</script>';
}
else{
    $nameQuer = "SELECT * FROM `characters` WHERE `character`= :selected_char AND `username`= :username";
    $check = $dbCon->prepare($nameQuer);
    $check->bindValue(":selected_char", $selected_name);
    $check->bindValue(":username", $user);
    $check->execute();
    $results = $check->fetchAll();
    $check->closeCursor();
    
    if (sizeof($results) <= 0 && $validName)
    {	
        $query = "INSERT INTO characters 
        (`username`, `character`, `class`, `gender`, `level`, `experience`, `town_id`, `items`, `itemsMass`, `maxItems`, `bonusItems`, `maxBonusItems`, `currentAP`, `maxAP`, `status`) 
        VALUES 
        (:user, :character, :class, :gender, '0', '0', NULL, NULL, '0', '20', 'none', '0', :classAp, :classAp2, '3.7.11')";
    
        //Establish how much AP the character  should  have, based on Class
        switch($selected_class){
            case 'Survivor':
            $classAp = 16;
            break;
    
            case 'Builder':
            $classAp = 12;
            break;
        }
    
        try{
            $statement = $dbCon->prepare($query);
            $statement->bindValue(':user', $user);
            $statement->bindValue(':character', $selected_name);
            $statement->bindValue(':class', $selected_class);
            $statement->bindValue(':gender', $selected_gender);
            $statement->bindValue(':classAp', $classAp);
            $statement->bindValue(':classAp2', $classAp);
            $statement->execute();
    
            $statement->closeCursor();
            echo '<script>window.location = "' . $root . '/inTown/?locat=browseChars";</script>';
        }
        catch(PDOException $e){
            echo "There was a problem while trying to create the character";
            echo $e;
        }
    }
    else
    {	
        echo "Character name is taken OR invalid";
    }
}

?>
<html>
<title>Character Browse/Create</title>
<head>
</head>
<body>
</body>
</html>
