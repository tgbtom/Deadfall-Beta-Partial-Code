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
    
    public static function addAp($structure, $apToAdd, $townName)
    {
        $builtStructures = self::getTownStructures($townName);
        $builtArray = explode(':', $builtStructures);
        
        foreach ($builtArray as $value)
        {
            $currentArray = explode('.', $value);
            $building["Name"] = $currentArray[0];
            $building["Ap"] = $currentArray[1];
            $building["Level"] = $currentArray[2];
            if ($building["Name"] == $structure)
            {
                $building["Ap"] += $apToAdd;
            }
            
            $newBuildString .= $building["Name"] . "." . $building["Ap"] . "." . $building["Level"];
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
}
