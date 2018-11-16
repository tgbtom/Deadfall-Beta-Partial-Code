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
    
    public function getItemCosts()
    {
        return $this->s_item_costs;
    }
    
    //returns a multidimensional array of item costs
    public function getItemCosts_array()
    {
        //2.10:8.2
        $breakdown = explode(':', $this->s_item_costs);
        for ($i = 0; $i < count($breakdown); $i++)
        {
            $break = explode('.', $breakdown[$i]);
            $requirements[] = array($break[0], $break[1]); //0 => itemId, itemcount  1=> item id, item count
        }
        
        return $requirements;
    }
    
    public function getDefence()
    {
        return $this->s_defence;
    }
    
    public function getMaxLevel()
    {
        $this->s_max_level;
    }
    
    public function getDescription()
    {
        $this->s_description;
    }
    
    public function getIndentation()
    {
        $this->s_indentation;
    }
}

class StructuresDB
{
    public static function getTownStructures($townName)
    {
        $dbCon = Database::getDB();
        
        $query = "SELECT 'buildings' from `towns` WHERE `townName` = :townName";
        $statement = $dbCon->prepare($query);
        $statement->bindValue(':townName', $townName);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();
        
        return $statement;
    }
}
