<?php
/**
 * Description of structures
 *
 * @author Dingo
 */

class Structure {
    private $s_name, $s_category, $s_requirement, $s_ap_cost, $s_item_costs, $s_defence, $s_max_level, $s_description, $s_indentation;
    
    public function __construct($s_name, $s_category, $s_requirement, $s_ap_cost, $s_item_costs, $s_defence, $s_max_level, $s_description, $s_indentation)
    {
        $this->s_name = $s_name;
        $this->s_category = $s_category;
        $this->s_requirement = $s_requirement;
        $this->s_ap_cost = $s_ap_cost;
        $this->s_item_costs = $s_item_costs;
        $this->s_defence = $s_defence;
        $this->s_max_level = $s_max_level;
        $this->s_description = $s_description;
        $this->s_indentation = $s_indentation;
    }
    
    public function getName()
    {
        return $this->s_name;
    }
    
    public function getCategory()
    {
        return $this->s_category;
    }
    
    public function getRequirement()
    {
        return $this->s_requirement;
    }
    
    public function getApCost()
    {
        return $this->s_ap_cost;
    }
    
    public function getItemCosts_string()
    {
        return $this->s_item_costs;
    }
    
    public function getItemCosts_objects()
    {
        $itemCostObjects = new StructureItemCosts($this->s_item_costs);
        return $itemCostObjects;
    }
    
    public function getDefence()
    {
        return $this->s_defence;
    }
    
    public function getMaxLevel()
    {
        return $this->s_max_level;
    }
    
    public function getDescription()
    {
        return $this->s_description;
    }
    
    public function getIndentation()
    {
        return $this->s_indentation;
    }
}

class StructureItemCosts //Array of Item Cost Objects for a particular structure
{
    private $itemCosts = array();
    
    public function __construct($itemCostsString)
    {
        $itemCostsArray = explode(":", $itemCostsString);
        foreach ($itemCostsArray as $value)
        {
            if ($value != "")
            {
            $currentCost = explode(".", $value);
            $currentId = $currentCost[0];
            $currentAmount = $currentCost[1];
            $this->itemCosts[] = new ItemCost($currentId, $currentAmount);
            }
        }
    }
    
    public function getItemCosts()
    {
        return $this->itemCosts;
    }
}

class ItemCost
{
    private $itemId;
    private $itemAmount;
    
    public function __construct($itemId, $itemAmount)
    {
        $this->itemId = $itemId;
        $this->itemAmount = $itemAmount;
    }
    
    public function getItemId()
    {
        return $this->itemId;
    }
    
    public function getItemAmount()
    {
        return $this->itemAmount;
    }
}

class StructuresDB
{
    public static function getTownStructures($townName)
    {
        $dbCon = Database::getDB();
        
        $query = "SELECT buildings from `towns` WHERE `townName` = :townName";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':townName', $townName);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();
        
        return $result['buildings'];
    }
    
    public static function getBuiltDetails($structure, $townName)
    {
                
        $builtStructures = self::getTownStructures($townName);
        $builtArray = explode(':', $builtStructures); //Defence.0.1  Perimeter Fence.0.0
        
        foreach ($builtArray as $value)
        {
            $currentArray = explode('.', $value);
            $building["Name"] = $currentArray[0];
            $building["Ap"] = $currentArray[1];
            $building["Level"] = $currentArray[2];
            if ($building["Name"] == $structure)
            {
                //Found Building
                return $building;
            }
        }
    }
    
    public static function addAp($structure_object, $apToAdd, $townName)
    {
        $builtStructures = self::getTownStructures($townName);
        $builtArray = explode(':', $builtStructures);
        
        foreach ($builtArray as $key => $value)
        {
            $currentArray = explode('.', $value);
            $building["Name"] = $currentArray[0];
            $building["Ap"] = $currentArray[1];
            $building["Level"] = $currentArray[2];
            if ($building["Name"] == $structure_object->getName())
            {
                $building["Ap"] += $apToAdd;
                if ($building["Ap"] >= $structure_object->getApCost()){
                    $building["Ap"] = 0;
                    $building["Level"] += 1;
                    self::addDefence($structure_object->getDefence(), $townName);
                }
            }
            
            if ($key == 0){
                $newBuildString .= $building["Name"] . "." . $building["Ap"] . "." . $building["Level"];
            }
            else{
                $newBuildString .= ":" . $building["Name"] . "." . $building["Ap"] . "." . $building["Level"];
            }
            
        }
        
        $queryString = "UPDATE towns SET `buildings` = '" . $newBuildString . "' WHERE `townName` = '" . $townName . "'";
        Database::sendQuery($queryString);
    }
    
    public static function isStructureAffordable($structure_object, $townName)
    {   
        //Build an array of item cost objects        
        $itemCosts = $structure_object->getItemCosts_objects();
        foreach ($itemCosts->getItemCosts() as $itemCost)
        {
            $costId = $itemCost->getItemId();
            $costAmount = $itemCost->getItemAmount();
            
            if (TownBankDB::getItemAmount($costId, $townName) < $costAmount)
            {
                 //You do not have enough resources
                return false;
            }
        }
        //If all items are found to be sufficient
        return true;
    }
    
    public static function addDefence($defenceToAdd, $townName){
        $dbCon = Database::getDB();
        
        $query = "SELECT defenceSize FROM `towns` WHERE `townName` = :townName";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(":townName", $townName);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();
        
        $currentDefence = $result["defenceSize"];
        $newDefence = $currentDefence + $defenceToAdd;
        
        $query2 = "UPDATE towns SET `defenceSize` = :defenceSize WHERE `townName` = :townName";
        $statement2 = $dbCon->prepare($query2);
        $statement2->bindValue(":defenceSize", $newDefence);
        $statement2->bindValue(":townName", $townName);
        $statement2->execute();
        $statement->closeCursor();
    }
}

