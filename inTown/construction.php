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
        <style>
       	table {width:100%; color:#000000; border: 1px solid #120B06; padding:2px; background-color:#6D5A4A;box-shadow: 7px 7px #333333; border-collapse: collapse;}
        td:first-child {padding-left:7px;}
        th{border-bottom: 1px solid black;}
	input.number {width:40px;}
	.data {width:150px; border: 1px solid black;}
	.newBuild {color:#111111; border: 1px solid #120B06; border-collapse:collapse; padding:2px; background-color:#6D5A4A;}
	.alreadyBuilt {color:#111111; border: 1px solid #120B06; border-collapse:collapse; padding:2px; background-color:#6D5A4A;}
	.buildingHelp {cursor:help; overflow:auto;}
	style1 {font-size:0.8em;}
	.head {border: 1px solid black;}
	.shield {float: right; display: block;}
        .buttonCell {text-align: right; padding-right: 5px;}
        small {color: #181818;}
	.buildingName
	{
		float: left;
		display: block;
	}
        .apInput
        {
            width: 50px;
            border-radius: 7px;
            margin-right: 8px;
            float: right;
        }
        .buildButton {
        display: inline-block;
        float: right;
        border-radius: 4px;
        background-color: #54483E;
        border: none;
        color: #A39D8D;
        text-align: center;
        font-size: 16px;
        padding: 5px;
        width: 150px;
        transition: all 0.5s;
        cursor: pointer;
        margin: 5px;
        box-shadow: 3px 2px black;
        }

        .buildButton span {
        cursor: pointer;
        display: inline-block;
        position: relative;
        transition: 0.5s;
        }

        .buildButton span:after {
        content: '\00bb';
        position: absolute;
        opacity: 0;
        top: 0;
        right: -20px;
        transition: 0.5s;
        }
        
        .buildButton:hover
        {
        background-color: #47372A;
        transition: 0s;
        }

        .buildButton:hover span {
        padding-right: 25px; 
        }

        .buildButton:hover span:after {
        opacity: 1;
        right: 0;
        }
        
        .buildButton:active
        {
        box-shadow: none;
        }
        </style>
        
        <?php 
        //Get important session details
        $playerName = $_SESSION['login'];
        $charName = $_SESSION['char'];
        $xCo = $_SESSION['x'];
        $yCo = $_SESSION['y'];
        $reqMet = false;
        $itemAmt =0;
        
        $charDetails = getCharDetails();
        $townName = $charDetails['townName'];
        $charLevel = $charDetails['level'];
        $charClass = $charDetails['class'];
        $charAp = $charDetails['currentAP'];
        
        $bank = getWarehouseItems($townName);
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
                            $builtDetails = StructuresDB::getBuiltDetails($currentBuilding->getName(), $townName);
                            $builtDetailsRequired = StructuresDB::getBuiltDetails($currentBuilding->getRequirement(), $townName);
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
                                $resourceCostsString .= "  <img src='../images/items/" . $itemName . ".png'>" . TownBankDB::getItemAmount($itemIdNeeded, $townName) . "/" . $itemAmountNeeded;
                            }
                            //This code repeats for every building that exists in the configuration
                            
                            //Check if the building adds defence, if it does, add a shield icon to the row
                            if ($currentBuilding->getDefence() > 0)
                            {
                                $defString = "<style1> <img src='../images/icons/shield.png'>" . $currentBuilding->getDefence() . "</style1>";
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
                                    $formName = "form" . $currentBuilding->getName();
                                        $tableRow = "<form action='../functions/build.php' method='post' id='" . $formName . "'><tr>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td><small>Contribute to Upgrade</small></td><input hidden type='text' name='buildName' value='" . $currentBuilding->getName() . "'>"
                                                . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' max='$charAp'>" . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
                                                . "<td class='buttonCell'><button type='submit' form='" . $formName . "' value='Submit' class='buildButton'><span>Upgrade</span></button></td>"
                                                . "</tr></form>";
                                }
                                else
                                {
                                    //Check if structure upgrade is affordable
                                    if (StructuresDB::isStructureAffordable($currentBuilding, $townName))
                                    {
                                        $tableRow = "<tr>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td>" . $resourceCostsString . "</td>"
                                                . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' max='$charAp'>" . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
                                                . "<td class='buttonCell'><button type='submit' form='' value='Submit' class='buildButton'><span>Upgrade</span></button></td>"
                                                . "</tr>";
                                    }
                                    else
                                    {
                                        //Structure has been upgraded, but there are not enough resources to begin the next level
                                        $tableRow = "<tr>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td>" . $resourceCostsString . "</td>"
                                                . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' max='$charAp'>" . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
                                                . "<td class='buttonCell'>Not Enough Resources</td>"
                                                . "</tr>";
                                    }
                                }
                                
                            }
                            elseif ($builtDetails["Ap"] >= 1) //No --> But There is AP assigned
                            {
                                //Allow AP contributions
                                        $tableRow = "<tr>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td><small>Contribute to Structure</small></td>"
                                                . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' max='$charAp'>" . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
                                                . "<td class='buttonCell'><button type='submit' form='' value='Submit' class='buildButton'><span>Build</span></button></td>"
                                                . "</tr>";
                            }
                            else
                            {
                                //Do you have the required building at atleast level 1?
                                if ($builtDetailsRequired["Level"] >= 1)
                                {
                                    //Required Building is atleast level 1
                                    if (StructuresDB::isStructureAffordable($currentBuilding, $townName))
                                    {
                                        //Structure is affordable, and construction has not begun
                                        $tableRow = "<tr>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td>" . $resourceCostsString . "</td>"
                                                . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' max='$charAp'>" . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
                                                . "<td class='buttonCell'><button type='submit' form='' value='Submit' class='buildButton'><span>Build Now</span></button></td>"
                                                . "</tr>";
                                    }
                                    else
                                    {
                                        //Structure is unlocked, but not affordable
                                        $tableRow = "<tr>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td>" . $resourceCostsString . "</td>"
                                                . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' max='$charAp'>" . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
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

