<?php
//Building Name, Building Type, Required Building, AP cost, Item#.ItemAmount:Item2#.Item2Amount, Defence Given, Maximum Level, Building Description
/*When a new building is added, it needs to be added to every town in databases as well
['buildingName.(1=Base Starting building|0=Buildings with prereq's).buildingLevel']*/
$buildingsInfo = array(
array("Defence", "Defence", "Defence", "1", "", "0", "1", "Defensive constructions that help to defend from the horde.", "0"),
array("Perimeter Fence", "Defence", "Defence", "30", "2.10:8.2", "40", "1", "Build a fence establishing a perimeter for the settlement. Will Keep some zeds out.", ""),
array("Wooden Wall", "Defence", "Perimeter Fence", "70", "2.18:8.5", "60", "5", "Improve the perimeter around the settlement with large wooden boards.", "&nbsp→"),
array("Inner Wall", "Defence", "Wooden Wall", "50", "2.10:10.15", "15", "5", "Create a secondary wall, just in case there is a breach in the main wall.", "&nbsp&nbsp→"),
array("Wooden Support", "Defence", "Wooden Wall", "40", "2.15", "30", "1", "Add structural support the walls.", "&nbsp&nbsp→"),
array("Metal Patching", "Defence", "Wooden Wall", "80", "3.10", "150", "1", "Reinforce the outer wall.", "&nbsp&nbsp→"),
array("Sentry Tower", "Defence", "Wooden Wall", "75", "3.10:2.15:10.15", "100", "5", "Build a sentry tower on the wall. Used as a lookout.", "&nbsp&nbsp→"),
array("Supply", "Supply", "Supply", "1", "", "0", "1", "Constructions that revolve around supplies/service.", "0"),
array("Water Reserve", "Supply", "Supply", "50", "5.30:0.5:3.2", "0", "1", "Establish a holding area for all clean water reserves in the settlement. Once built; provides 2 water rations per day.", ""),
array("Vegetable Garden", "Supply", "Supply", "85", "0.10", "0", "1", "A small plot to grow vegetables. Generates food overnight.", "")
);
//Compiled Database Text Array below !*!*!*!*[ADD TO THIS EVERYTIME A BUILDING IS ADDED]*!*!*!*!
//Defence.0.1:Perimeter Fence.0.0:Wooden Wall.0.0:Inner Wall.0.0:Wooden Support.0.0:Metal Patching.0.0:Sentry Tower.0.0:Supply.0.1:Water Reserve.0.0:Vegetable Garden.0.0

?>
