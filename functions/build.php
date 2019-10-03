<?php

require_once("../model/database.php");
require_once("../model/structures.php");
require_once("../data/buildings.php");
require_once("../functions/queryFunctions.php");

$charId = $_SESSION['char_id'];
$charObject = new Character($charId);
$townId = $charObject->townId; 
$townName = Towns::getTownNameById($townId);

$buildName = filter_input(INPUT_POST, "buildName");
$apToAssign = filter_input(INPUT_POST, "apToAssign");

if($charObject->class == "Builder"){
    $apToAssign *= 2;
}


if (isset($buildName) && isset($apToAssign))
{
    //Create an object of the Structure that we are trying to upgrade, and an object of its town-specific stats
    for ($i=0; $i < count($buildingsInfo); $i++)
    {
        if ($buildingsInfo[$i][0] == $buildName)
        {
           $currentBuilding = new Structure($buildingsInfo[$i][0], $buildingsInfo[$i][1], $buildingsInfo[$i][2], $buildingsInfo[$i][3], $buildingsInfo[$i][4], $buildingsInfo[$i][5], $buildingsInfo[$i][6], $buildingsInfo[$i][7], $buildingsInfo[$i][8]);
           $builtDetails = StructuresDB::getBuiltDetails($buildName, $townId);
           //Ensure the building is not maxed out Level
           if ($builtDetails["Level"] >= $currentBuilding->getMaxLevel())
           {
               //Building is Maxed Out
               echo "<script>window.location.href='../inTown/?locat=construction&e=Building was already completed.'</script>";
           }
           else
           {
               //Building is not max level
               //Check to see if it is a partial level (Resources already assigned)
               
               $apRemaining = $currentBuilding->getApCost() - $builtDetails["Ap"];
               
               //We need to check required AP, if apToAdd is greater than required ap, reduce apToAdd, also ensure character has enough ap still
                if ($apToAssign > $apRemaining){
                    $apToAssign = $apRemaining;
                }
               
                if($charObject->class == "Builder" && ($charObject->currentAP < ($apToAssign / 2))){
                    //Return, Character does not have enough Ap
                    echo "<script>window.location.href='../inTown/?locat=construction&e=Character does not have " . ($apToAssign / 2) . " Ap.'</script>";
                }
                elseif ($charObject->class != "Builder" && ($charObject->currentAP < $apToAssign)){
                    //Return, Character does not have enough Ap
                    echo "<script>window.location.href='../inTown/?locat=construction&e=Character does not have " . $apToAssign . " Ap.'</script>";
                }

               if ($builtDetails["Ap"] >= 1)
               {
                   //Structure has been started already

                       //Apply the AP to the structure, REMOVE AP FROM CHAR****
                       //Builders have half the AP removed because it doubles the AP value above and we dont want to remove double AP
                       if($charObject->class == "Builder"){
                        $newAp = $charObject->currentAP - ceil($apToAssign/2);
                       }
                       else{
                        $newAp = $charObject->currentAP - $apToAssign;
                       }
                       $queryString = "UPDATE characters SET `currentAP` = " . $newAp . " WHERE "
                               . "`id` = '" . $charId . "' AND "
                               . "`username` = '" . $charDetails["username"] . "' AND "
                               . "`town_id` = '" . $townId ."'";
                       Database::sendQuery($queryString);
                       
                       StructuresDB::addAp($currentBuilding, $apToAssign, $townId);
                       echo "<script>window.location.href='../inTown/?locat=construction'</script>";
               }
               else
               {
                   //We need to remove resources to contribute
                   //Check if the structure is still affordable
                    if (StructuresDB::isStructureAffordable($currentBuilding, $townId))
                   {
                       //Check character ap and ap required
                       //...Then remove items from bank.
                       
                        if ($apToAssign > $apRemaining)
                        {
                            $apToAssign = $apRemaining;
                        }
                        
                        if ($charObject->currentAP < $apToAssign)
                        {
                            //Return, Character does not have enough Ap
                            echo "<script>window.location.href='../inTown/?locat=construction&e=Character does not have " . $apToAssign . " Ap.'</script>";
                        }
                        else
                        {
                            //Apply the AP to the structure, REMOVE AP FROM CHAR
                            if($charObject->class == "Builder"){
                                $newAp = $charObject->currentAP - ceil($apToAssign/2);
                            }
                            else{
                                $newAp = $charObject->currentAP - $apToAssign;
                            }
                            $queryString = "UPDATE characters SET `currentAP` = " . $newAp . " WHERE "
                               . "`id` = '" . $charId . "' AND "
                               . "`username` = '" . $charDetails["username"] . "' AND "
                               . "`town_id` = '" . $townId ."'";
                            Database::sendQuery($queryString);
                            
                            StructuresDB::addAp($currentBuilding, $apToAssign, $townId);
                            
                            //...then remove items from bank
                            $itemCosts = $currentBuilding->getItemCosts_objects();
                            foreach ($itemCosts->getItemCosts() as $value)
                            {
                                TownBankDB::removeItem($value->getItemId(), $value->getItemAmount(), $townId); 
                            }
                            echo "<script>window.location.href='../inTown/?locat=construction'</script>";
                            
                        }
                       
                   }
                   else{
                       echo "<script>window.location.href='../inTown/?locat=construction&e=Structure is no longer affordable.'</script>";
                   }
                }
               
           }
        }
    }
}
else
{
    //If the page is being visisted without the correct data being transmitted, redirect to construction page
    echo "<script>window.location.href = '../inTown/?locat=construction&e=fuct'</script>";
}


