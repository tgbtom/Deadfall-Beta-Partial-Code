<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("../connect.php");
require_once("../functions/verifyLogin.php");
include ("../data/buildings.php");
include ("../data/items.php");
require_once ('../functions/queryFunctions.php');
require_once("../model/structures.php");
require_once("../model/database.php");

?>

<!DOCTYPE HTML>
<html>
    <head>
        <link rel="stylesheet" href="mainDesignTown.css" type="text/css">
        <link rel="stylesheet" href="../css/construction.css" type="text/css">
        
        <?php 
        //Get important session details
        $playerName = $_SESSION['login'];
        $charName = $_SESSION['char'];
        $charId = $_SESSION['char_id'];
        $xCo = $_SESSION['x'];
        $yCo = $_SESSION['y'];
        $reqMet = false;
        $itemAmt =0;

        $apNotice = "";
        
        $charDetails = getCharDetails();
        $townId = $charDetails['town_id'];
        $townName = Towns::getTownNameById($townId);
        $charLevel = $charDetails['level'];
        $charClass = $charDetails['class'];
        $charAp = $charDetails['currentAP'];

        if($charClass == "Builder"){
            //Make note of Double contribution room for builders because their AP counts as 2 towards structures
            $apNotice = "<small> 2x</small>";
        }
        
        $bank = getWarehouseItems($townId);
        $townBank = explode(',', $bank);
        ?>
        
    </head>
    <body>
        <div class="container">
            
            <?php include("../universal/header.php");?>
            
            <div class="centralBox">
                
                <table>
                    <caption>Construction Sites</caption>
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Building</th>
                            <th>Resources</th>
                            <th>AP</th>
                            <th>Build</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        for ($i = 0; $i < count($buildingsInfo); $i++)
                        {
                             $tableRow = "<tr></tr>";
                             
                            //Get Details for the current Building we are checking      
                            $currentBuilding = new Structure($buildingsInfo[$i][0], $buildingsInfo[$i][1], $buildingsInfo[$i][2], $buildingsInfo[$i][3], $buildingsInfo[$i][4], $buildingsInfo[$i][5], $buildingsInfo[$i][6], $buildingsInfo[$i][7], $buildingsInfo[$i][8]);
                            $builtDetails = StructuresDB::getBuiltDetails($currentBuilding->getName(), $townId);
                            $builtDetailsRequired = StructuresDB::getBuiltDetails($currentBuilding->getRequirement(), $townId);
                            $defString = '';
                            $resourceCostsString = '';
                            $indent = ($buildingsInfo[$i][8] == '0') ? '' : $buildingsInfo[$i][8];
                            $buildingCosts = $currentBuilding->getItemCosts_objects();
                            
                            //Create a string to display the required items for the building
                            foreach ($buildingCosts->getItemCosts() as $value)
                            {
                                $itemIdNeeded = $value->getItemId();
                                $itemName = $itemsMaster[$itemIdNeeded][0];
                                $itemAmountNeeded = $value->getItemAmount();
                                $resourceCostsString .= "  <img src='../images/items/" . $itemName . ".png' title='" . $itemName . "'>" . TownBankDB::getItemAmount($itemIdNeeded, $townId) . "/" . $itemAmountNeeded;
                            }
                            //This code repeats for every building that exists in the configuration
                            
                            //Check if the building adds defence, if it does, add a shield icon to the row
                            if ($currentBuilding->getDefence() > 0)
                            {
                                $defString = "<style1> <img src='../images/icons/shield.png' title='Defence Given'>" . $currentBuilding->getDefence() . "</style1>";
                            }
     
                            
                            //Does the Building have atleast 1 complete level?
                            if ($builtDetails["Level"] >= 1) //Yes
                            {
                                if ($builtDetails["Level"] >= $currentBuilding->getMaxLevel())
                                {
                                    if ($currentBuilding->getApCost() == 1) //Check if it is a category Structure
                                    {
                                        $tableRow = "<tr><td colspan='5' style='height:10px;'></td></tr><tr class='category'>"
                                                . "<th colspan='5'>" . $currentBuilding->getName() . "</th>"
                                                . "</tr>";
                                    }
                                    
                                    else //Building is maximum level
                                    {
                                        $tableRow = "<tr>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td><small><b>Structure is Complete</b><small></td>"
                                                . "<td class='apCell'></td>"
                                                . "<td class='buttonCell'></td>"
                                                . "</tr>";
                                    }
                                }
                                elseif ($builtDetails["Ap"] >= 1)
                                {
                                    //Upgrade has already begun
                                    $tableRow = "<form action='../functions/build.php' method='post'><tr>"
                                            . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                            . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                            . "<td><small>Contribute to Upgrade</small></td><input hidden type='text' name='buildName' value='" . $currentBuilding->getName() . "'>"
                                            . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' min=0 max='$charAp'>" . $ap . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
                                            . "<td class='buttonCell'><button type='submit' value='Submit' class='buildButton'><span>Contribute  " . $apNotice . "</span></button></td>"
                                            . "</tr></form>";
                                }
                                else
                                {
                                    //Check if structure upgrade is affordable
                                    if (StructuresDB::isStructureAffordable($currentBuilding, $townId))
                                    {
                                        $tableRow = "<form action='../functions/build.php' method='post'><tr>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td>" . $resourceCostsString . "</td><input hidden type='text' name='buildName' value='" . $currentBuilding->getName() . "'>"
                                                . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' min=0 max='$charAp'>" . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
                                                . "<td class='buttonCell'><button type='submit' value='Submit' class='buildButton'><span>Upgrade  " . $apNotice . "</span></button></td>"
                                                . "</tr></form>";
                                    }
                                    else
                                    {
                                        //Structure has been upgraded, but there are not enough resources to begin the next level
                                        $tableRow = "<tr>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td>" . $resourceCostsString . "</td>"
                                                . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' disabled max='$charAp'>" . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
                                                . "<td class='buttonCell'>Not Enough Resources</td>"
                                                . "</tr>";
                                    }
                                }
                                
                            }
                            elseif ($builtDetails["Ap"] >= 1) //No --> But There is AP assigned
                            {
                                //Allow AP contributions
                                        $tableRow = "<form action='../functions/build.php' method='post'><tr>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td><small>Contribute to Structure</small></td><input hidden type='text' name='buildName' value='" . $currentBuilding->getName() . "'>"
                                                . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' min=0 max='$charAp'>" . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
                                                . "<td class='buttonCell'><button type='submit' value='Submit' class='buildButton'><span>Contribute " . $apNotice . "</span></button></td>"
                                                . "</tr></form>";
                            }
                            else
                            {
                                //Do you have the required building at atleast level 1?
                                if ($builtDetailsRequired["Level"] >= 1)
                                {
                                    //Required Building is atleast level 1
                                    if (StructuresDB::isStructureAffordable($currentBuilding, $townId))
                                    {
                                        //Structure is affordable, and construction has not begun
                                        $tableRow = "<form action='../functions/build.php' method='post'><tr>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td>" . $resourceCostsString . "</td><input hidden type='text' name='buildName' value='" . $currentBuilding->getName() . "'>"
                                                . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' min=0 max='$charAp'>" . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
                                                . "<td class='buttonCell'><button type='submit' value='Submit' class='buildButton'><span>Build Now  " . $apNotice . "</span></button></td>"
                                                . "</tr></form>";
                                    }
                                    else
                                    {
                                        //Structure is unlocked, but not affordable
                                        $tableRow = "<tr>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td>" . $resourceCostsString . "</td>"
                                                . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' disabled max='$charAp'>" . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
                                                . "<td class='buttonCell'>Not Enough Resources</td>"
                                                . "</tr>";
                                    }
                                }
                            }
                            echo $tableRow;
                        }
                        ?>
                    </tbody>
                </table>
                
            </div>
        </div>
    </body>
</html>