class SpecialStructures 
{
    public static function overnightFunctions($townName){
        $structureStatus = StructuresDB::getBuiltDetails("Mechanical Water Pump", $townName);
        if ($structureStatus["Level"] >= 1){
            TownBankDB::addItem(0, 5, $townName);
        }
    }

    private static function getSpecialStructures($townName){
        $specialStructures = array(
            "Fabrikator Workshop"
        );
        return $specialStructures;
    }

    public static function specialStructuresStatus($townName){
        $dbCon = Database::getDB();
        $specialStructures = self::getSpecialStructures($townName);
        $builtSpecials = array();

        foreach ($specialStructures as $current){
            $currentDetails = StructuresDB::getBuiltDetails($current, $townName);
            $level = $currentDetails["Level"];
            if($level >= 1){
                $builtSpecials[] .= $current;
            }
        }
        return $builtSpecials;
    }

    public static function getHtmlContent($structure, $townName){
        if($structure == "Fabrikator Workshop"){

            $woodAmount = TownBankDB::getItemAmount(2, $townName);
            $metalAmount = TownBankDB::getItemAmount(3, $townName);
            $brickAmount = TownBankDB::getItemAmount(10, $townName);

            $htmlContent = "<table class='specialTable'>
            <tr><th colspan='2'><img src='../images/items/Wood Board.png'> Wood (" . $woodAmount . ") <img src='../images/items/Wood Board.png'></th></tr>
            <form action='' method='post'><tr><td>3 <img src='../images/items/Wood Board.png'> ==> <img src='../images/items/Sheet Metal.png'> 1</td><td><button type='submit' name='specialFunction' value='convertWoodToMetal' class='buildButton'><span>Convert</span></button></td></tr></form>
            <form action='' method='post'><tr><td>3 <img src='../images/items/Wood Board.png'> ==> <img src='../images/items/Brick.png'> 1</td><td><button type='submit' name='specialFunction' value='convertWoodToBrick' class='buildButton'><span>Convert</span></button></td></tr></form>
            <tr><th colspan='2'><img src='../images/items/Sheet Metal.png'> Sheet Metal (" . $metalAmount . ") <img src='../images/items/Sheet Metal.png'></th></tr>
            <form action='' method='post'><tr><td>3 <img src='../images/items/Sheet Metal.png'> ==> <img src='../images/items/Wood Board.png'> 1</td><td><button type='submit' name='specialFunction' value='convertMetalToWood' class='buildButton'><span>Convert</span></button></td></tr></form>
            <form action='' method='post'><tr><td>3 <img src='../images/items/Sheet Metal.png'> ==> <img src='../images/items/Brick.png'> 1</td><td><button type='submit' name='specialFunction' value='convertMetalToBrick' class='buildButton'><span>Convert</span></button></td></tr></form>
            <tr><th colspan='2'><img src='../images/items/Brick.png'> Brick (" . $brickAmount . ") <img src='../images/items/Brick.png'></th></tr>
            <form action='' method='post'><tr><td>3 <img src='../images/items/Brick.png'> ==> <img src='../images/items/Wood Board.png'> 1</td><td><button type='submit' name='specialFunction' value='convertBrickToWood' class='buildButton'><span>Convert</span></button></td></tr></form>
            <form action='' method='post'><tr><td>3 <img src='../images/items/Brick.png'> ==> <img src='../images/items/Sheet Metal.png'> 1</td><td><button type='submit' name='specialFunction' value='convertBrickToMetal' class='buildButton'><span>Convert</span></button></td></tr></form>
            </table>";

            return $htmlContent;
        }
    }

}

