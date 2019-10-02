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
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
        <script type="text/javascript" src="../js/construction.js"></script>
        
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
            

            <!-- 
            $tableRow = "<form action='../functions/build.php' method='post'><tr onclick='testAjax(`".json_encode($currentBuilding)."`, this)' class='pointer pointerGreen'>"
            . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
            . "<td colspan='3'>" . $indent . $currentBuilding->getName() . $defString . "</td>"
            . "<td colspan='1'>" . $resourceCostsString . "</td><input hidden type='text' name='buildName' value='" . $currentBuilding->getName() . "'>"
            // . "<td class='apCell'><input type='number' placeholder='Ap' class='apInput' name='apToAssign' min=0 max='$charAp'>" . $builtDetails["Ap"] . "/" . $currentBuilding->getApCost() . " <img src='../images/icons/ap.png'></td>"
            // . "<td class='buttonCell'><button type='submit' value='Submit' class='buildButton'><span>Build Now  " . $apNotice . "</span></button></td>"
            . "</tr></form>"; -->

            <div class="infoBlock">
                <form action='../functions/build.php' method='post'>
                <div class="buildingImage"><img src="../images/structures/filler_building.png"></div>
                <h2 id="buildInfoName">Select A Building to See Details</h2>
                <div class="buildingCosts" id="costs"></div>
                <p id="description"></p>
                <p id="allDetails" hidden> 
                <input type='number' id="apInput" placeholder=' Ap' class='apInput' name='apToAssign' min='0' max='<?php echo $charAp; ?>' onblur="lockDownAp('<?php echo $charAp; ?>')">  
                <input hidden type='text' name='buildName' id='buildName' value=''>
                <span class="buttonSpan" id="buttonSpan">
                    <button type='submit' value='Submit' class='buildButton'><span>Contribute</span></button>
                </span>
                <span class="individualCost" id="apAssigned"></span>
                </p>
                </form>
            </div>


            <div class="centralBox">
                
                <table>
                    <caption>Construction Sites</caption>
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th colspan="2">Building</th>
                            <th colspan="2" style="text-align:center;">Costs</th>
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
                                $resourceCostsString .= "  <img src='../images/items/" . $itemName . ".png' title='" . $itemName . "'>";
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
                                                . "<th></th><th colspan='4'>" . $currentBuilding->getName() . "</th>"
                                                . "</tr>";
                                    }
                                    
                                    else //Building is maximum level
                                    {
                                        $tableRow = "<tr class='pointerWhite'>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td colspan='3'>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td colspan='1'><small><b>Done - Structure at Max</b><small></td>"
                                                . "</tr>";
                                    }
                                }
                                elseif ($builtDetails["Ap"] >= 1)
                                {
                                    //Upgrade has already begun
                                    $tableRow = "<form action='../functions/build.php' method='post'><tr onclick='testAjax(`".json_encode($currentBuilding)."`, this)' class='pointer pointerGreen'>"
                                            . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                            . "<td colspan='3'>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                            . "<td colspan='1'><small>Contribute to Upgrade</small></td><input hidden type='text' name='buildName' value='" . $currentBuilding->getName() . "'>"
                                            . "</tr></form>";
                                }
                                else
                                {
                                    //Check if structure upgrade is affordable
                                    if (StructuresDB::isStructureAffordable($currentBuilding, $townId))
                                    {
                                        $tableRow = "<form action='../functions/build.php' method='post'><tr onclick='testAjax(`".json_encode($currentBuilding)."`, this)' class='pointer pointerGreen'>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td colspan='3'>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td colspan='1'>" . $resourceCostsString . "</td><input hidden type='text' name='buildName' value='" . $currentBuilding->getName() . "'>"
                                                . "</tr></form>";
                                    }
                                    else
                                    {
                                        //Structure has been upgraded, but there are not enough resources to begin the next level
                                        $tableRow = "<tr onclick='testAjax(`".json_encode($currentBuilding)."`, this)' class='pointer pointerRed'>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td colspan='3'>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td colspan='1'>" . $resourceCostsString . "</td>"
                                                . "</tr>";
                                    }
                                }
                                
                            }
                            elseif ($builtDetails["Ap"] >= 1) //No --> But There is AP assigned
                            {
                                //Allow AP contributions
                                        $tableRow = "<form action='../functions/build.php' method='post'><tr onclick='testAjax(`".json_encode($currentBuilding)."`, this)' class='pointer pointerGreen'>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td colspan='3'>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td colspan='1'><small>Started - Contribute</small></td><input hidden type='text' name='buildName' value='" . $currentBuilding->getName() . "'>"
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
                                        $tableRow = "<form action='../functions/build.php' method='post'><tr onclick='testAjax(`".json_encode($currentBuilding)."`, this)' class='pointer pointerGreen'>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td colspan='3'>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td colspan='1'>" . $resourceCostsString . "</td><input hidden type='text' name='buildName' value='" . $currentBuilding->getName() . "'>"
                                                . "</tr></form>";
                                    }
                                    else
                                    {
                                        //Structure is unlocked, but not affordable
                                        $tableRow = "<tr onclick='testAjax(`".json_encode($currentBuilding)."`, this)' class='pointer pointerRed'>"
                                                . "<td>" . $builtDetails['Level'] . "/"  . $currentBuilding->getMaxLevel() . "</td>"
                                                . "<td colspan='3'>" . $indent . $currentBuilding->getName() . $defString . "</td>"
                                                . "<td colspan='1'>" . $resourceCostsString . "</td>"
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

            <div class="bulletinBox">
            <div class="bulletinBoxTop">
			    <h3 style="text-align:center;">Bulletin Board</h3>
		    </div>
            <div class="bulletinBoxBottom">
                <!-- Bulletin Information Is Drawn from the Database below -->
                <?php 
                $Query3 = "SELECT `bulletin` FROM `towns` WHERE `town_id` = '$townId'";
                $Query4 = mysqli_query($con, $Query3);
                
                while ($row = mysqli_fetch_assoc($Query4))
                {
                    $bulletin = explode (".", $row['bulletin']);
                    foreach ($bulletin as $cur)
                    {
                        /* the if statement skips index 0 of the bulletin which is always empty */
                        if ($cur != '' && (strpos($cur, "<structure-contribute>") != FALSE || strpos($cur, "<structure-complete>") != FALSE))
                        {
                        echo "<li>" . $cur . "</li>";
                        }
                    }
                }
                ?>
            </div>
            </div>
        </div>
    </body>
</html>

