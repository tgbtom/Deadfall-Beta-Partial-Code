<?php
require_once ("database.php");

Class Zone {
    public $townId, $zone_id, $x, $y, $specialStructures, $lootability, $groundItems, $zeds, $lastKnownZed, $charactersHere, $dangerValue, $controlPoints, $bulletin;
    private $townTableName;

    public function __construct($townId, $x, $y){
        $this->townTableName = Towns::getTownTableName($townId);

        $dbCon = Database::getDB();

        $query = "SELECT * FROM `" . $this->townTableName . "` WHERE `x` = :x AND `y` = :y";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":x", $x);
        $statement->bindValue(":y", $y);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        $this->townId = $townId;
        $this->x = $x;
        $this->y = $y;
        $this->zone_id = $result["id"];
        $this->specialStructures = $result["specialStructure"];
        $this->lootability = $result["lootability"];
        $this->groundItems = $result["groundItems"];
        $this->zeds = $result["zeds"];
        $this->lastKnownZed = $result["lastKnownZed"];
        $this->charactersHere = $result["charactersHere"];
        $this->dangerValue = $result["danger_value"];
        $this->controlPoints = $result["control_points"];
        $this->bulletin = $result["bulletin"];
    }

    public function addControlPoints($amountToAdd){
        $dbCon = Database::getDB();
        $newControlPoints = $this->controlPoints + $amountToAdd;

        $query = "UPDATE `" . $this->townTableName . "` SET `control_points` = :controlPoints WHERE `x` = :x AND `y` = :y";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":controlPoints", $newControlPoints);
        $statement->bindValue(":x", $this->x);
        $statement->bindValue(":y", $this->y);
        $statement->execute();
        $statement->closeCursor();
    }
}

?>