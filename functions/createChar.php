<?php 
require_once ("../connect.php");
require_once ("./verifyLogin.php");

$selected_class = filter_input(INPUT_POST, 'charClass');
$selected_name = filter_input(INPUT_POST, 'charName');
$selected_gender = filter_input(INPUT_POST, 'gender');


//Need to check if the user already has a character by the given name, if they do then don't allow them to create one with that name!
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

$user = $_SESSION['login'];
$nameQuer = "SELECT * FROM `characters` WHERE `username`='" . $user . "' AND `character`='" . $selected_name . "'";
$check = $dbCon->prepare($nameQuer);
$check->execute();
$results = $check->fetchAll();
$check->closeCursor();

if (sizeof($results) <= 0)
{	
    $query = "INSERT INTO characters 
    (`username`, `character`, `class`, `gender`, `level`, `experience`, `townName`, `items`, `itemsMass`, `maxItems`, `bonusItems`, `maxBonusItems`, `currentAP`, `maxAP`, `status`) 
    VALUES 
    (:user, :character, :class, :gender, '0', '0', 'none', NULL, '0', '20', 'none', '0', '16', '16', '3.7.11')";

    try{
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':user', $user);
        $statement->bindValue(':character', $selected_name);
        $statement->bindValue(':class', $selected_class);
        $statement->bindValue(':gender', $selected_gender);
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
    echo "Character name is taken";
}

?>
<html>
<title>Character Browse/Create</title>
<head>
</head>
<body>
</body>
</html>
