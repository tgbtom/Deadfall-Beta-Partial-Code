<?php

require_once ("model/database.php");

$townName = filter_input(INPUT_GET, "town");

function getTownHistory($townName){

    $dbCon = Database::getDB();
    $query = "SELECT * FROM `towns` WHERE `townName` = :townName";
    $statement = $dbCon->prepare($query);
    $statement->bindValue(":townName", $townName);
    $statement->execute();
    $result = $statement->fetch();
    $statement->closeCursor();

    return $result;
}

$townHistory = getTownHistory($townName);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="mainDesign.css">
    <title><?php echo htmlspecialchars($townName); ?></title>
</head>
<body>
    <div class="container" style="text-align: center;">
        <h1><u>Town Summary for: <?php echo htmlspecialchars($townName); ?></u></h1>

        <!-- Days Survived -->
        <h2>Game Over! :(</h2>
        <h4>Characters survived for <?php echo $townHistory["dayNumber"] - 1; ?> days!</h4>

        <h5>This page is under construction and will have more statistics and information in the future.</h5>

        <a href="inTown/?locat=inTown">Return To Character Select</a>
    </div>
</body>
</html>