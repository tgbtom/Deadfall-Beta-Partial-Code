<?php

require_once("../model/database.php");
require_once("../model/structures.php");
require_once("../data/buildings.php");
require_once("../functions/queryFunctions.php");

$charDetails = getCharDetails();
$townName = $charDetails["townName"];

$buildName = filter_input(INPUT_POST, "buildName");
$apToAssign = filter_input(INPUT_POST, "apToAssign");

if (isset($buildName) && isset($apToAssign))
{
    //Create an object of the Structure that we are trying to upgrade, and an object of its town-specific stats
    for ($i=0; $i < count($buildingsInfo); $i++)
    {
        if ($buildingsInfo[$i][0] == $buildName)
        {
           $currentBuilding = new Structure($buildingsInfo[$i][0], $buildingsInfo[$i][1], $buildingsInfo[$i][2], $buildingsInfo[$i][3], $buildingsInfo[$i][4], $buildingsInfo[$i][5], $buildingsInfo[$i][6], $buildingsInfo[$i][7], $buildingsInfo[$i][8]);
           $builtDetails = StructuresDB::getBuiltDetails($buildName, $townName);
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
               
               if ($builtDetails["Ap"] >= 1)
               {
                   //Structure has been started already
                   //We need to check required AP, if apToAdd is greater than required ap, reduce apToAdd, also ensure character has enough ap still
                   
                   if ($apToAssign > $apRemaining)
                   {
                       $apToAssign = $apRemaining;
                   }
                   
                   if ($charDetails["currentAP"] < $apToAssign)
                   {
                       //Return, Character does not have enough Ap
                       echo "<script>window.location.href='../inTown/?locat=construction&e=Character does not have " . $apToAssign . " Ap.'</script>";
                   }
                   else
                   {
                       //Apply the AP to the structure*** <DONE>
                       StructuresDB::addAp($currentBuilding->getName(), $apToAssign, $townName);
                   }
               }
               else
                {
                   //We need to remove resources to contribute
                   //Check if the structure is still affordable
                    if (StructuresDB::isStructureAffordable($currentBuilding, $townName))
                   {
                       //Check character ap and ap required
                       //...Then remove items from bank.
                       
                        if ($apToAssign > $apRemaining)
                        {
                            $apToAssign = $apRemaining;
                        }
                        
                        if ($charDetails["currentAP"] < $apToAssign)
                        {
                            //Return, Character does not have enough Ap
                            echo "<script>window.location.href='../inTown/?locat=construction&e=Character does not have " . $apToAssign . " Ap.'</script>";
                        }
                        else
                        {
                            //Apply the AP to the structure
                            StructuresDB::addAp($currentBuilding->getName(), $apToAssign, $townName);
                            //...then remove items from bank************************************
                            
                        }
                       
                   }
                }
               
           }
        }
    }
}
else
{
    //If the page is being visisted without the correct data being transmitted, redirect to construction page
    echo "<script>window.location.href = '../inTown/?locat=construction'</script>";
}


