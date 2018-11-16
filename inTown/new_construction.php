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
       	table {width:100%; color:#000000; border: 1px solid #120B06; padding:2px; background-color:#6D5A4A;box-shadow: 7px 7px #333333;}
	input.number {width:40px;}
	.data {width:150px; border: 1px solid black;}
	.newBuild {color:#111111; border: 1px solid #120B06; border-collapse:collapse; padding:2px; background-color:#6D5A4A;}
	.alreadyBuilt {color:#111111; border: 1px solid #120B06; border-collapse:collapse; padding:2px; background-color:#6D5A4A;}
	.buildingHelp {cursor:help; overflow:auto;}
	style1 {font-size:0.8em;}
	.head {border: 1px solid black;}
	.shield {float: right; display: block;}
	.buildingName
	{
		float: left;
		display: block;
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
                            //Get Details for the current Building we are checking
                            $buildName = $buildingsInfo[$i][0]; //innerwall
                            $buildReq = $buildingsInfo[$i][2]; // outerwall
                            $buildApReq = $buildingsInfo[$i][3]; //200
                            $buildingDef = $buildingsInfo[$i][5]; //50 defence
                            $maximumLevel = $buildingsInfo[$i][6]; //5
                            $buildingDesc = $buildingsInfo[$i][7]; //This is a wall
                            $defString = '';
                            
                            //This code repeats for every building that exists in the configuration
                            
                            //Check if the building adds defence, if it does, add a shield icon to the row
                            if ($buildingDef > 0)
                            {
                                $defString = "<style1><img src='../images/icons/shield.png'>" . $buildingDef . "</style1>";
                            }
                            
                            //Find the list of buildings currently in the town //Step is not neccessary as of now (Partial Change to OOP)
                            $structuresBuilt = StructuresDB::getTownStructures($townName); //Defence.0.1:Perimeter Fence.0.0:Wooden Wall.0.0:Inner Wall.0.0
                            
                            //Does the Building have atleast 1 complete level?
                            if (isStructureBuilt($buildName, $townName)) //Yes
                            {
                                
                            }
                            else //No --> Is There AP assigned to the first level?
                            {
                                
                            }
                            
                            //Does the building have atleast 1 level completed?
                                //Is it Maximum level?
                                    //Building is complete
                                    //
                                //Is AP assigned to the next level?
                                    //Enable AP contributions to Upgrade
                                    //
                                //Do you have enough resources?
                                    //Enable Begin Upgrade
                                    //
                            //Is there AP asigned to the first level?
                                //Enable AP Contributions
                                //
                            //Do you have the required building?
                                //Do you have the required resources?
                                    //Enable 
                        }
                        ?>
                    </tbody>
                </table>
                
            </div>
        </div>
    </body>
</html>