class TownBankDB
{
    public static function getItemAmount($itemId, $townName)
    {
        $dbCon = Database::getDB();
        
        //Get list of all items in bank
        $query = "SELECT groundItems FROM `" . $townName . "` WHERE `x` = 0 AND `y` = 0";
        $statement = $dbCon->prepare($query);
        $statement->execute();
        $result = $statement->fetch(); //0.41,1.41,2.3,.4,10.1,3.1,4.1,13.1,6.2,7.1,5.1
        $statement->closeCursor();
        
        $bank = explode(",", $result["groundItems"]);
        foreach ($bank as $value)
        {
            $currentItem = explode(".", $value);
            $currentId = $currentItem[0];
            $currentAmount = $currentItem[1];
            
            if ($currentId == $itemId)
            {
                return $currentAmount;
            }
        }
        
        return 0;
    }
    
    public static function removeItem($itemId, $amountToRemove, $townName)
    {
        $dbCon = Database::getDB();
        
         //Get list of all items in bank
        $query = "SELECT groundItems FROM `" . $townName . "` WHERE `x` = 0 AND `y` = 0";
        $statement = $dbCon->prepare($query);
        $statement->execute();
        $result = $statement->fetch(); //0.41,1.41,2.3,.4,10.1,3.1,4.1,13.1,6.2,7.1,5.1
        $statement->closeCursor();

        $newGroundItems = "";
        
        $bank = explode(",", $result["groundItems"]);
        foreach($bank as $key => $value)
        {
            $currentItem = explode(".", $value);
            $current["Id"] = $currentItem[0];
            $current["Amount"] = $currentItem[1];
            
            if ($current["Id"] == $itemId)
            {
                $current["Amount"] = (($current["Amount"] - $amountToRemove) >= 0) ? $current["Amount"] - $amountToRemove : 0 ;
            }
            
            if ($key == 0)
            {
                $newGroundItems .= $current["Id"] . "." . $current["Amount"];
            }
            else
            {
                $newGroundItems .= "," . $current["Id"] . "." . $current["Amount"];
            }
            
        }
        
        $queryString = "UPDATE " . $townName . " SET `groundItems` = '" . $newGroundItems . "' WHERE `x` = 0 AND `y` = 0";
        Database::sendQuery($queryString);
        
    }

    public static function addItem($itemId, $amountToAdd, $townName){
        $dbCon = Database::getDB();

         //Get list of all items in bank
         $query = "SELECT groundItems FROM `" . $townName . "` WHERE `x` = 0 AND `y` = 0";
         $statement = $dbCon->prepare($query);
         $statement->execute();
         $result = $statement->fetch(); //0.41,1.41,2.3,.4,10.1,3.1,4.1,13.1,6.2,7.1,5.1
         $statement->closeCursor();
         
         $newGroundItems = "";
         $itemAdded = false;
         $bank = explode(",", $result["groundItems"]);

         if (empty($bank)){
            $newGroundItems .= $itemId . "." . $amountToAdd;
         }
         else {
            foreach($bank as $key => $value)
            {
                $currentItem = explode(".", $value);
                $current["Id"] = $currentItem[0];
                $current["Amount"] = $currentItem[1];
                
                if ($current["Id"] == $itemId)
                {
                   $current["Amount"] = $current["Amount"] + $amountToAdd;
                   $itemAdded = true;
                }
                
                if ($key == 0)
                {
                   $newGroundItems .= $current["Id"] . "." . $current["Amount"];
                }
                else
                {
                   $newGroundItems .= "," . $current["Id"] . "." . $current["Amount"];
                }
   
                if (($key == count($bank) - 1) && $itemAdded == false){
                   $newGroundItems .= "," . $itemId . "." . $amountToAdd;
                }
                
            }
         }
         
         $queryString = "UPDATE " . $townName . " SET `groundItems` = '" . $newGroundItems . "' WHERE `x` = 0 AND `y` = 0";
         Database::sendQuery($queryString);
         
    }
}
